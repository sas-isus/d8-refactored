<?php

namespace Drupal\Tests\lightning_media\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * @group lightning_media
 */
class MediaBrowserTest extends BrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'field_ui',
    'lightning_media_image',
    'lightning_media_twitter',
    'node',
  ];

  /**
   * Slick Entity Reference has a schema error.
   *
   * @todo Remove when depending on slick_entityreference 1.2 or later.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Tests that the media browser is the default widget for a new media
   * reference field.
   */
  public function testNewMediaReferenceField() {
    $this->drupalPlaceBlock('local_actions_block');

    $node_type = $this->drupalCreateContentType()->id();
    $media_type = $this->createMediaType('image')->id();

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->drupalGet("/admin/structure/types/manage/$node_type/fields");
    $this->clickLink('Add field');
    $values = [
      'new_storage_type' => 'field_ui:entity_reference:media',
      'label' => 'Foobar',
      'field_name' => 'foobar',
    ];
    $this->drupalPostForm(NULL, $values, 'Save and continue');
    $this->drupalPostForm(NULL, [], 'Save field settings');
    $values = [
      "settings[handler_settings][target_bundles][$media_type]" => $media_type,
    ];
    $this->drupalPostForm(NULL, $values, 'Save settings');

    $component = entity_get_form_display('node', $node_type, 'default')
      ->getComponent('field_foobar');

    $this->assertInternalType('array', $component);
    $this->assertSame('entity_browser_entity_reference', $component['type']);
    $this->assertSame('media_browser', $component['settings']['entity_browser']);
  }

  /**
   * Tests validation in the upload widget of the media browser.
   */
  public function testUploadValidation() {
    $account = $this->createUser([
      'access media_browser entity browser pages',
      'create media',
    ]);
    $this->drupalLogin($account);

    $this->createMediaType('image');

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);

    // The widget should require a file.
    $this->assertSession()->fieldExists('File');
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('You must upload a file.');

    // The widget should reject files with unsupported extensions.
    $file_field = $this->assertSession()
      ->elementExists('css', '.js-form-managed-file');
    $file_field->attachFileToField('File', __DIR__ . '/../../files/test.php');
    $file_field->pressButton('Upload');
    $this->assertSession()->pageTextContains('Only files with the following extensions are allowed');
    // The error message should not be double-escaped.
    $this->assertSession()->responseNotContains('&lt;em class="placeholder"&gt;');
  }

  /**
   * Tests validation in the embed code widget of the media browser.
   */
  public function testEmbedCodeValidation() {
    $account = $this->createUser([
      'access media_browser entity browser pages',
      'create media',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->buttonExists('Create embed')->press();

    // The widget should require an embed code.
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('You must enter a URL or embed code.');

    // The widget should also raise an error if the input cannot match any media
    // type.
    $this->assertSession()->fieldExists('input')->setValue('The quick brown fox gets eaten by hungry lions.');
    $this->assertSession()->buttonExists('Update')->press();
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->pageTextContains('Input did not match any media types:');

    // The widget should not react if the input is valid, but the user does not
    // have permission to create media of the matched type.
    $this->assertSession()->fieldExists('input')->setValue('https://twitter.com/webchick/status/824051274353999872');
    $this->assertSession()->buttonExists('Update')->press();
    $elements = $this->getSession()->getPage()->findAll('css', '#entity *');
    $this->assertEmpty($elements);
  }

}
