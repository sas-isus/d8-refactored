<?php

namespace Drupal\permissions_by_term\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class SettingsForm extends ConfigFormBase {

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $description_require_all_terms_granted = <<<EOT
By default users are granted access content, as long they have access to a <strong>single</strong>
related taxonomy term. If the <strong>require all terms granted</strong> option is checked, they must
have access to <strong>all</strong> related taxonomy terms to access an node.
EOT;

    $description_permission_mode = <<<EOT
This mode makes nodes accessible (view and edit) only, if editors have been explicitly granted the permission to them. Users won't have access to nodes matching any of the following conditions:
<br />- nodes without any terms
<br />- nodes without any terms which grant them permission
EOT;

    $description_disable_node_access_records = <<<EOT
By disabling node access records, PbT won't hide nodes in:
<br />- listings made by the Views module (e.g. search result pages)
<br />- menus<br />
This setting can be useful, if you just want to restrict nodes on node view and 
node edit. Like hiding unpublished nodes from editors during a content 
moderation workflow. Disabling node access records will save you some time on 
node save and taxonomy save, since the node access records must not be rebuild.
EOT;

    $form['require_all_terms_granted'] = [
      '#type' => 'checkbox',
      '#title' => t('Require all terms granted'),
      '#description' => t($description_require_all_terms_granted),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('require_all_terms_granted'),
    ];

    $form['permission_mode'] = [
      '#type' => 'checkbox',
      '#title' => t('Permission mode'),
      '#description' => t($description_permission_mode),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('permission_mode'),
    ];

    $form['disable_node_access_records'] = [
      '#type' => 'checkbox',
      '#title' => t('Disable node access records'),
      '#description' => t($description_disable_node_access_records),
      '#default_value' => \Drupal::config('permissions_by_term.settings')->get('disable_node_access_records'),
    ];

    return parent::buildForm($form, $form_state);
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

    parent::submitForm($form, $form_state);
  }

}
