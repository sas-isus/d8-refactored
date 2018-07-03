<?php

namespace Drupal\penncourse\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\penncourse\Service\PenncourseService;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Class PenncourseController.
 */
class PenncourseController extends ControllerBase {

  /**
   * The Penncourse helper service.
   *
   * @var \Drupal\penncourse\Service\PenncourseService
   */
  protected $penncourse;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new PenncourseController object.
   */
  // public function __construct(ConfigFactory $config_factory) {
  public function __construct(ConfigFactory $config_factory, PenncourseService $penncourse, Renderer $renderer, CurrentPathStack $current_path) {
    $this->configFactory = $config_factory;
    $this->penncourse = $penncourse;
    $this->renderer = $renderer;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $config_factory = $container->get('config.factory');
    $penncourse = $container->get('penncourse.service');
    $renderer = $container->get('renderer');
    $currentPath = $container->get('path.current');
    return new static(
      $config_factory,
      $penncourse,
      $renderer,
      $currentPath
    );
  }

  /**
   * Test.
   *
   * @return string
   *   Return Hello string.
   */
  public function test() {
    $subj_areas = $this->penncourse->test();
    // $subj_areas = \Drupal::service('penncourse.service')->test();

    return [
      '#type' => 'markup',
      '#markup' => $subj_areas
    ];
  }

  /**
   * Default route response, redirects to final term
   * @return RedirectResponse
   */
  public function viewCourseDefault() {
    $final_term = $this->penncourse->getFinalTerm();

    return $this->redirect('penncourse.view', ['term' => $final_term]);;
  }

  /**
   * View courses
   * @param $term
   * @param $subj_code
   * @param $level
   * @return array
   *   Return course listings.
   */
  public function viewCourseTable($term, $subj_code = 'all', $level = 'all') {
    $content = [];
    // get renderable array for view
    $view = views_embed_view('pc_section_table', 'default', $term, $subj_code, $level);
    // render view
    $content['#markup'] = $this->renderer->render($view);
    $content['#attached']['library'][] = 'penncourse/penncourse-table';

    return $content;
  }

  /**
   * Generate title for course listing display
   * @param $term
   * @param $subj_code
   * @param $level
   * @return string
   *   Return page title.
   */
  public function viewCourseTableTitle($term, $subj_code = 'all', $level = 'all') {
    $title = '';
    if ((!$level || ($level == 'all')) && (!$subj_code || ($subj_code == 'all'))) {
        $title = 'Courses for ';
    }
    else {
        if ($subj_code && ($subj_code != 'all')) {
            $title = $this->penncourse->translateSubject($subj_code);
        }
        if (($level == 'graduate') || ($level == 'undergraduate')) {
            $title = ucfirst($level) . ' ' . $title;
        }
        $title = $title . ' courses for ';
    }
    $title = $title . $this->penncourse->translateTerm($term);
    return $title;
  }
}
