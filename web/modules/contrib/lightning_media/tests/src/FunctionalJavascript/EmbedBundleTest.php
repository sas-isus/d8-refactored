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
class EmbedBundleTest extends WebDriverTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_video',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->createMediaType('video_embed_field', [
      'id' => 'advertisement',
      'label' => 'Advertisement',
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
            'video' => 'video',
            'advertisement' => 'advertisement',
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
  public function testEmbed() {
    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Create an article with a media via the embed widget.
    $this->drupalGet('node/add/article');
    $page->fillField('Title', 'Foo');
    $this->openMediaBrowser();

    $page->clickLink('Create embed');
    $video_url = 'https://www.youtube.com/watch?v=zQ1_IbFFbzA';
    $page->fillField('input', $video_url);
    $assert_session->assertWaitOnAjaxRequest();
    // There are 2 AJAX requests, wait for the second one with sleep.
    sleep(1);
    $page->selectFieldOption('Bundle', 'Advertisement');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->fieldExists('Video Url');
    $page->fillField('Name', 'Bar');
    $page->pressButton('Place');
    $session->switchToIFrame();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->buttonExists('Remove');
    $page->pressButton('Save');

    // Assert the correct entities are created.
    $node = Node::load(1);
    $this->assertInstanceOf(Node::class, $node);
    $this->assertSame('Foo', $node->getTitle());
    $this->assertSame('advertisement', $node->field_media->entity->bundle());
    $this->assertSame('Bar', $node->field_media->entity->label());
    $this->assertSame($video_url, $node->field_media->entity->field_media_video_embed_field->value);
  }

  /**
   * Tests that an error message is displayed for malformed URLs.
   */
  public function testErrorMessages() {
    $this->drupalGet('node/add/article');
    $this->openMediaBrowser();

    // Error message is displayed for malformed URLs.
    $this->assertSession()->elementExists('named', ['link', 'Create embed'])->click();
    $this->assertSession()->fieldExists('input')->setValue('Foo');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldNotExists('Bundle');
    $this->assertError("Error message Input did not match any media types: 'Foo'");

    $this->assertSession()->fieldExists('input')->setValue('');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldNotExists('Bundle');
    $this->assertNoErrors();

    // No error message when URL is valid.
    $this->assertSession()->fieldExists('input')->setValue('https://www.youtube.com/watch?v=zQ1_IbFFbzA');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->selectExists('Bundle');
    $this->assertNoErrors();

    // Rerender the form if URL is changed.
    $this->assertSession()->fieldExists('input')->setValue('Bar');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertError("Error message Input did not match any media types: 'Bar'");
    $this->assertSession()->fieldNotExists('Bundle');
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

  /**
   * Asserts that an error message is present on the page.
   *
   * @param string $message
   *   The message to look for.
   */
  private function assertError($message) {
    $this->assertSession()->elementExists('css', '[role="alert"]');
    $this->assertSession()->pageTextContains($message);
  }

  /**
   * Asserts that there are no error messages present on the page.
   */
  private function assertNoErrors() {
    $this->assertSession()->elementNotExists('css', '[role="alert"]');
  }

}
