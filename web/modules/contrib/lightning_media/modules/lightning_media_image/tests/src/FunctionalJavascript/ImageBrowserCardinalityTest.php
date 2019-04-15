<?php

namespace Drupal\Tests\lightning_media_image\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\lightning_media_image\Traits\ImageBrowserTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group lightning_media
 * @group lightning_media_image
 */
class ImageBrowserCardinalityTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;
  use ImageBrowserTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image_widget_crop',
    'lightning_media_image',
    'node',
  ];

  /**
   * The content type created during the test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  private $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->nodeType = $this->createContentType();

    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_multi_image',
      'entity_type' => 'node',
      'type' => 'image',
      'cardinality' => 3,
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->nodeType->id(),
      'label' => 'Multi-Image',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_unlimited_images',
      'entity_type' => 'node',
      'type' => 'image',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->nodeType->id(),
      'label' => 'Unlimited Images',
    ])->save();

    entity_get_form_display('node', $this->nodeType->id(), 'default')
      ->setComponent('field_multi_image', [
        'type' => 'entity_browser_file',
        'settings' => [
          'entity_browser' => 'image_browser',
          'field_widget_edit' => TRUE,
          'field_widget_remove' => TRUE,
          'view_mode' => 'default',
          'preview_image_style' => 'thumbnail',
          'open' => TRUE,
          'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
        ],
        'region' => 'content',
      ])
      ->setComponent('field_unlimited_images', [
        'type' => 'entity_browser_file',
        'settings' => [
          'entity_browser' => 'image_browser',
          'field_widget_edit' => TRUE,
          'field_widget_remove' => TRUE,
          'view_mode' => 'default',
          'preview_image_style' => 'thumbnail',
          'open' => TRUE,
          'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
        ],
        'region' => 'content',
      ])
      ->save();

    for ($i = 0; $i < 4; $i++) {
      $uri = $this->getRandomGenerator()->image(uniqid('public://random_') . '.png', '240x240', '640x480');

      $file = File::create([
        'uri' => $uri,
      ]);
      $file->setMimeType('image/png');
      $file->setTemporary();
      $file->save();

      $media = Media::create([
        'bundle' => 'image',
        'name' => $this->getRandomGenerator()->name(32),
        'image' => $file->id(),
        'field_media_in_library' => TRUE,
      ]);
      $this->assertSame(SAVED_NEW, $media->save());
    }

    $account = $this->createUser([
      'access media overview',
      'create ' . $this->nodeType->id() . ' content',
      'access image_browser entity browser pages',
    ]);
    $this->drupalLogin($account);

    $GLOBALS['install_state'] = [];
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = entity_load('view', 'media');
    lightning_media_image_view_insert($view);
    unset($GLOBALS['install_state']);

    module_load_install('lightning_media_image');
    lightning_media_image_install();
  }

  /**
   * Tests that multiple cardinality is enforced in the image browser.
   */
  public function testMultipleCardinality() {
    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $session = $this->getSession();
    $page = $session->getPage();

    $this->openImageBrowser('Multi-Image');
    $items = $page->findAll('css', '[data-selectable]');
    $this->assertGreaterThanOrEqual(4, count($items));
    $this->select($items[0]);
    $this->select($items[1]);

    $this->assertSession()->buttonExists('Select')->press();
    $session->switchToIFrame(NULL);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->openImageBrowser('Multi-Image');
    $this->select($items[2]);

    $disabled = $page->findAll('css', '[data-selectable].disabled');
    $this->assertGreaterThanOrEqual(3, count($disabled));
  }

  /**
   * Tests that the image browser respects unlimited cardinality.
   */
  public function testUnlimitedCardinality() {
    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $session = $this->getSession();
    $page = $session->getPage();

    $this->openImageBrowser('Unlimited Images');
    $items = $page->findAll('css', '[data-selectable]');
    $this->assertGreaterThanOrEqual(4, count($items));
    $this->select($items[0]);
    $this->select($items[1]);
    $this->select($items[2]);

    $this->assertSession()->buttonExists('Select')->press();
    $this->getSession()->switchToIFrame(NULL);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->openImageBrowser('Unlimited Images');
    $this->select($items[3]);

    $disabled = $page->findAll('css', '[data-selectable].disabled');
    $this->assertEmpty($disabled);
  }

  /**
   * Selects an item in the image browser.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The item to select.
   */
  private function select(NodeElement $element) {
    $element->click();
    $this->assertSession()->fieldExists('Select this item', $element)->check();
  }

}
