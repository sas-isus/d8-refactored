<?php

namespace Drupal\google_tag\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines the Google tag manager container settings form.
 */
class ContainerForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_tag_container';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $container = $this->entity;

    // The main premise of entity forms is that we get to work with an entity
    // object at all times instead of checking submitted values from the form
    // state.

    // Build form elements.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => 'Label',
      '#default_value' => $container->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $container->id(),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'containerExists'],
        'replace_pattern' => '[^a-z0-9_.]+',
      ],
    ];

    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Container settings'),
      '#description' => $this->t('The container settings and snippet insertion conditions for this container.'),
      '#attributes' => ['class' => ['google-tag']],
      '#attached' => [
        'library' => ['google_tag/drupal.settings_form'],
      ],
    ];

    $form['general'] = $this->generalFieldset($form_state);
    $form['path'] = $this->pathFieldset($form_state);
    $form['role'] = $this->roleFieldset($form_state);
    $form['status'] = $this->statusFieldset($form_state);
    $form['advanced'] = $this->advancedFieldset($form_state);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => 'Delete',
    ];

    return $form;
  }

  /**
   * Fieldset builder for the container settings form.
   */
  public function generalFieldset(&$form_state) {
    $container = $this->entity;

    // Build form elements.
    $fieldset = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'settings',
    ];

    $fieldset['container_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container ID'),
      '#description' => $this->t('The ID assigned by Google Tag Manager (GTM) for this website container. To get a container ID, <a href="https://tagmanager.google.com/">sign up for GTM</a> and create a container for your website.'),
      '#default_value' => $container->get('container_id'),
      '#attributes' => ['placeholder' => ['GTM-xxxxxx']],
      '#size' => 12,
      '#maxlength' => 15,
      '#required' => TRUE,
    ];

    $fieldset['weight'] = [
      '#type' => 'weight',
      '#title' => 'Weight',
      '#default_value' => $container->get('weight'),
    ];

    return $fieldset;
  }

  /**
   * Fieldset builder for the container settings form.
   */
  public function pathFieldset(&$form_state) {
    $container = $this->entity;

    // Build form elements.
    $description = $this->t('On this and the next two tabs, specify the conditions on which the GTM JavaScript snippet will either be included in or excluded from the page response, thereby enabling or disabling tracking and other analytics. All conditions must be satisfied for the snippet to be included. The snippet will be excluded if any condition is not met.<br /><br />On this tab, specify the path condition.');

    $fieldset = [
      '#type' => 'details',
      '#title' => $this->t('Page paths'),
      '#group' => 'settings',
      '#description' => $description,
    ];

    $fieldset['path_toggle'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add snippet on specific paths'),
      '#options' => [
        GOOGLE_TAG_EXCLUDE_LISTED => $this->t('All paths except the listed paths'),
        GOOGLE_TAG_INCLUDE_LISTED => $this->t('Only the listed paths'),
      ],
      '#default_value' => $container->get('path_toggle'),
    ];

    $args = [
      '%blog' => '/blog',
      '%blog-wildcard' => '/blog/*',
      '%front' => '<front>',
    ];

    $fieldset['path_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Listed paths'),
      '#description' => $this->t('Enter one relative path per line using the "*" character as a wildcard. Example paths are: "%blog" for the blog page, "%blog-wildcard" for each individual blog, and "%front" for the front page.', $args),
      '#default_value' => $container->get('path_list'),
      '#rows' => 10,
    ];

    return $fieldset;
  }

  /**
   * Fieldset builder for the container settings form.
   */
  public function roleFieldset(&$form_state) {
    $container = $this->entity;

    // Build form elements.
    $fieldset = [
      '#type' => 'details',
      '#title' => $this->t('User roles'),
      '#description' => $this->t('On this tab, specify the user role condition.'),
      '#group' => 'settings',
    ];

    $fieldset['role_toggle'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add snippet for specific roles'),
      '#options' => [
        GOOGLE_TAG_EXCLUDE_LISTED => $this->t('All roles except the selected roles'),
        GOOGLE_TAG_INCLUDE_LISTED => $this->t('Only the selected roles'),
      ],
      '#default_value' => $container->get('role_toggle'),
    ];

    $user_roles = array_map(function ($role) {
      return $role->label();
    }, user_roles());

    $fieldset['role_list'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Selected roles'),
      '#default_value' => $container->get('role_list'),
      '#options' => $user_roles,
    ];

    return $fieldset;
  }

  /**
   * Fieldset builder for the container settings form.
   */
  public function statusFieldset(&$form_state) {
    $container = $this->entity;

    // Build form elements.
    $description = $this->t('Enter one response status per line. For more information, refer to the <a href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes">list of HTTP status codes</a>.');

    $fieldset = [
      '#type' => 'details',
      '#title' => $this->t('Response statuses'),
      '#group' => 'settings',
      '#description' => $this->t('On this tab, specify the page response status condition.'),
    ];

    $fieldset['status_toggle'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add snippet for specific statuses'),
      '#options' => [
        GOOGLE_TAG_EXCLUDE_LISTED => $this->t('All statuses except the listed statuses'),
        GOOGLE_TAG_INCLUDE_LISTED => $this->t('Only the listed statuses'),
      ],
      '#default_value' => $container->get('status_toggle'),
    ];

    $fieldset['status_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Listed statuses'),
      '#description' => $description,
      '#default_value' => $container->get('status_list'),
      '#rows' => 5,
    ];

    return $fieldset;
  }

  /**
   * Fieldset builder for the container settings form.
   */
  public function advancedFieldset(&$form_state) {
    $container = $this->entity;

    // Build form elements.
    $fieldset = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#group' => 'settings',
    ];

    $fieldset['data_layer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data layer'),
      '#description' => $this->t('The name of the data layer. Default value is "dataLayer". In most cases, use the default.'),
      '#default_value' => $container->get('data_layer'),
      '#attributes' => ['placeholder' => ['dataLayer']],
      '#required' => TRUE,
    ];

    $fieldset['include_classes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add classes to the data layer'),
      '#description' => $this->t('If checked, then the listed classes will be added to the data layer.'),
      '#default_value' => $container->get('include_classes'),
    ];

    $description = $this->t('The types of tags, triggers, and variables <strong>allowed</strong> on a page. Enter one class per line. For more information, refer to the <a href="https://developers.google.com/tag-manager/devguide#security">developer documentation</a>.');

    $fieldset['whitelist_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('White-listed classes'),
      '#description' => $description,
      '#default_value' => $container->get('whitelist_classes'),
      '#rows' => 5,
      '#states' => $this->statesArray('include_classes'),
    ];

    $fieldset['blacklist_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Black-listed classes'),
      '#description' => $this->t('The types of tags, triggers, and variables <strong>forbidden</strong> on a page. Enter one class per line.'),
      '#default_value' => $container->get('blacklist_classes'),
      '#rows' => 5,
      '#states' => $this->statesArray('include_classes'),
    ];

    $fieldset['include_environment'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include an environment'),
      '#description' => $this->t('If checked, then the applicable snippets will include the environment items below. Enable <strong>only for development</strong> purposes.'),
      '#default_value' => $container->get('include_environment'),
    ];

    $description = $this->t('The environment ID to use with this website container. To get an environment ID, <a href="https://tagmanager.google.com/#/admin">select Environments</a>, create an environment, then click the "Get Snippet" action. The environment ID and token will be in the snippet.');

    $fieldset['environment_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment ID'),
      '#description' => $description,
      '#default_value' => $container->get('environment_id'),
      '#attributes' => ['placeholder' => ['env-x']],
      '#size' => 10,
      '#maxlength' => 7,
      '#states' => $this->statesArray('include_environment'),
    ];

    $fieldset['environment_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment token'),
      '#description' => $this->t('The authentication token for this environment.'),
      '#default_value' => $container->get('environment_token'),
      '#attributes' => ['placeholder' => ['xxxxxxxxxxxxxxxxxxxxxx']],
      '#size' => 20,
      '#maxlength' => 25,
      '#states' => $this->statesArray('include_environment'),
    ];

    return $fieldset;
  }

  /**
   * Returns states array for a form element.
   *
   * @param string $variable
   *   The name of the form element.
   *
   * @return array
   *   The states array.
   */
  public function statesArray($variable) {
    return [
      'required' => [
        ':input[name="' . $variable . '"]' => ['checked' => TRUE],
      ],
      'invisible' => [
        ':input[name="' . $variable . '"]' => ['checked' => FALSE],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Trim the text values.
    $container_id = trim($form_state->getValue('container_id'));
    $environment_id = trim($form_state->getValue('environment_id'));
    $form_state->setValue('data_layer', trim($form_state->getValue('data_layer')));
    $form_state->setValue('path_list', $this->cleanText($form_state->getValue('path_list')));
    $form_state->setValue('status_list', $this->cleanText($form_state->getValue('status_list')));
    $form_state->setValue('whitelist_classes', $this->cleanText($form_state->getValue('whitelist_classes')));
    $form_state->setValue('blacklist_classes', $this->cleanText($form_state->getValue('blacklist_classes')));

    // Replace all types of dashes (n-dash, m-dash, minus) with a normal dash.
    $container_id = str_replace(['–', '—', '−'], '-', $container_id);
    $environment_id = str_replace(['–', '—', '−'], '-', $environment_id);
    $form_state->setValue('container_id', $container_id);
    $form_state->setValue('environment_id', $environment_id);

    $form_state->setValue('role_list', array_filter($form_state->getValue('role_list')));

    if (!preg_match('/^GTM-\w{4,}$/', $container_id)) {
      // @todo Is there a more specific regular expression that applies?
      // @todo Is there a way to validate the container ID?
      // It may be valid but not the correct one for the website.
      $form_state->setError($form['general']['container_id'], $this->t('A valid container ID is case sensitive and formatted like GTM-xxxxxx.'));
    }
    if ($form_state->getValue('include_environment') && !preg_match('/^env-\d{1,}$/', $environment_id)) {
      $form_state->setError($form['advanced']['environment_id'], $this->t('A valid environment ID is case sensitive and formatted like env-x.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo This method is not needed.
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    global $_google_tag_display_message;
    $_google_tag_display_message = TRUE;
    $manager = \Drupal::service('google_tag.container_manager');
    $manager->createAssets($this->entity);

    // Redirect to collection page.
    return new RedirectResponse($this->entity->url('collection', ['absolute' => TRUE]));
  }

  /**
   * Cleans a string representing a list of items.
   *
   * @param string $text
   *   The string to clean.
   * @param string $format
   *   The final format of $text, either 'string' or 'array'.
   *
   * @return string
   *   The clean text.
   */
  public function cleanText($text, $format = 'string') {
    $text = explode("\n", $text);
    $text = array_map('trim', $text);
    $text = array_filter($text, 'trim');
    if ($format == 'string') {
      $text = implode("\n", $text);
    }
    return $text;
  }

  /**
   * Checks if a container machine name is taken.
   *
   * @param string $value
   *   The machine name.
   * @param array $element
   *   An array containing the structure of the 'id' element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   Whether or not the container machine name is taken.
   */
  public function containerExists($value, $element, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $container */
    $container = $form_state->getFormObject()->getEntity();
    return (bool) $this->entityTypeManager->getStorage($container->getEntityTypeId())
      ->getQuery()
      ->condition($container->getEntityType()->getKey('id'), $value)
      ->execute();
  }

}
