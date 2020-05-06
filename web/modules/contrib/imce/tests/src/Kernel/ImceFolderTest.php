<?php

namespace Drupal\Tests\imce\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\imce\ImceFM;
use Drupal\imce\ImceFolder;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Kernel tests for ImceFolder.
 *
 * @group imce
 */
class ImceFolderTest extends KernelTestBase {

  use StringTranslationTrait;
  use UserCreationTrait;

  /**
   * The form delete profile.
   *
   * @var \Drupal\imce\ImceFolder
   */
  protected $imceFolder;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'system',
    'imce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['imce']);
    $this->installEntitySchema('imce_profile');
    $this->installEntitySchema('user');
    $this->setUpCurrentUser();
    $this->imceFolder = new ImceFolder('js', $this->getConf());
    $this->imceFolder->setFm(new ImceFM($this->getConf(), \Drupal::currentUser(), Request::create("/imce")));
    $this->imceFolder->scan();
  }

  public function testFiles() {
    $files = $this->imceFolder->files;
    $this->assertTrue(is_array(($files)));
  }

  public function testSubfolders() {
    $subfolders = $this->imceFolder->subfolders;
    $this->assertTrue(is_array(($subfolders)));
  }

  public function testName() {
    $this->assertTrue(is_string($this->imceFolder->name));
    $this->assertEqual($this->imceFolder->name, 'js');
  }

  public function testPath() {
    $this->imceFolder->setPath('js');
    $path = $this->imceFolder->getPath();
    $this->assertTrue(is_string($path));
  }

  public function testItem() {
    $items = $this->imceFolder->items;
    $this->assertTrue(is_array(($items)));
  }

  public function testScanned() {
    $this->assertTrue(is_bool($this->imceFolder->scanned));
    $this->assertTrue($this->imceFolder->scanned);
  }

  public function getConf() {
    return [
      "extensions" => "*",
      "maxsize" => '104857600.0',
      "quota" => 0,
      "maxwidth" => 0,
      "maxheight" => 0,
      "replace" => 0,
      "thumbnail_style" => "",
      "folders" => [
        "." => [
          "permissions" => [
            "all" => TRUE,
          ],
        ],
      ],
      "pid" => "admin",
      "scheme" => "public",
      "root_uri" => "public://",
      "root_url" => "/sites/default/files",
      "token" => "Vof6182Y9jbV1jFfCU0arR2XDI8qs-OfO8c-R-IbkTg",
    ];
  }

}
