<?php

namespace Drupal\imce\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Url;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Imce settings form.
 */
class ImceSettingsForm extends ConfigFormBase {

  /**
   * Manages entity type plugin definitions.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Provides a StreamWrapper manager.
   *
   * @var Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Manages entity type plugin definitions.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   Provides a StreamWrapper manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, StreamWrapperManagerInterface $stream_wrapper_manager) {
    parent::__construct($config_factory);

    $this->entityTypeManager = $entity_type_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'imce_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['imce.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('imce.settings');
    $form['roles_profiles'] = $this->buildRolesProfilesTable($config->get('roles_profiles') ?: []);
    // Common settings container.
    $form['common'] = [
      '#type' => 'details',
      '#title' => $this->t('Common settings'),
    ];
    $form['common']['abs_urls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable absolute URLs'),
      '#description' => $this->t('Make the file manager return absolute file URLs to other applications.'),
      '#default_value' => $config->get('abs_urls'),
    ];
    $form['common']['admin_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use admin theme for IMCE paths'),
      '#default_value' => $config->get('admin_theme'),
      '#description' => $this->t('If you have user interface issues with the active theme you may consider switching to admin theme.'),
    ];
    $form['#attached']['library'][] = 'imce/drupal.imce.admin';
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('imce.settings');
    // Absolute URLs.
    $config->set('abs_urls', $form_state->getValue('abs_urls'));
    // Admin theme.
    $config->set('admin_theme', $form_state->getValue('admin_theme'));
    $roles_profiles = $form_state->getValue('roles_profiles');
    // Filter empty values.
    foreach ($roles_profiles as $rid => &$profiles) {
      if (!$profiles = array_filter($profiles)) {
        unset($roles_profiles[$rid]);
      }
    }
    $config->set('roles_profiles', $roles_profiles);
    $config->save();
    // Warn about anonymous access.
    if (!empty($roles_profiles[RoleInterface::ANONYMOUS_ID])) {
      $this->messenger()
        ->addMessage($this->t('You have enabled anonymous access to the file manager. Please make sure this is not a misconfiguration.'), 'warning');
    }
    parent::submitForm($form, $form_state);
  }

  public function getProfileOptions() {
    // Prepare profile options.
    $options = ['' => '-' . $this->t('None') . '-'];
    foreach ($this->entityTypeManager->getStorage('imce_profile')->loadMultiple() as $pid => $profile) {
      $options[$pid] = $profile->label();
    }
    return $options;
  }

  /**
   * Build header.
   *
   * @return array
   *   Array of headers items.
   */
  public function buildHeaderProfilesTable() : array {
    $wrappers = $this->streamWrapperManager->getNames(StreamWrapperInterface::WRITE_VISIBLE);
    $imce_url = Url::fromRoute('imce.page')->toString();
    $rp_table['#header'] = [$this->t('Role')];
    $default = file_default_scheme();
    foreach ($wrappers as $scheme => $name) {
      $url = $scheme === $default ? $imce_url : $imce_url . '/' . $scheme;
      $rp_table['#header'][]['data'] = ['#markup' => '<a href="' . $url . '">' . Html::escape($name) . '</a>'];
    }

    return $rp_table;
  }

  public function buildRowsProfilesTables($roles, $roles_profiles, $wrappers) {
    // Prepare roles.
    $rp_table = [];
    foreach ($roles as $rid => $role) {
      $rp_table[$rid]['role_name'] = [
        '#plain_text' => $role->label(),
      ];
      foreach ($wrappers as $scheme => $name) {
        $rp_table[$rid][$scheme] = [
          '#type' => 'select',
          '#options' => $this->getProfileOptions(),
          '#default_value' => isset($roles_profiles[$rid][$scheme]) ? $roles_profiles[$rid][$scheme] : '',
        ];
      }
    }

    return $rp_table;
  }

  /**
   * Returns roles-profiles table.
   */
  public function buildRolesProfilesTable(array $roles_profiles) {
    $rp_table = ['#type' => 'table'];

    $roles = user_roles();
    $wrappers = $this->streamWrapperManager->getNames(StreamWrapperInterface::WRITE_VISIBLE);

    $imce_url = Url::fromRoute('imce.page')->toString();

    $rp_table += $this->buildHeaderProfilesTable($wrappers);
    $rp_table += $this->buildRowsProfilesTables($roles, $roles_profiles, $wrappers);

    // Add description.
    $rp_table['#prefix'] = '<h3>' . $this->t('Role-profile assignments') . '</h3>';
    $rp_table['#suffix'] = '<div class="description">' . $this->t('Assign configuration profiles to user roles for available file systems. Users with multiple roles get the bottom most profile.') . ' ' . $this->t('The default file system %name is accessible at :url path.', ['%name' => $wrappers[file_default_scheme()], ':url' => $imce_url]) . '</div>';
    return $rp_table;
  }

}
