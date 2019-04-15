<?php

namespace Drupal\Tests\lightning_media\FunctionalJavascript;

use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\quickedit\FunctionalJavascript\QuickEditJavascriptTestBase;

/**
 * @group lightning_media
 */
class QuickEditMediaBrowserTest extends QuickEditJavascriptTestBase {

  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'file',
    'lightning_page',
    'lightning_media_image',
    'contextual',
    'quickedit',
    'toolbar',
  ];

  /**
   * The tested node's id.
   *
   * @var string
   */
  protected $nodeId;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create image field on page.
    $this->createEntityReferenceField(
      'node',
      'page',
      'field_image',
      'Image',
      'media',
      'default:media',
      [
        'target_bundles' => [
          'image' => 'image',
        ],
      ],
      1
    );

    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager
      ->getStorage('entity_form_display')
      ->load('node.page.default')
      ->setComponent('field_image', [
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
    $entity_type_manager
      ->getStorage('entity_view_display')
      ->load('node.page.default')
      ->setComponent('field_image', [
        'type' => 'entity_reference_entity_view',
        'label' => 'above',
        'settings' => [
          'view_mode' => 'default',
        ],
      ])
      ->save();

    // Create image.
    $uri = $this->getRandomGenerator()->image('public://test_image.png', '240x240', '640x480');
    $file = File::create([
      'uri' => $uri,
      'filename' => 'test_image.png',
    ]);
    $file->setMimeType('image/png');
    $file->setTemporary();
    $file->save();
    $image = Media::create([
      'bundle' => 'image',
      'name' => 'Foo',
      'image' => [
        'target_id' => $file->id(),
        'alt' => 'Test Alt 1',
      ],
      'field_media_in_library' => TRUE,
    ]);
    $image->save();

    // Create page.
    $node = $this->drupalCreateNode([
      'field_image' => [
        'target_id' => $image->id(),
      ],
    ]);
    $this->nodeId = $node->id();

    // Navigate to page.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $url = $node->toUrl()->toString();
    $this->drupalGet($url);
  }

  /**
   * Awaits Quick Edit to be initiated for a page with nested entities.
   *
   * @see ::awaitQuickEditForEntity()
   */
  protected function awaitQuickEditForPage() {
    $condition = <<< JS
document.querySelector('[data-quickedit-entity-id="node/$this->nodeId"] .quickedit') !== null
JS;
    $this->assertJsCondition($condition);
  }

  /**
   * Tests if media can be edited and removed with Quick Edit.
   */
  public function testEditAndRemove() {
    $assert_session = $this->assertSession();

    // Initialize Quick Edit.
    $this->awaitQuickEditForPage();
    $this->startQuickEditViaToolbar('node', $this->nodeId, 0);

    // Click the image field.
    $assert_session->waitForElementVisible('css', sprintf('[data-quickedit-field-id="node/%d/field_image/en/full"]', $this->nodeId));
    $this->click(sprintf('[data-quickedit-field-id="node/%d/field_image/en/full"]', $this->nodeId));

    // Click edit.
    $edit = $assert_session->waitForElement('css', 'input[value="Edit"]');
    $edit->click();

    // Change name.
    $assert_session->waitForElement('css', '.ui-dialog');
    $name = $assert_session->waitForField('Name');
    $name->setValue('Bar');

    // Remove image.
    $remove = $assert_session->waitForButton('image_0_remove_button');
    $remove->click();

    // Add new image.
    $uri = $this->getRandomGenerator()->image('public://test_image_2.png', '240x240', '640x480');
    $path = $this->container->get('file_system')->realpath($uri);
    $file = $assert_session->waitForField('Image');
    $file->attachFile($path);

    // Add alternative text.
    $alt = $assert_session->waitForField('Alternative text');
    $alt->setValue('Test Alt 2');

    // Click save.
    $save = $assert_session->waitForElement('css', '.ui-dialog-buttonset .button');
    $save->click();

    // Assert image has changed.
    $this->assertJsCondition('document.querySelector(".ui-dialog") === null');
    $image = Media::load(1);
    $this->assertSame('Bar', $image->getName());

    // Save Quick Edit.
    $this->saveQuickEdit();
    $this->assertJsCondition("Drupal.quickedit.collections.entities.get('node/$this->nodeId[0]').get('state') === 'closed'");

    // Assert new image is displayed.
    $assert_session->elementNotExists('css', 'img[alt="Test Alt 1"]');
    $assert_session->elementExists('css', 'img[alt="Test Alt 2"]');
    $assert_session->elementNotExists('css', 'img[src*="test_image.png"]');
    $assert_session->elementExists('css', 'img[src*="test_image_2.png"]');

    // Restart Quick Edit.
    $this->click('#toolbar-bar .contextual-toolbar-tab button');
    $this->startQuickEditViaToolbar('node', $this->nodeId, 0);

    // Click the image field.
    $assert_session->waitForElementVisible('css', sprintf('[data-quickedit-field-id="node/%d/field_image/en/full"]', $this->nodeId));
    $this->click(sprintf('[data-quickedit-field-id="node/%d/field_image/en/full"]', $this->nodeId));

    // Remove the image.
    $remove = $assert_session->waitForButton('Remove');
    $remove->click();

    // Save Quick Edit.
    $assert_session->waitForButton('Place');
    $this->saveQuickEdit();
    $this->assertJsCondition("Drupal.quickedit.collections.entities.get('node/$this->nodeId[0]').get('state') === 'closed'");

    // Assert image is removed from node.
    $assert_session->elementNotExists('css', 'img[src*="test_image_2.png"]');
    $node = Node::load($this->nodeId);
    $this->assertEmpty($node->get('field_image')->getValue());
  }

}
