<?php

namespace Drupal\penn403\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

class Penn403Form extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'penn403_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('penn403.settings');

    $form['access_contact'] = array(
      '#type' => 'textfield',
      '#title' => 'Contact Email',
      '#default_value' => $config->get('access_contact'),
      '#description' => $this->t('Optionally set an email address to use in
        contact links for messages to users with insufficient privileges. If
        not set, contact links will use the global site administrator email
        address.'),
    );

    $form['auth_login_route'] = array(
      '#type' => 'textfield',
      '#title' => 'External Auth Login Route',
      '#default_value' => $config->get('auth_login_route'),
      '#description' => $this->t('Specify the Drupal route for the external
        authentication login page.'),
    );

    $form['auto_redirect'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Auto Redirect'),
      '#options' => array(
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ),
      '#default_value' => $config->get('auto_redirect'),
      '#description' => $this->t('By default, then Penn 403 module will
        automatically redirect anonymous users to PennKey login if they
        encounter a 403 access denied error. This behavior may not be
        desired in a site that supports mixed authentication. If this
        option is set to No, the user will be presented with a themeable
        403 error page that includes a link to PennKey authentication.'),
    );


    // Fetch roles to populate dropdown
    $role_objects = Role::loadMultiple();
    $role_list = [];

    foreach ($role_objects as $role_key => $role_object) {
    	$role_list[$role_key] = $role_object->get('label');
    }

    // Specify protected role(s)
    $form['authorized_roles'] = array(
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Authorized Roles'),
      '#description' => $this->t('Designate the roles which have access to the gated content'),
      '#options' => $role_list,
      '#default_value' => $config->get('authorized_roles'),
      '#required' => TRUE,
      '#size' => sizeof($role_list),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('penn403.settings');

    $config->set('access_contact', $form_state->getValue('access_contact'));

    $config->set('auth_login_route', $form_state->getValue('auth_login_route'));

    $config->set('auto_redirect', $form_state->getValue('auto_redirect'));

    $role_values = array_values($form_state->getValue('authorized_roles'));
    $config->set('authorized_roles', $role_values);

    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'penn403.settings',
    ];
  }
}
