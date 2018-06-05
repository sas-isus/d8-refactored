<?php

namespace Drupal\menu_export\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;

/**
 * Configure Menu Export settings.
 */
class MenuExportConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_export_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'menu_export.settings',
      'menu_export.export_data'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('menu_export.settings');
    $menuIds = \Drupal::entityQuery('menu')->execute();
    $menuEntities = Menu::loadMultiple($menuIds);
    foreach ($menuEntities as $menu) {
      $menuNames[$menu->id()] = $menu->label();
    }
    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<strong>Caution:</strong> Select only the menus which are consistent in all the environments(dev,staging,prod).')
    ];
    $form['menus'] = [
      '#title' => $this->t('Menus to Export'),
      '#type' => 'checkboxes',
      '#options' => $menuNames,
      '#default_value' => $config->get('menus') ? $config->get('menus') : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Custom form validation for menus.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('menus') as $val) {
      if ($val)
        $menu[] = $val;
    }
    $form_state->setValue('menus', $menu);
    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('menu_export.settings')
        ->set('menus', $form_state->getValue('menus'))
        ->save();
    $this->backupMenus($form_state->getValue('menus'));
    parent::submitForm($form, $form_state);
  }

  protected function backupMenus($menus) {
    $config = $this->config('menu_export.export_data');
    $config->delete()->save();
    if (empty($menus)) {
      return false;
    }
    foreach ($menus as $menu) {
      $menuLinkIds = \Drupal::entityQuery('menu_link_content')
          ->condition('menu_name', $menu)
          ->execute();
      $menuLinks = \Drupal\menu_link_content\Entity\MenuLinkContent::loadMultiple($menuLinkIds);
      $saveMenu[] = '';
      foreach ($menuLinks as $link) {
        if (!empty($link)) {
          $linkArray = $link->toArray();
          unset($linkArray['id']);
          foreach ($linkArray as $key => $linkArrayItem) {
            $linkData[$key] = reset($linkArrayItem);
          }
          $saveMenu[$link->uuid()] = serialize($linkData);
          //$data[$link->id()] = $linkData;

          unset($linkData);
        }
      }
      $config->set($menu, $saveMenu);
      unset($saveMenu);
    }
    $config->save();
    return true;
  }

}
