<?php

namespace Drupal\gathercontent\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the GatherContent Mapping entity.
 *
 * @ConfigEntityType(
 *   id = "gathercontent_mapping",
 *   label = @Translation("GatherContent Mapping"),
 *   config_prefix = "gathercontent_mapping",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Mapping extends ConfigEntityBase implements MappingInterface {

  /**
   * The GatherContent Mapping ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The GatherContent Project ID.
   *
   * @var int
   */
  protected $gathercontent_project_id;

  /**
   * The GatherContent Project name.
   *
   * @var string
   */
  protected $gathercontent_project;

  /**
   * The GatherContent Template ID.
   *
   * @var int
   */
  protected $gathercontent_template_id;

  /**
   * The GatherContent Template name.
   *
   * @var string
   */
  protected $gathercontent_template;

  /**
   * Content type machine name.
   *
   * @var string
   */
  protected $content_type;

  /**
   * Content type name.
   *
   * @var string
   */
  protected $content_type_name;

  /**
   * Timestamp of mapping update in Drupal.
   *
   * @var string
   */
  protected $updated_drupal;

  /**
   * Mapping data.
   *
   * @var string
   */
  protected $data;

  /**
   * Template during latest update.
   *
   * @var string
   */
  protected $template;

  /**
   * {@inheritdoc}
   */
  public function getGathercontentTemplateId() {
    return $this->get('gathercontent_template_id');
  }

  /**
   * Set the template used in GatherContent.
   *
   * @param int $gathercontent_template_id
   *   The template id.
   */
  public function setGathercontentTemplateId($gathercontent_template_id) {
    $this->gathercontent_template_id = $gathercontent_template_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getGathercontentProjectId() {
    return $this->get('gathercontent_project_id');
  }

  /**
   * Set the project id used in GatherContent.
   *
   * @param int $gathercontent_project_id
   *   The project id.
   */
  public function setGathercontentProjectId($gathercontent_project_id) {
    $this->gathercontent_project_id = $gathercontent_project_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getGathercontentProject() {
    return $this->get('gathercontent_project');
  }

  /**
   * Set the project name used in GatherContent.
   *
   * @param string $gathercontent_project
   *   The name of the project.
   */
  public function setGathercontentProject($gathercontent_project) {
    $this->gathercontent_project = $gathercontent_project;
  }

  /**
   * {@inheritdoc}
   */
  public function getGathercontentTemplate() {
    return $this->get('gathercontent_template');
  }

  /**
   * Set the template name used in Gathercontent.
   *
   * @param string $gathercontent_template
   *   The name of the template.
   */
  public function setGathercontentTemplate($gathercontent_template) {
    $this->gathercontent_template = $gathercontent_template;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentType() {
    return $this->get('content_type');
  }

  /**
   * {@inheritdoc}
   */
  public function setContentType($content_type) {
    $this->content_type = $content_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentTypeName() {
    return $this->get('content_type_name');
  }

  /**
   * Set the content type name.
   *
   * {@inheritdoc}
   */
  public function setContentTypeName($content_type_name) {
    $this->content_type_name = $content_type_name;
  }

  /**
   * Get the date of the last update.
   *
   * @return string
   *   Userfriendly timestamp of the last update.
   */
  public function getUpdatedDrupal() {
    return $this->get('updated_drupal');
  }

  /**
   * {@inheritdoc}
   */
  public function setUpdatedDrupal($updated_drupal) {
    $this->updated_drupal = $updated_drupal;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedContentType() {
    $content_type = $this->get('content_type_name');
    if (!empty($content_type)) {
      return $content_type;
    }
    else {
      return t('None');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormatterUpdatedDrupal() {
    $updated_drupal = $this->get('updated_drupal');
    if (!empty($updated_drupal)) {
      return \Drupal::service('date.formatter')
        ->format($updated_drupal, 'custom', 'M d, Y - H:i');
    }
    else {
      return t('Never');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    return $this->get('template');
  }

  /**
   * {@inheritdoc}
   */
  public function setTemplate($template) {
    $this->template = $template;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->get('data');
  }

  /**
   * {@inheritdoc}
   */
  public function setData($data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function hasMapping() {
    return !empty($this->get('data'));
  }

}
