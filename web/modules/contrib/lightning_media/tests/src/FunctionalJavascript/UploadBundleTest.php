<?php

namespace Drupal\Tests\lightning_media\FunctionalJavascript;

use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * @group lightning_media
 */
class UploadBundleTest extends WebDriverTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_image',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->createMediaType('image', [
      'id' => 'picture',
      'label' => 'Picture',
    ]);
    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_media',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => 'Media',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
            'picture' => 'picture',
          ],
        ],
      ],
    ])->save();

    $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('node.article.default')
      ->setComponent('field_media', [
        'type' => 'entity_browser_entity_reference',
        'settings' => [
          'entity_browser' => 'media_browser',
          'field_widget_display' => 'rendered_entity',
          'field_widget_edit' => TRUE,
          'field_widget_remove' => TRUE,
          'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
          'field_widget_display_settings' => [
            'view_mode' => 'thumbnail',
          ],
          'open' => TRUE,
        ],
        'region' => 'content',
      ])
      ->save();

    $user = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($user);
  }

  /**
   * Tests that select is shown when media bundle is ambiguous.
   */
  public function testUpload() {
    $session = $this->getSession();

    // Create an article with a media via the upload widget.
    $this->drupalGet('node/add/article');
    $this->assertSession()->fieldExists('Title')->setValue('Foo');
    $this->openMediaBrowser();

    $uri = $this->getRandomGenerator()->image('public://test_image.png', '240x240', '640x480');
    $path = $this->container->get('file_system')->realpath($uri);

    $this->assertSession()->fieldExists('input_file')->attachFile($path);
    $this->assertSession()->waitForField('Bundle')->selectOption('Picture');
    $this->assertSession()->waitForField('Name')->setValue('Bar');
    $this->assertSession()->fieldExists('Alternative text')->setValue('Baz');
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    sleep(1);

    $session->switchToIFrame();
    $this->assertSession()->waitForButton('Remove');
    $this->assertSession()->buttonExists('Save')->press();

    // Assert the correct entities are created.
    $node = Node::load(1);
    $this->assertInstanceOf(Node::class, $node);
    /** @var \Drupal\node\NodeInterface $node */
    $this->assertSame('Foo', $node->getTitle());
    $this->assertFalse($node->get('field_media')->isEmpty());
    $this->assertSame('picture', $node->field_media->entity->bundle());
    $this->assertSame('Bar', $node->field_media->entity->getName());
    $this->assertSame('Baz', $node->field_media->entity->field_media_image->alt);
    $this->assertSame('test_image_0.png', $node->field_media->entity->field_media_image->entity->getFilename());
  }

  /**
   * Tests that select is shown after first uploading an incorrect file.
   */
  public function testWrongExtension() {
    $this->drupalGet('node/add/article');
    $this->openMediaBrowser();

    // Alert is displayed when uploading a .txt file.
    file_put_contents('public://test_text.txt', $this->getRandomGenerator()->paragraphs());
    $path = $this->container->get('file_system')->realpath('public://test_text.txt');
    $this->assertSession()->fieldExists('input_file')->attachFile($path);
    $this->assertSession()->waitForElement('css', '[role="alert"]');
    $this->assertSession()->pageTextContains('Error message Only files with the following extensions are allowed');

    // Previous alert gets hidden after uploading .png file.
    $this->getRandomGenerator()->image('public://test_image.png', '240x240', '640x480');
    $path = $this->container->get('file_system')->realpath('public://test_image.png');
    $this->assertSession()->fieldExists('input_file')->attachFile($path);
    $this->assertSession()->waitForField('Bundle');
    $this->assertSession()->elementNotExists('css', '[role="alert"]');
  }

  /**
   * Tests that image resolution changes after selecting bundle.
   */
  public function testResolutionChange() {
    FieldConfig::loadByName('media', 'image', 'image')
      ->setSetting('max_resolution', '100x100')
      ->save();

    $this->drupalGet('node/add/article');
    $this->openMediaBrowser();

    // Upload a 200x200 image.
    $this->getRandomGenerator()->image('public://test_image.png', '200x200', '200x200');
    $path = $this->container->get('file_system')->realpath('public://test_image.png');
    $this->assertSession()->fieldExists('input_file')->attachFile($path);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->elementNotExists('css', '[role="contentinfo"]');
    $this->assertSession()->selectExists('Bundle')->selectOption('Image');
    $this->assertSession()->assertWaitOnAjaxRequest();
    sleep(1);

    // Assert the image resolution is changed to 100x100.
    $this->assertSession()->pageTextContains('Status message The image was resized to fit within the maximum allowed dimensions of 100x100 pixels. The new dimensions of the resized image are 100x100 pixels.');
  }

  /**
   * Opens the media browser.
   *
   * @param bool $switch
   *   (optional) If TRUE, switch into the entity browser frame. Defaults to
   *   TRUE.
   */
  private function openMediaBrowser($switch = TRUE) {
    $this->assertSession()->buttonExists('Add media')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    if ($switch) {
      $this->getSession()->switchToIFrame('entity_browser_iframe_media_browser');
    }
  }

}
