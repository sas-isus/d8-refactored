<?php

namespace Drupal\Tests\imce\Kernel\Plugin\ImcePlugin;

use Drupal\imce\Imce;
use Drupal\imce\ImceFolder;
use Drupal\imce\Plugin\ImcePlugin\Newfolder;
use Drupal\Tests\imce\Kernel\Plugin\KernelTestBasePlugin;

/**
 * Kernel tests for Imce plugins for Imce Plugin NewFolder.
 *
 * @group imce
 */
class NewFolderTest extends KernelTestBasePlugin {

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\ImcePlugin\Newfolder
   */
  public $newFolder;

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

    $this->newFolder = new Newfolder([], 'newfolder', $this->getPluginDefinations());
    $this->setParametersRequest();
    $this->setActiveFolder();

    $this->newFolder->opNewfolder($this->imceFM);
  }

  /**
   * Get permissions settings.
   *
   * @return array
   *   Return the array with permissions.
   */
  public function getConf() {
    return [
      'permissions' => ['all' => TRUE],
    ];
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
   * Set the request parameters.
   */
  public function setParametersRequest() {
    $this->imceFM->request->request->add([
      'jsop' => 'newfolder',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
      'newfolder' => 'folder-test',
    ]);
  }

  /**
   * Get plugins definations to new folder.
   */
  public function getPluginDefinations() {
    return [
      'weight' => '-15',
      'operations' => [
        'newfolder' => "opNewfolder",
      ],
      'id' => "newfolder",
      'label' => "New Folder",
      'class' => "Drupal\imce\Plugin\ImcePlugin\Newfolder",
      'provider' => "imce",
    ];
  }

  /**
   * Test to NewFolder::permissionInfo().
   */
  public function testPermissiomInfo() {
    $permissionInfo = $this->newFolder->permissionInfo();
    $this->assertTrue(is_array($permissionInfo));
    $this->assertTrue(in_array('Create subfolders', $permissionInfo));
  }

  /**
   * Test if folder was created.
   */
  public function testFolderCreate() {
    $uriFolder = Imce::joinPaths(
      $this->imceFM->activeFolder->getUri(), $this->imceFM->getPost('newfolder')
    );

    $this->assertTrue(is_string($uriFolder));
    $this->assertTrue(file_exists($uriFolder));
  }

}
