<?php

namespace Drupal\permissions_by_term\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class SettingsForm extends ConfigFormBase {

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'permissions_by_term_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'permissions_by_term.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeBundleInfo = $container->get('entity_type.bundle.info');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['require_all_terms_granted'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Require all terms granted'),
      '#description'   => t('By default users are granted access content, as long they have access to a <strong>single</strong>
related taxonomy term. If the <strong>require all terms granted</strong> option is checked, they must
have access to <strong>all</strong> related taxonomy terms to access an node.'),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('require_all_terms_granted'),
    ];

    $form['permission_mode'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Permission mode'),
      '#description'   => t('This mode makes nodes accessible (view and edit) only, if editors have been explicitly granted the permission to them. Users won\'t have access to nodes matching any of the following conditions:
<br />- nodes without any terms
<br />- nodes without any terms which grant them permission'),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('permission_mode'),
    ];

    $form['disable_node_access_records'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Disable node access records'),
      '#description'   => t('By disabling node access records, nodes won\'t be hidden in:
<br />- listings made by the Views module (e.g. search result pages)
<br />- menus
<br />- other Drupal core systems, which are based on <a href="https://www.drupal.org/docs/8/modules/permissions-by-term#s-node-access-records" target="_blank" title="Node Access records documentation">Node Access records</a><br />
This setting can be useful, if you just want to restrict nodes on node view and
node edit. Like hiding unpublished nodes from editors during a content
moderation workflow. Disabling node access records will save you some time on
node save and taxonomy save, since the node access records must not be rebuild. Also it will provide major performance
benefit on large, non-cached content listings like the "/admin/content" page. If you want to restrict nodes on your
overall website and you are using a warmed page cache, then it is recommended to leave this setting disabled.'),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('disable_node_access_records'),
    ];

    $form['target_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Limit by taxonomy vocabularies'),
      '#description' => $this->t('Whether to limit permissions management and search by selected taxonomy vocabularies. If left empty, all taxonomy vocabularies are allowed.'),
      '#options' => $this->getTaxonomyVocabularyOptions(),
      '#default_value' => $this->config('permissions_by_term.settings')->get('target_bundles') ?? [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Get list of vocabularies options.
   *
   * @return array
   *   List of options.
   */
  protected function getTaxonomyVocabularyOptions() {
    return array_map(function ($bundle_info) {
      return $bundle_info['label'];
    }, $this->entityTypeBundleInfo->getBundleInfo('taxonomy_term'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()
      ->getEditable('permissions_by_term.settings')
      ->set('require_all_terms_granted', $form_state->getValue('require_all_terms_granted'))
      ->save();

    \Drupal::configFactory()
      ->getEditable('permissions_by_term.settings')
      ->set('permission_mode', $form_state->getValue('permission_mode'))
      ->save();

    if ($form_state->getValue('disable_node_access_records') && !\Drupal::configFactory()
        ->getEditable('permissions_by_term.settings')
        ->get('disable_node_access_records')) {
      node_access_rebuild(true);
    }

    if (!$form_state->getValue('disable_node_access_records') && \Drupal::configFactory()
        ->getEditable('permissions_by_term.settings')
        ->get('disable_node_access_records')) {
      node_access_rebuild(true);
    }

    \Drupal::configFactory()
      ->getEditable('permissions_by_term.settings')
      ->set('disable_node_access_records', $form_state->getValue('disable_node_access_records'))
      ->save();

    $bundles = array_filter($form_state->getValue('target_bundles'));
    $this->configFactory
      ->getEditable('permissions_by_term.settings')
      ->set('target_bundles', array_values($bundles))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
