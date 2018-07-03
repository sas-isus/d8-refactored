<?php

namespace Drupal\penncourse\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\penncourse\Service\PenncourseService;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Class PenncourseFilterForm.
 */
class PenncourseFilterForm extends FormBase {

  /**
   * Drupal\penncourse\Service\PenncourseService definition.
   *
   * @var \Drupal\penncourse\Service\PenncourseService
   */
  protected $penncourseService;
  /**
   * Constructs a new PenncourseFilterForm object.
   */
  public function __construct(
    PenncourseService $penncourse_service,
    CurrentPathStack $current_path
  ) {
    $this->penncourseService = $penncourse_service;
    $this->currentPath = $current_path;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('penncourse.service'),
      $container->get('path.current')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'penncourse_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $subjects = $this->penncourseService->getAllSubjects();
    $subjects = array_merge(array('all' => 'All department subjects'), $subjects);

    $path_args = explode('/', $this->currentPath->getPath());

    // set default form values
    if (isset($path_args[2])) {
        $default_term = $path_args[2];
    }
    else {
        $default_term = $this->penncourseService->getFinalTerm();
    }

    if (isset($path_args[3])) {
        $default_subject = $path_args[3];
    }
    else {
        $default_subject = 'all';
    }

    if (isset($path_args[4])) {
        $default_level = $path_args[4];
    }
    else {
        $default_level = 'all';
    }

    $form['term'] = [
      '#type' => 'select',
      '#title' => $this->t('Term'),
      '#options' => $this->penncourseService->getAllTerms(),
      '#size' => 1,
      '#weight' => '0',
      '#default_value' => $default_term,
    ];
    $form['subject'] = [
      '#type' => 'select',
      '#title' => $this->t('Subject'),
      '#options' => $subjects,
      '#size' => 1,
      '#weight' => '0',
      '#default_value' => $default_subject,
    ];
    $form['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Level'),
      '#options' => ['all' => $this->t('All'), 'undergraduate' => $this->t('undergraduate'), 'graduate' => $this->t('graduate')],
      '#size' => 1,
      '#weight' => '0',
      '#default_value' => $default_level,
    ];
    // $form['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Submit'),
    // ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
