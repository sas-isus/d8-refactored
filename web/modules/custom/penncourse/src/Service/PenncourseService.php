<?php

namespace Drupal\penncourse\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\user\Entity\User;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;

/**
 * Class PenncourseService.
 */
// class PenncourseService implements PenncourseServiceInterface {
class PenncourseService {

  protected $config;
  protected $logger;

  /**
   * Constructs a new PenncourseService object.
   */
  public function __construct() {
    $this->config = \Drupal::service('config.factory')->getEditable('penncourse.penncourseconfig');
    $this->logger = \Drupal::service('logger.factory')->get('penncourse');
  }

  public function test() {
    $db_info = $this->getStorageInfo('pc_section', array('field_pc_section_id', 'field_pc_term', 'field_pc_subj_area'));
    $this->updateSectionsBySubject('2018C', 'ECON');
    /* $connection = \Drupal::database();
    $result = $connection->select($field_table, 'f')
      ->fields('f', array($field_column))
      ->distinct(TRUE)
      ->condition('bundle', 'article')
      ->condition('entity_id', $nids, 'IN')
      ->execute()->fetchCol(); */

    return $this->config->get('penncourse_authorization_token');
  }

  public function getSectionParams() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_HTTPHEADER => array(
            'Authorization-Bearer: ' . $this->config->get('penncourse_authorization_bearer'),
            'Authorization-Token: ' . $this->config->get('penncourse_authorization_token')
        ),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://esb.isc-seo.upenn.edu/8091/open_data/course_section_search_parameters/'
    ));

    $curl_return = curl_exec($curl);
    $result = json_decode($curl_return);
    // watchdog('penncourse test', $curl_return, array(), WATCHDOG_NOTICE);

    $service_error = $result->service_meta->error_text;
    if (!$service_error && isset($result->result_data[0])) {
        return $result->result_data[0];
    }
    else {
        return (object) ['error_text' => $service_error];
    }
  }

  /**
   * function penncourse_update_section_params
   *
   * updates the variable penncourse_subject_map and penncourse_available_terms
   * with the latest parameters from the section search web service
   */
  public function updateSectionParams() {
    $result = $this->getSectionParams();
    $this->logger->notice('penncourse cron: updating section service parameters');

    if (!isset($result->error_text)) {
      $subject_array = (array) $result->departments_map;
      $terms_array = (array) $result->available_terms_map;
      $this->config->set('penncourse_subject_map', $subject_array);
      $this->config->set('penncourse_available_terms', $terms_array);
      $this->config->save();
    }
    else {
      $this->logger->error('penncourse cron: exception: ' . $result->error_text);
    }
  }

  /**
   * process the section entries for a single subject area
   * @param $subj_area
   */
  public function processSubjectArea($subj_area) {
    $terms = $this->config->get('penncourse_available_terms');

    foreach ($terms as $key => $term) {
      $this->updateSectionsBySubject($key, $subj_area);
    }
  }

  /**
   * call the web service to retrieve section information
   * @param $term
   * @param $subj_area
   * @param $page
   */
  function callSectionService($term, $subj_area, $page = 1) {
    if ($term && $subj_area) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HTTPHEADER => array(
              'Authorization-Bearer: ' . $this->config->get('penncourse_authorization_bearer'),
              'Authorization-Token: ' . $this->config->get('penncourse_authorization_token')
            ),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://esb.isc-seo.upenn.edu/8091/open_data/course_section_search?course_id=' . $subj_area . '&term=' . $term . '&page_number=' . $page . '&number_of_results_per_page=100'
        ));

        $curl_return = curl_exec($curl);

        $result = json_decode($curl_return);

        $service_error = $result->service_meta->error_text;
        if (!$service_error && isset($result->result_data)) {
            return $result;
            // return $result->result_data;
        }
        else {
            return (object) ['error_text' => $service_error];
        }
    }
    else {
        // term and/or subject area not supplied
        return (object) ['error_text' => 'A valid term code or subject area must be supplied'];
    }
  }

  /**
   * retrieve all section data for a given term and subject area
   * @param $term
   * @param $subj_area
   */
  public function getTermSections($term, $subj_area) {
    $course_data = array();

    // get first data set
    try {
        $result = $this->callSectionService($term, $subj_area, 1);
        if (isset($result->error_text)) {
            throw new \Exception("Course section web service error: " . $result->error_text, 1);
        }
        else {
            // add course data to the array to be returned
            $course_data = array_merge($course_data, $result->result_data);

            if ($result->service_meta->number_of_pages > 1) {
                // there is more than 1 page of data, we need to get the rest
                $current_page = 1;
                $next_page = 2;

                while ($next_page > $current_page) {
                    $result = $this->callSectionService($term, $subj_area, $next_page);
                    if (isset($result->error_text)) {
                        $next_page = $current_page; // we don't want to continue looping on an error
                        throw new \Exception("Course section web service error: " . $result->error_text, 1);
                    }
                    else {
                        // add course data to the array to be returned
                        $course_data = array_merge($course_data, $result->result_data);
                        $next_page = $result->service_meta->next_page_number;
                        $current_page = $result->service_meta->current_page_number;
                    }
                }
            }
        }
    }
    catch (\Exception $e) {
      $this->logger->error('penncourse exception: ' . $e . '<br />term: ' . $term . '; subject area: ' . $subj_area);
    }
    return $course_data;
  }


  /**
   * retrieve a pc_section node given a section id and term
   * returns node object
   * @param string $section_id
   * @param string $term
   */
  public function getSectionNode($section_id, $term) {

  }

  /**
   * load course node for a course id and term
   * returns node object
   * @param string $subject_area
   * @param string $term
   */
  public function getSectionNodesBySubjectTerm($subject_area, $term) {
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'pc_section');
    $entity_ids = $query->execute();

      $query = new EntityFieldQuery();
      $entity = $query->entityCondition('entity_type', 'node')
          ->propertyCondition('type', 'pc_course')
          ->fieldCondition('field_pc_course_id', $course_id)
          ->fieldCondition('field_pc_term', $term)
          ->range(0, 1)
          ->execute();

      if (!empty($entity['node'])) {
          $node = node_load(current(array_keys($entity['node'])));

          return $node;
      }
      else {
          return NULL;
      }
  } // function penncourse_load_course_node($subj_code)


  /**
   * return the term code for the most currently published course roster
   *
   * course rosters for the summer and fall are finalized on 2/19
   * rosters for the spring are finalized on 10/1
   * @return string term code.
   *
   */
  public function getFinalTerm() {
    if ((date('n', REQUEST_TIME) >= 10) && (date('j', REQUEST_TIME) >= 1)) {
      // date is after 10/1 - final term is next spring term
      $term = date('Y', REQUEST_TIME) + 1;
      $term .= 'A';
    }
    elseif ((date('n', REQUEST_TIME) >= 2) && ((date('j', REQUEST_TIME) >= 19) || (date('n', REQUEST_TIME) >= 3))) {
      // date is after 2/19 - final term is next (or current) fall term
      $term = date('Y', REQUEST_TIME);
      $term .= 'C';
    }
    else {
      // date is before 2/19 - final term is current spring term
      $term = date('Y', REQUEST_TIME);
      $term .= 'A';
    }
    return $term;
  }

  /**
   * returns the current term
   * A = January 1 through May 20
   * B = May 21 through August 15
   * C = August 16 through December 31
   * @return string term code
   */
  function getCurrentTerm() {
      // get database information about course_course_id field
      $term = date('Y', REQUEST_TIME);
      if ((date('n', REQUEST_TIME) <= 5) && ((date('j', REQUEST_TIME) <= 20) || (date('n', REQUEST_TIME) <= 4))) {
          $term .= 'A';
      }
      elseif ((date('n', REQUEST_TIME) <= 8) && ((date('j', REQUEST_TIME) <= 15) || (date('n', REQUEST_TIME) <= 7))) {
          $term .= 'B';
      }
      else {
          $term .= 'C';
      }
      return $term;
  }

  /**
   * Given a content type and array of field names in that content type,
   * returns a keyed array of the database table and column names
   *
   * Only valid for simple field types (textfield, integer)
   *
   * @param string $content_type The machine name of the content type
   * @param array $field_name_array An array of field names
   * @return array An array of table and field names keyed by the field machine name
   */
  function getStorageInfo($content_type, $field_name_array) {
    $result = array();
    $table_mapping = \Drupal::entityTypeManager()->getStorage('node')->getTableMapping();

    foreach ($field_name_array as $field_name) {
      $field_table = $table_mapping->getFieldTableName($field_name);
      $result[$field_name]['table'] = $field_table;
      $field_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')[$field_name];
      $field_column = $table_mapping->getFieldColumnName($field_storage_definitions, 'value');
      $result[$field_name]['column'] = $field_column;
    }
    return $result;
  } // function penncourse_db_info

  /**
   * returns an array of nids (node id's) keyed by a combo of the term and section id
   * lookup is restricted to a single term
   * @param integer $year
   * @param string $subj_area
   * @return array
   */
  function getSectionNids($term, $subj_area = NULL) {
      if ($term) {
          // get database info about course fields
          $db_info = $this->getStorageInfo('pc_section', array('field_pc_section_id', 'field_pc_term', 'field_pc_subj_area'));

          $nids = array();
          // get nids and the course_key
          // we just build this string for debugging
          /* $sql = sprintf("			SELECT 		n.nid AS nid,
     																	c.%s AS field_pc_section_id,
     																	t.%s AS field_pc_term
   												 	FROM 			{node} n
   												 	LEFT JOIN {%s} t ON n.nid = t.entity_id
   												 	LEFT JOIN {%s} c ON n.nid = c.entity_id
   												 	LEFT JOIN {%s} s ON n.nid = s.entity_id
   												 	WHERE 		(n.type in ('pc_section'))
   												 	            AND (t.%s = '%s')
   												 	            AND (s.%s = '%s')
     											 	ORDER BY 	field_pc_section_id ASC",
              $db_info['field_pc_section_id']['column'],
              $db_info['field_pc_term']['column'],
              $db_info['field_pc_term']['table'],
              $db_info['field_pc_section_id']['table'],
              $db_info['field_pc_subj_area']['table'],
              $db_info['field_pc_term']['column'],
              $term,
              $db_info['field_pc_subj_area']['column'],
              $subj_area);
          pcpm('section node lookup SQL: ' . $sql); */

          // now build the query string with table names, leaving named placeholders for db_query parameter substitution
          $sql = sprintf("SELECT        n.nid AS nid,
                                        c.%s AS field_pc_section_id,
                                        t.%s AS field_pc_term
                          FROM          {node} n
                                        LEFT JOIN {%s} t ON n.nid = t.entity_id
                                        LEFT JOIN {%s} c ON n.nid = c.entity_id
                                        LEFT JOIN {%s} s ON n.nid = s.entity_id
                          WHERE         (n.type in ('pc_section'))
                                        AND (t.%s = :term)
                                        AND (s.%s = :subject)
                          ORDER BY      field_pc_section_id ASC",
              $db_info['field_pc_section_id']['column'],
              $db_info['field_pc_term']['column'],
              $db_info['field_pc_term']['table'],
              $db_info['field_pc_section_id']['table'],
              $db_info['field_pc_subj_area']['table'],
              $db_info['field_pc_term']['column'],
              $db_info['field_pc_subj_area']['column']);

          // now we use that prepared statement with our query parameters
          $connection = Database::getConnection();
          $results = $connection->query($sql,
            array(':term' => $term,
              ':subject' => $subj_area),
            array());

          // pcpm('section node lookup SQL: ' . $sql);

          foreach ($results as $key_array) {
              $nids[trim($key_array->field_pc_term) . '-' . trim($key_array->field_pc_section_id)] = $key_array->nid;
          }
          unset($results);

          return $nids;
      }
      else {
          return NULL;
      }
  }

  /**
   * Load section data from web service and build
   * section nodes for a given term and subject area
   * @param string $term
   * @param string $subj_area
  */
  function updateSectionsBySubject($term, $subj_area) {
      // has been updated to reflect the fact that only sections (not courses) are displayed

      // only run if term has been set
      if ($term) {
          // get section node id's
          $section_nodes = $this->getSectionNids($term, $subj_area);
          // now we get the course section data
          $results = $this->getTermSections($term, $subj_area);
          if (!$results) {
              // make sure we have returned data from the service
              $this->logger->notice('penncourse warning: no section results returned for term ' . $term . ' and subject area ' . $subj_area);
          }
          else {
              $sections = array();
              $course_id = '';
              foreach ($results as $section) {
                  /* if ($course_record->course_id != $course_id) {
                      $courses[trim($course_record->term) . '-' . trim($course_record->course_id)] = $course_record;
                      $course_id = $course_record->course_id;
                  } */
                  $sections[trim($section->term) . '-' . trim($section->section_id)] = $section;
              }
              unset($results);

              // loop through $sections array and update or create pc_section nodes as needed
              $current_term = $this->getCurrentTerm();
              foreach ($sections as $key => $section_record) {
                  $section_info = array();
                  $section_info['title'] = $section_record->section_title;
                  $section_info['descr'] = $section_record->course_description;
                  // $section_info['descr'] = penncourse_transform_description($section_record->course_desc); this field is maintained locally on the Drupal site
                  $section_info['instructors'] = $this->transformInstructors($section_record->instructors);
                  $section_info['meeting'] = $this->transformMeetings($section_record->meetings);
                  if ($section_record->term <= $current_term) {
                      $section_info['location'] = $this->transformLocation($section_record->meetings);
                  }
                  else {
                      $section_info['location'] = '';
                  }
                  $section_info['section_id'] = $section_record->section_id;
                  // $section_info['course'] = $course_nodes[trim($section_record->term) . '-' . trim($section_record->course_id)];
                  $section_info['subj_area'] = $section_record->course_department;
                  $section_info['term'] = $section_record->term;
                  $section_info['term_session'] = $section_record->term_session;
                  $section_info['activity'] = $section_record->activity;
                  $section_info['course_no'] = $section_record->course_number;
                  $section_info['section_no'] = $section_record->section_number;
                  $section_info['xlist'] = $this->buildXlists($section_record->crosslistings);
                  $section_info['syllabus_url'] = $section_record->syllabus_url;
                  $section_info['status'] = $section_record->course_status;
                  $section_info['course_id'] = $section_record->course_department . $section_record->course_number;
                  $section_info['notes'] = $this->buildNotes($section_record->important_notes);
                  $section_info['fulfills'] = $this->buildNotes($section_record->fulfills_college_requirements);
                  if ($section_record->course_number > 499) {
                      $section_info['level'] = 'graduate';
                  }
                  else {
                      $section_info['level'] = 'undergraduate';
                  }

                  // create or update the section node
                  if (isset($section_nodes[$key])) {
                      $this->updateSectionNode($section_nodes[$key], $section_info);
                  }
                  else {
                      $section_nodes[$key] = $this->updateSectionNode(NULL, $section_info);
                  }
              }

              // switch user to delete nodes
              // we want the author of this content to be a dedicated user account, penncourse_user
              /* $current_uid = NULL;
              $penncourse_user = user_load_by_name('penncourse_user');
              if ($penncourse_user) {
                  $current_uid = $user->uid;
                  $user = $penncourse_user;
              } */
              // if a section record no longer exists for the corresponding pc_section node, delete the node
              foreach ($section_nodes as $key => $nid) {
                  if (!isset($sections[$key])) {
                    $this->logger->notice('penncourse cron: deleting pc_section node ' . $nid);
                    // $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
                    $node = Node::load($nid);
                    $node->delete();
                    // $storage_handler->delete($entities);
                  }
              }

              // switch user to delete nodes
              /* if ($current_uid !== NULL) {
                  // reload our original user
                  $user = user_load($current_uid);
              } */
          }
      }
  }

  /**
   * Create or update section nodes (pc_section) based on data passed in $values_array
   * @param integer $nid
   * @param array $values_array
   */
  function updateSectionNode($nid = NULL, $values_array) {
      $user = User::load(\Drupal::currentUser()->id());
      // we want the author of this content to be a dedicated user account, penncourse_user
      $current_uid = NULL;
      $penncourse_user = user_load_by_name('penncourse_user');
      if ($penncourse_user) {
          $current_uid = $user->id();
          $user = $penncourse_user;
      }

      if ($nid) {
          $action = 'updating section node: ' . $nid . '[nid] ' . $values_array['section_id'] . '[section_id]';
          $node = Node::load($nid);
      }
      else {
        $action = 'new course node: ' . $values_array['course_id'] . '[course_id]';
        $node = Node::create([
    		  // The node entity bundle.
    		  'type' => 'pc_section',
    		  'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    		  'created' => REQUEST_TIME,
    		  // The user ID.
    		  'uid' => $penncourse_user->id(),
    		  'moderation_state' => 'published',
    		  'title' => $values_array['course_id'] . ' - ' . $values_array['title'],
    		]);
        $node->save();
      }

      $node->setTitle($values_array['course_id'] . ' - ' . $values_array['title']);
      // only update the description from data service if there is not a local description
      if ($node->get('field_pc_local_descr')->getString() != '1') {
        $node->set('field_pc_descr', $values_array['descr']);
      }
      // $node->field_pc_descr[0]['value'] = $values_array['descr']; this field is maintained locally by departments
      $node->set('field_pc_instructors', ['value' => $values_array['instructors'], 'format' => 'penncourse_format']);
      if ($values_array['status'] == 'X') {
          $meetings = 'CANCELED';
      }
      else {
          $meetings = $values_array['meeting'];
      }
      $node->set('field_pc_meeting', ['value' => $meetings, 'format' => 'penncourse_format']);
      // $node->field_pc_meeting->format = 'basic_html'; // we need to set formats too
      $node->set('field_pc_title', $values_array['title']);
      $node->set('field_pc_location', ['value' => $values_array['location'], 'format' => 'penncourse_format']);
      $node->set('field_pc_section_id', $values_array['section_id']);
      // $node->field_pc_course[$node->language][0]['nid'] = $values_array['course'];
      $node->set('field_pc_subj_area', $values_array['subj_area']);
      $node->set('field_pc_term', $values_array['term']);
      $node->set('field_pc_term_session', $values_array['term_session']);
      $node->set('field_pc_activity', $values_array['activity']);
      $node->set('field_pc_course_no', $values_array['course_no']);
      $node->set('field_pc_section_no', $values_array['section_no']);
      $node->set('field_pc_course_int', $values_array['course_no']);
      $node->set('field_pc_section_int', $values_array['section_no']);
      $node->set('field_pc_xlist', ['value' => $values_array['xlist'], 'format' => 'penncourse_format']);
      $node->set('field_pc_syllabus_url', $values_array['syllabus_url']);
      $node->set('field_pc_status', $values_array['status']);
      // $node->set('field_pc_course_id', $values_array['course_id']);
      // $node->field_pc_course[$node->language][0]['target_id'] = $values_array['course_nid'];
      // $node->field_pc_course[$node->language][0]['target_type'] = 'node';
      $node->set('field_pc_level', $values_array['level']);
      $node->set('field_pc_sec_reg_ctrl', ['value' => $values_array['notes'], 'format' => 'penncourse_format']);
      $node->set('field_pc_fulfills', ['value' => $values_array['fulfills'], 'format' => 'penncourse_format']);

      $node->save();
      $this->logger->notice('penncourse cron: ' . $action . '<br />' . Link::fromTextAndUrl($node->getTitle(), $node->toUrl())->toString());
      $nid = $node->id();
      unset($node);

      /* if ($current_uid !== NULL) {
          // reload our original user
          $user = user_load($current_uid);
      } */
      return $nid;
  } // function penncourse_build_section_node

  /**
   * build html markup from array of instructor info
   * @param array
   * @return string xhtml formatted text
   */
  function transformInstructors(array $instructors) {
      $xhtml = '';
      foreach ($instructors as $instructor) {
          if ($instructor->name) {
              // commenting this out because we don't have access to the pennkey data from the web service
              // $xhtml .= '<span class="penncourse-course-instructor" data-pennkey="' . $instructor->pennkey . '">' . $instructor->lastname . ', ' . $instructor->firstname . '</span><br />';
              $xhtml .= '<span class="penncourse-course-instructor" data-pennkey="">' . $instructor->name . '</span><br />';
          }
      }
      // trim the last <br /> from the string
      $xhtml = substr($xhtml, 0, -6);

      // echo $xhtml.chr(10).chr(13);
      return $xhtml;
  }

  /**
   * build html markup from array of course meeting info
   * @param array
   * @return string xhtml formatted text
   */
  function transformMeetings(array $meetings) {
      $xhtml = '';
      foreach ($meetings as $meeting) {
          if ($meeting->meeting_days && $meeting->start_time && $meeting->end_time) {
              $xhtml .= '<span class="penncourse-course-meeting">' . $meeting->meeting_days . ' ' . $meeting->start_time . '-' . $meeting->end_time . '</span><br />';
          }
      }
      // trim the last <br /> from the string
      $xhtml = substr($xhtml, 0, -6);

      return $xhtml;
  }

  /**
   * build html markup from array of course meeting info
   * @param array
   * @return string xhtml formatted text
   */
  function transformLocation(array $meetings) {
      $xhtml = '';
      foreach ($meetings as $meeting) {
          // $xhtml .= '<li><span style="font-weight:bold">Room: </span>'.$meeting->room.' &nbsp; '.$meeting->building.'</li>';
          if ($meeting->building_code && $meeting->room_number) {
              $xhtml .= '<span class="penncourse-course-location">' . $meeting->building_code . ' ' . $meeting->room_number . '</span><br />';
          }
      }
      // trim the last <br /> from the string
      $xhtml = substr($xhtml, 0, -6);

      return $xhtml;
  } // function penncourse_transform_location

  /**
   * build html markup from array of cross listing info
   * @param array
   * @return string xhtml formatted text
   */
  function buildXlists(array $xlists) {
      $result = '';
      foreach ($xlists as $xlist) {
          $result .= '<span class="penncourse-course-xlist">' . $xlist->subject . $xlist->course_id . $xlist->section_id . '</span>, ';
      }
      // trim the last <br /> from the string
      $result = substr($result, 0, -2);

      return $result;
  }

  /**
   * build html markup from array of course note info
   * @param array
   * @return string xhtml formatted text
   */
  function buildNotes(array $notes) {
      $xhtml = '';
      foreach ($notes as $note) {
          $xhtml .= '<span class="penncourse-course-notes">' . $note . '</span><br />';
      }
      // trim the last <br /> from the string
      $xhtml = substr($xhtml, 0, -6);

      return $xhtml;
  }

  /**
   * returns a formatted string of Subject Area name
   * ('ANTH' returns 'Anthropology')
   * @param string $subj_code
   * @return string
   */
  function translateSubject($subj_code) {
    $subj_descr = '';
    // get the subject map
    $subjects = $this->config->get('penncourse_subject_map');

    if ($subjects) {
      $subj_descr = $subjects[$subj_code];
    }
    return $subj_descr;
  }

  /**
   * returns a formatted string of the term name for a given $term_code
   * ('2008C' returns 'Fall 2008')
   * @param string $term_code
   * @return string
   */
  function translateTerm($term_code) {
    $term_name = array('A' => 'Spring', 'B' => 'Summer', 'C' => 'Fall');
    if (is_numeric(substr($term_code, 0, 4)) && ((strtoupper(substr($term_code, 4, 1)) == 'A') || (strtoupper(substr($term_code, 4, 1)) == 'B') || (strtoupper(substr($term_code, 4, 1)) == 'C'))) {
      return $term_name[strtoupper(substr($term_code, 4, 1))] . ' ' . substr($term_code, 0, 4);
    }
    else {
      // invalid code
      return 'Invalid term code';
    }
  }

  /**
   * Return an array of all course terms currently stored in the database
   * @return array
   */
  function getAllTerms() {
      // get database info about course fields
      $db_info = $this->getStorageInfo('pc_course', array('field_pc_term'));

      $sql = sprintf("SELECT DISTINCT     t.%s AS term
                      FROM 			          {%s} t
   					          ORDER BY 	          term DESC",
          $db_info['field_pc_term']['column'],
          $db_info['field_pc_term']['table']);

      $connection = Database::getConnection();
      $results = $connection->query($sql,
        array(),
        array());

      $terms = array();
      foreach ($results as $record) {
          $terms[$record->term] = $this->translateTerm($record->term);
      }

      return $terms;
  }

  /**
   * Return an array of subjects configured for this site
   * @return array
   */
  function getAllSubjects() {
    // get the subjects to process for this site
    $subject_array = explode(' ', trim($this->config->get('penncourse_subject_areas')));

    $subjects = array();
    foreach ($subject_array as $subject_code) {
      $subjects[$subject_code] = $this->translateSubject($subject_code);
    }

    return $subjects;
  }




}
