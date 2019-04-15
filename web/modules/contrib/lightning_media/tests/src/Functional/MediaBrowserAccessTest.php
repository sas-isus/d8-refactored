<?php

namespace Drupal\Tests\lightning_media\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * @group lightning_media
 */
class MediaBrowserAccessTest extends BrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['lightning_media'];

  public function test() {
    $account = $this->drupalCreateUser([
      'access media_browser entity browser pages',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No widgets are available.');

    // Create a media type. There should still be no widgets available, since
    // the current user does not have permission to create media.
    $this->createMediaType('image');
    $this->getSession()->reload();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No widgets are available.');

    $this->drupalLogout();
    $account = $this->drupalCreateUser([
      'access media_browser entity browser pages',
      'create media',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('No widgets are available.');
    $this->assertSession()->buttonExists('Upload');
    $this->assertSession()->buttonExists('Create embed');
  }

}
