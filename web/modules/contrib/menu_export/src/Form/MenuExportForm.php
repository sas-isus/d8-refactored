<?php

namespace Drupal\menu_export\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\system\Entity\Menu;

/**
 * Configure Menu Export settings.
 */
class MenuExportForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_export_form';
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
    $form = parent::buildForm($form, $form_state);
    $menus = $this->config('menu_export.settings')
      ->get('menus');
    $menuEnt = Menu::loadMultiple($menus);
    $menuData = array_map(function ($menuEnt) {
      return $menuEnt->label();
    }, $menuEnt);
    $form['menus_to_export'] = [
      '#theme'=>'item_list',
      '#title'=>$this->t('Menus to Export'),
      '#items'=>$menuData,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export Selected Menu Links'),
    ];
    return $form;
  }

  /**
   * Custom form validation for email.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return parent::validateForm($form,$form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
		$this->exportMenus();
		drupal_set_message('Menu Items exported successfully','status');
    parent::submitForm($form, $form_state);
  }

	protected function exportMenus(){
	  $menus = $this->config('menu_export.settings')
    	->get('menus');
		if(empty($menus)){
			return false;
		}

		$config = $this->config('menu_export.export_data');
		$config->delete()->save();
		foreach($menus as $menu){
			$menuLinkIds = \Drupal::entityQuery('menu_link_content')
				->condition('menu_name',$menu)
				->execute();
			$menuLinks = \Drupal\menu_link_content\Entity\MenuLinkContent::loadMultiple($menuLinkIds);
			foreach($menuLinks as $link){
				if(!empty($link)){
					$linkArray = $link->toArray();
					foreach($linkArray as $key=>$linkArrayItem){
						$linkData[$key] = reset($linkArrayItem);
					}
					//$data[$link->id()] = $linkData;
					$config->set($link->id(),$linkData);
					unset($linkData);
				}
			}
		}
		$config->save();
		return true;
	}

}
