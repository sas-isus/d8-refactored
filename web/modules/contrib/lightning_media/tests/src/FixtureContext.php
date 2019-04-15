<?php

namespace Drupal\Tests\lightning_media;

use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\lightning_core\FixtureBase;

final class FixtureContext extends FixtureBase {

  /**
   * @BeforeScenario
   */
  public function setUp() {
    $this->installModule('image_widget_crop');
    $this->installModule('lightning_page');

    // Ensure that the page content type and all related configuration will be
    // deleted when Lightning Page is uninstalled.
    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load('page');
    $dependencies = $node_type->getDependencies();
    $dependencies['enforced']['module'][] = 'lightning_page';
    $node_type->set('dependencies', $dependencies)->save();

    module_load_install('lightning_media_image');
    lightning_media_image_install();

    $config = Config::forModule('lightning_media')->optional();

    /** @var \Drupal\user\RoleInterface $role */
    $role = $config->getEntity('user_role', 'media_creator');
    $role
      ->grantPermission('access content')
      ->grantPermission('access content overview')
      ->grantPermission('access image_browser entity browser pages')
      ->grantPermission('create page content')
      ->grantPermission('edit own page content')
      ->grantPermission('use text format rich_text')
      ->grantPermission('view own unpublished content');
    $this->save($role);

    $role = $config->getEntity('user_role', 'media_manager');
    $this->save($role);

    $this->installTheme('bartik');
    $this->installTheme('seven');

    $this->config('system.theme')
      ->set('default', 'bartik')
      ->set('admin', 'seven')
      ->save();

    $this->config('node.settings')->set('use_admin_theme', TRUE)->save();

    // Sentence-case the label of the media browser's embed code widget. Yes,
    // not doing this can cause tests to fail.
    $this->config('entity_browser.browser.media_browser')
      ->set('widgets.8b142f33-59d1-47b1-9e3a-4ae85d8376fa.label', 'Create embed')
      ->save();

    // Cache the original view.
    $this->config('views.view.media');

    $GLOBALS['install_state'] = [];
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = entity_load('view', 'media');
    lightning_media_view_insert($view);
    lightning_media_image_view_insert($view);
    unset($GLOBALS['install_state']);
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    parent::tearDown();
  }

}
