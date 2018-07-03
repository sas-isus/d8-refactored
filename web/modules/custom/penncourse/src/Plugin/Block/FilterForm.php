<?php

namespace Drupal\penncourse\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\penncourse\Service\PenncourseService;
use Drupal\Core\Render\Renderer;

/**
 * Provides a 'FilterForm' block.
 *
 * @Block(
 *  id = "penncourse_filter_form",
 *  admin_label = @Translation("Course Filter"),
 * )
 */
class FilterForm extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\penncourse\Service\PenncourseService definition.
   *
   * @var \Drupal\penncourse\Service\PenncourseService
   */
  protected $penncourseService;
  /**
   * Constructs a new FilterForm object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PenncourseService $penncourse_service,
    Renderer $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->penncourseService = $penncourse_service;
    $this->renderer = $renderer;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('penncourse.service'),
      $container->get('renderer')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    // $form = \Drupal::formBuilder()->getForm('Drupal\penncourse\Form\PenncourseFilterForm');
    // return $form;
    $build = [];
    $build['#markup'] = $this->renderer->render(\Drupal::formBuilder()->getForm('Drupal\penncourse\Form\PenncourseFilterForm'));
    $build['#attached']['library'][] = 'penncourse/penncourse-form';

    return $build;
  }

}
