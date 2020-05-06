<?php

namespace Drupal\Tests\imce\Kernel\Plugin\ImcePlugin;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\imce\ImceFolder;
use Drupal\imce\Plugin\ImcePlugin\Resize;
use Drupal\Tests\imce\Kernel\Plugin\KernelTestBasePlugin;

/**
 * Kernel tests for Imce plugins for Imce Plugin Core.
 *
 * @group imce
 */
class ResizeTest extends KernelTestBasePlugin {

  use StringTranslationTrait;

  /**
   * The Imce ckeditor plugin.
   *
   * @var \Drupal\imce\Plugin\ImcePlugin\Resize
   */
  public $resize;

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
    $this->getTestFileUri();
    $this->resize = new Resize([], 'resize', $this->getPluginDefinations());
    $this->setParametersRequest();
    $this->setActiveFolder();
    $this->setSelection();
  }

  /**
   * This method will be removed.
   */
  public function test() {
    $this->assertEquals('test', 'test');
  }

  /**
   * Set the ImceFM::selection[].
   */
  public function setSelection() {
    $this->imceFM->selection[] = $this->imceFM->createItem(
      'file', "ciandt.jpg", ['path' => '.']
    );
    $this->imceFM->selection[0]->parent = new ImceFolder('.', $this->getConf());
    $this->imceFM->selection[0]->parent->setFm($this->imceFM);
    $this->imceFM->selection[0]->parent->setPath('.');
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
      'jsop' => 'resize',
      'token' => 'LLuA1R0aUOzoduSJkJxN5aoHVdJnQk8LbTBgdivOU4Y',
      'active_path' => '.',
      'selection' => [
        './ciandt.jpg',
      ],
      'width' => '315',
      'height' => '210',
      'copy' => '0',
    ]);
  }

  /**
   * Set the parameter copy.
   */
  public function setParameterCopy($copy) {
    $this->imceFM->request->request->add(['copy' => $copy]);
  }

  /**
   * Get plugins definations to new folder.
   */
  public function getPluginDefinations() {
    return [
      'weight' => 0,
      'operations' => [
        'resize' => 'opResize',
      ],
      'id' => 'resize',
      'label' => 'Resize',
      'class' => 'Drupal\imce\Plugin\ImcePlugin\Resize',
      'provider' => 'imce',
    ];
  }

  /**
   * Test Resize::permissionInfo()
   */
  public function testPermissiomInfo() {
    $permissionInfo = $this->resize->permissionInfo();
    $this->assertTrue(is_array($permissionInfo));
    $this->assertTrue(in_array($this->t('Resize images'), $permissionInfo));
  }

  /**
   * Test resizing the image by making a image copy.
   */
  public function testResizeImageWithCopy() {
    $this->setParameterCopy(1);
    $this->resize->opResize($this->imceFM);
    list($width, $height) = getimagesize(PublicStream::basePath() . '/ciandt_0.jpg');
    $this->assertEqual($width, 315);
    $this->assertEqual($height, 210);
  }

  /**
   * Test image resizing without copy.
   */
  public function testResizeImageWithoutCopy() {
    $this->setParameterCopy(0);
    $this->resize->opResize($this->imceFM);
    list($width, $height) = getimagesize(PublicStream::basePath() . '/ciandt.jpg');
    $this->assertEqual($width, 315);
    $this->assertEqual($height, 210);
  }

}
