<?php

namespace Drupal\Tests\lightning_media\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group lightning_media
 */
class CKEditorMediaBrowserTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image_widget_crop',
    'lightning_media_document',
    'lightning_media_image',
    'lightning_media_twitter',
    'node',
  ];

  /**
   * The content type created during the test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  private $nodeType;

  /**
   * The ID of the current user.
   *
   * @var int
   */
  private $uid = 0;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->addMedia([
      'bundle' => 'tweet',
      'name' => 'Code Wisdom 1',
      'embed_code' => 'https://twitter.com/CodeWisdom/status/707945860936691714',
    ]);
    $this->addMedia([
      'bundle' => 'tweet',
      'embed_code' => 'https://twitter.com/CodeWisdom/status/826500049760821248',
    ]);
    $this->addMedia([
      'bundle' => 'tweet',
      'embed_code' => 'https://twitter.com/CodeWisdom/status/826460810121773057',
    ]);

    $this->nodeType = $this->createContentType();

    $account = $this->createUser([
      'access media overview',
      'create ' . $this->nodeType->id() . ' content',
      'edit own ' . $this->nodeType->id() . ' content',
      'access ckeditor_media_browser entity browser pages',
      'access media_browser entity browser pages',
      'use text format rich_text',
    ]);
    $this->drupalLogin($account);

    $this->uid = $account->id();

    $GLOBALS['install_state'] = [];
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = entity_load('view', 'media');
    lightning_media_view_insert($view);
    unset($GLOBALS['install_state']);

    module_load_install('lightning_media_image');
    lightning_media_image_install();
  }

  /**
   * Tests exposed filters in the media browser.
   */
  public function testExposedFilters() {
    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $this->open(TRUE);

    // All items should be visible.
    $this->assertCount(3, $this->getItems());

    // Try filtering by media type.
    $this->assertSession()->fieldExists('Type')->selectOption('Image');
    $this->applyFilters();
    $this->assertEmpty($this->getItems());

    // Clear the type filter.
    $this->assertSession()->fieldExists('Type')->selectOption('- Any -');
    $this->applyFilters();
    $this->assertCount(3, $this->getItems());

    // Try filtering by keywords.
    $this->assertSession()->fieldExists('Keywords')->setValue('Code Wisdom 1');
    $this->applyFilters();
    $this->assertCount(1, $this->getItems());

    // Clear the keyword filter.
    $this->assertSession()->fieldExists('Keywords')->setValue('');
    $this->applyFilters();
    $this->assertCount(3, $this->getItems());
  }

  /**
   * Tests that cardinality is never enforced in the media browser.
   */
  public function testUnlimitedCardinality() {
    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $this->open(TRUE);

    $items = $this->getItems();
    $this->assertGreaterThanOrEqual(3, count($items));
    $items[0]->click();
    $this->assertTrue($items[0]->hasCheckedField('Select this item'));
    $items[1]->click();
    $this->assertTrue($items[1]->hasCheckedField('Select this item'));

    // Only one item can be selected at any time, but nothing is ever disabled.
    $this->assertSession()->elementsCount('css', '[data-selectable].selected', 1);
    $this->assertSession()->elementNotExists('css', '[data-selectable].disabled');
  }

  /**
   * Tests that the entity embed dialog opens when editing a pre-existing embed.
   */
  public function testEditEmbed() {
    $node = $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Blorf',
      'uid' => $this->uid,
      'body' => [
        'value' => '',
        'format' => 'rich_text',
      ],
    ]);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->open(TRUE);

    $items = $this->getItems();
    $this->assertGreaterThanOrEqual(3, count($items));
    $items[0]->click();
    $this->assertTrue($items[0]->hasCheckedField('Select this item'));
    $this->assertSession()->buttonExists('Place')->press();
    $this->getSession()->switchToIFrame(NULL);
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert that the Entity Embed dialog box is present, but don't click the
    // Embed button *in* the form, because it is suppressed by Drupal's dialog
    // system.
    $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog');
    $this->assertSession()->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Embed');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()->buttonExists('Save')->press();
    $this->drupalGet($node->toUrl('edit-form'));
    $this->open();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->elementExists('css', 'form.entity-embed-dialog');
  }

  /**
   * Tests that the image embed plugin is used to embed an image.
   *
   * @depends testExposedFilters
   */
  public function testImageEmbed() {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $file_storage */
    $file_storage = $this->container->get('entity_type.manager')->getStorage('file');
    $uri = uniqid('public://') . '.png';
    $uri = $this->getRandomGenerator()->image($uri, '640x480', '800x600');
    $this->assertFileExists($uri);
    $image = $file_storage->create([
      'uri' => $uri,
    ]);
    $this->assertSame(SAVED_NEW, $file_storage->save($image));

    $media = $this->addMedia([
      'bundle' => 'image',
      'name' => 'Foobar',
      'image' => $image->id(),
    ]);
    $media->image->alt = 'I am the greetest';
    $this->assertSame(SAVED_UPDATED, $media->save());

    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $this->open(TRUE);

    $this->assertSession()->fieldExists('Type')->selectOption('Image');
    $this->applyFilters();

    $items = $this->getItems();
    $this->assertGreaterThanOrEqual(1, count($items));
    $items[0]->click();
    $this->assertSession()->buttonExists('Place')->press();
    $this->getSession()->switchToIFrame(NULL);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $embed_dialog = $this->assertSession()->elementExists('css', 'form.entity-embed-dialog');
    $this->assertSession()->optionExists('Image style', 'Cropped: Freeform', $embed_dialog);
    $this->assertSession()->fieldValueEquals('Alternate text', 'I am the greetest', $embed_dialog);
    $this->assertSession()->fieldValueEquals('attributes[title]', 'Foobar', $embed_dialog);
  }

  /**
   * Tests that the image embed plugin is not used to embed a document.
   *
   * @depends testExposedFilters
   */
  public function testDocumentEmbed() {
    $session = $this->getSession();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $file_storage */
    $file_storage = $this->container->get('entity_type.manager')->getStorage('file');
    $uri = uniqid('public://') . '.txt';
    file_put_contents($uri, $this->getRandomGenerator()->paragraphs());
    $file = $file_storage->create([
      'uri' => $uri,
    ]);
    $file_storage->save($file);

    $this->addMedia([
      'bundle' => 'document',
      'field_document' => $file->id(),
    ]);

    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $this->open(TRUE);

    $this->assertSession()->fieldExists('Type')->selectOption('Document');
    $this->applyFilters();

    $items = $this->getItems();
    $this->assertGreaterThanOrEqual(1, count($items));
    $items[0]->click();
    $this->assertSession()->buttonExists('Place')->press();
    $session->switchToIFrame(NULL);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $embed_dialog = $this->assertSession()->elementExists('css', 'form.entity-embed-dialog');
    $this->assertSession()->fieldNotExists('Image style', $embed_dialog);
    $this->assertSession()->fieldNotExists('Alternative text', $embed_dialog);
    $this->assertSession()->fieldNotExists('attributes[title]', $embed_dialog);
  }

  /**
   * Adds a media item to the library and marks it for deletion in tearDown().
   *
   * @param array $values
   *   The values with which to create the media item.
   *
   * @return \Drupal\media\MediaInterface
   *   The saved media item.
   */
  private function addMedia(array $values) {
    $values['field_media_in_library'] = TRUE;
    $values['status'] = TRUE;

    $media = Media::create($values);
    $this->assertSame(SAVED_NEW, $media->save());

    return $media;
  }

  /**
   * Returns all selectable items in the media browser.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The selectable items.
   */
  private function getItems() {
    return $this->getSession()
      ->getPage()
      ->findAll('css', '[data-selectable]');
  }

  /**
   * Applies exposed Views filters.
   */
  private function applyFilters() {
    $this->assertSession()->elementExists('css', '.views-exposed-form .form-actions input[type = "submit"]')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    sleep(2);
  }

  /**
   * Opens the CKeditor media browser.
   *
   * @param bool $switch
   *   (optional) If TRUE, switch into the media browser iFrame. Defaults to
   *   FALSE.
   */
  private function open($switch = FALSE) {
    // Assert that at least one CKeditor instance is initialized.
    $session = $this->getSession();
    $status = $session->wait(10000, 'Object.keys( CKEDITOR.instances ).length > 0');
    $this->assertTrue($status);

    // Assert that we have a valid list of CKeditor instance IDs.
    $editors = $session->evaluateScript('Object.keys( CKEDITOR.instances )');
    $this->assertInternalType('array', $editors);
    /** @var string[] $editors */
    $editors = array_filter($editors);
    $this->assertNotEmpty($editors);

    // Assert that the editor is ready.
    $editor = reset($editors);
    $status = $session->wait(10000, "CKEDITOR.instances['$editor'].status === 'ready'");
    $this->assertTrue($status);

    $status = $session->evaluateScript("CKEDITOR.instances['$editor'].execCommand('editdrupalentity', { id: 'media_browser' });");
    $this->assertNotEmpty($status);
    sleep(3);

    if ($switch) {
      $session->switchToIFrame('entity_browser_iframe_ckeditor_media_browser');
    }
  }

}
