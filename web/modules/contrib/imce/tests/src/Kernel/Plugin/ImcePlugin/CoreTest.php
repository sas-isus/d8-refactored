<?php

namespace Drupal\Tests\imce\Kernel\Plugin\ImcePlugin;

use Drupal\imce\ImceFolder;
use Drupal\imce\Plugin\ImcePlugin\Core;
use Drupal\Tests\imce\Kernel\Plugin\KernelTestBasePlugin;

/**
 * Kernel tests for Imce plugins for Imce Plugin Core.
 *
 * @group imce
 */
class CoreTest extends KernelTestBasePlugin {

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\ImcePlugin\Core
   */
  public $core;

  /**
   * The Imce file manager.
   *
   * @var \Drupal\imce\ImceFM
   */
  public $imceFM;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'config',
    'file',
    'system',
    'imce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->imceFM = $this->getImceFM();

    $this->core = new Core([], 'core', $this->getPluginDefinations());
    $this->setParametersRequest();
    $this->setActiveFolder();

    $this->core->opBrowse($this->imceFM);
  }

  /**
   * Set the active folder.
   */
  public function setActiveFolder() {
    $this->imceFM->activeFolder = new ImceFolder('.', $this->getConf());
    $this->imceFM->activeFolder->setPath('.');
    $this->imceFM->activeFolder->setFm($this->imceFM);
  }

  /**
   * Set the request parameters to browser operation.
   */
  public function setParametersRequest() {
    $this->imceFM->request->request->add([
      'jsop' => 'browser',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
    ]);
  }

  /**
   * Get plugins definations.
   *
   * @return array
   *   Return plugins definations.
   */
  public function getPluginDefinations() {
    return [
      'weight' => -99,
      'operations' => [
        'browse' => "opBrowse",
        'uuid' => "opUuid",
      ],
      'id' => "core",
      'label' => "Core",
      'class' => "Drupal\imce\Plugin\ImcePlugin\Core",
      'provider' => "imce",
    ];
  }

  /**
   * The get settings.
   *
   * @return array
   *   Return settings array.
   */
  public function getConf() {
    return [
      'permissions' => ['all' => TRUE],
    ];
  }

  /**
   * Test ImceFM::tree.
   */
  public function testTree() {
    $this->assertTrue(is_array($this->imceFM->tree));
    $this->assert((count($this->imceFM->tree) > 0));
  }

  /**
   * Test subFolders.
   */
  public function testSubfolders() {
    $subFolders = $this->imceFM->activeFolder->subfolders;
    $this->assertTrue(is_array($subFolders));
    $this->assert((count($subFolders) > 0));
  }

  /**
   * Test Core::permissionInfo()
   */
  public function testPermissionInfo() {
    $permissionInfo = $this->core->permissionInfo();
    $this->assertTrue(is_array($permissionInfo));
    $this->assertTrue(in_array('Browse files', $permissionInfo));
    $this->assertTrue(in_array('Browse subfolders', $permissionInfo));
  }

  /**
   * Test scan().
   */
  public function testScan() {
    $this->assertTrue(is_bool($this->imceFM->activeFolder->scanned));
    $this->assertTrue($this->imceFM->activeFolder->scanned);
  }

}
