<?php

namespace Acquia\LightningExtension\Context;

use Behat\Behat\Hook\Scope\ScenarioScope;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Contains step definitions for interacting with Lightning's media browser.
 */
final class MediaBrowserContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * Indicates if the current scenario uses JavaScript.
   *
   * @var bool
   */
  private $isJS;

  /**
   * Performs pre-scenario tasks.
   *
   * @BeforeScenario
   */
  public function setUp(ScenarioScope $scope) {
    /** @var MinkContext $mink_context */
    $mink_context = $this->getContext(MinkContext::class);

    $path = $mink_context->getMinkParameter('files_path');
    if (empty($path)) {
      $mink_context->setMinkParameter('files_path', __DIR__ . '/../files');
    }

    // Check if the feature or scenario has the 'javascript' tag.
    $tags = array_merge(
      $scope->getScenario()->getTags(),
      $scope->getFeature()->getTags()
    );
    $this->isJS = in_array('javascript', $tags, TRUE);
  }

  /**
   * Opens the media browser, obviously.
   *
   * @param string $button
   *   (optional) The embed button ID.
   *
   * @When I open the media browser
   */
  public function open($button = 'media_browser') {
    $this->isJS ? $this->openJS($button) : $this->openNoJS();
  }

  /**
   * Opens the media browser when JavaScript is enabled.
   *
   * @param string $button
   *   (optional) The embed button ID.
   * @param string $browser
   *   (optional) The entity browser ID.
   */
  private function openJS($button = 'media_browser', $browser = 'ckeditor_media_browser') {
    $this
      ->getContext(CkEditorContext::class)
      ->execute('editdrupalentity', NULL, [
        'id' => $button,
      ]);

    $frame = $this->awaitElement("iframe[name='entity_browser_iframe_$browser']", 30)
      ->getAttribute('name');

    $this->getSession()->switchToIFrame($frame);
  }

  /**
   * Opens the media browser without JavaScript.
   *
   * @param string $browser_id
   *   (optional) The entity browser ID.
   */
  private function openNoJS($browser_id = 'media_browser') {
    $uuid = $this->assertSession()
      ->elementExists('named', ['button', 'Add media'])
      ->getAttribute('data-uuid');

    $this->visitPath("/entity-browser/modal/$browser_id?uuid=$uuid");
  }

  /**
   * Selects an item from the media browser.
   *
   * @param int $n
   *   The one-based index of the item to select.
   *
   * @When I select item :n in the media browser
   */
  public function selectN($n) {
    $this->getContext(EntityBrowserContext::class)->select($n, 'media_browser');
  }

  /**
   * Completes the media browser selection.
   *
   * @When I complete the media browser selection
   */
  public function completeSelection() {
    $assert = $this->assertSession();
    $session = $this->getSession();

    $frame = $session->evaluateScript('window.name') ?: $session->evaluateScript('window.active_iframe.name');
    assert(! empty($frame));

    $button = $assert->elementExists('named', ['button', 'Place'])->getXpath();

    // Switch out of the iFrame, because it will be destroyed as soon as we
    // press the button.
    $session->switchToIFrame();
    $js = <<<END
document.evaluate('$button', window.$frame.document, null).iterateNext().click();
END;
    $session->executeScript($js);

    $this->awaitElement('form.entity-embed-dialog');
    $assert->elementExists('named', ['button', 'Embed'])->press();
    $this->awaitAjax();
  }

  /**
   * Enters an embed code in the media browser.
   *
   * @param string $code
   *   The embed code.
   *
   * @When I enter embed code :code
   */
  public function embed($code) {
    // Activate the 'Create embed' tab. We cannot use the link text because it
    // may change between versions of Lightning (as in commit 48fa57e), but the
    // UUID won't.
    $this->assertSession()
      ->elementExists('css', 'nav.eb-tabs ul li a[data-button-id="edit-tab-selector-8b142f33-59d1-47b1-9e3a-4ae85d8376fa"]')
      ->click();

    $this->getContext(MinkContext::class)->fillField('input', $code);

    // The change event, which triggers AJAX, is fired automatically after 600
    // milliseconds.
    sleep(1);
    $this->awaitAjax();
    $this->awaitExpression('jQuery("#entity").children().length');
  }

  /**
   * Uploads a file in the media browser.
   *
   * @param string $file
   *   The path to the file, relative to the test files directory.
   *
   * @When I upload :file
   */
  public function upload($file) {
    $this->isJS ? $this->uploadJS($file) : $this->uploadNoJS($file);
  }

  /**
   * Uploads a file in the media browser using JavaScript.
   *
   * @param string $file
   *   The path to the file, relative to the test files directory.
   */
  private function uploadJS($file) {
    $this->assertSession()->elementExists('named', ['link', 'Upload'])->click();
    $this->getContext(MinkContext::class)->attachFileToField('File', $file);
    $this->awaitExpression('jQuery("#entity").children().length');
  }

  /**
   * Uploads a file in the media browser without using JavaScript.
   *
   * @param string $file
   *   The path to the file, relative to the test files directory.
   */
  private function uploadNoJS($file) {
    $assert = $this->assertSession();

    // Switch to the "Upload" tab of the media browser, which should be the
    // first button named "Upload" on the page.
    $assert->elementExists('named', ['button', 'Upload'])->press();

    $this->getContext(MinkContext::class)->attachFileToField('File', $file);
    $wrapper = $assert->elementExists('css', '.js-form-managed-file');
    $assert->elementExists('named', ['button', 'Upload'], $wrapper)->press();
  }

  /**
   * @param $title
   * @param $file
   *
   * @When I create media named :title by uploading :file
   */
  public function createFromUpload($title, $file) {
    /** @var MinkContext $mink_context */
    $mink_context = $this->getContext(MinkContext::class);
    $mink_context->assertAtPath('/entity-browser/modal/media_browser');

    $this->upload($file);

    // If the file is an image, assert that cropping is available.
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    if (in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
      /** @var \Acquia\LightningExtension\Context\ImageBrowserContext $context */
      $context = $this->getContext(ImageBrowserContext::class);
      $context->assertCrop();
    }

    $assert = $this->assertSession();
    $assert->fieldExists('Name')->setValue($title);
    $assert->elementExists('named', ['button', 'Place'])->press();
  }

  /**
   * Creates a media item in the media browser using an embed code.
   *
   * @param string $title
   *   The label of the created media item.
   * @param string $embed_code
   *   The embed code from which to create the media item.
   *
   * @When I create media named :title using the embed code :embed_code
   */
  public function createFromEmbedCode($title, $embed_code) {
    /** @var MinkContext $mink_context */
    $mink_context = $this->getContext(MinkContext::class);
    $mink_context->assertAtPath('/entity-browser/modal/media_browser');

    $assert = $this->assertSession();
    $assert->elementExists('named', ['button', 'Create embed'])->press();
    $assert->fieldExists('input')->setValue($embed_code);
    $assert->elementExists('named', ['button', 'Update'])->press();
    $assert->fieldExists('Name')->setValue($title);
    $assert->elementExists('named', ['button', 'Place'])->press();
  }

}
