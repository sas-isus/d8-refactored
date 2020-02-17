<?php

namespace Drupal\Tests\extlink\FunctionalJavascript;

/**
 * Testing of the External Links administration interface and functionality.
 *
 * @group Extlink Admin Tests
 */
class ExtlinkAdminTest extends ExtlinkTestBase {

  /**
   * Test access to the admin pages.
   */
  public function testAdminAccess() {
    $this->drupalLogin($this->normalUser);
    $this->drupalGet(self::EXTLINK_ADMIN_PATH);
    $this->assertSession()->pageTextContains(t('Access denied'), 'Normal users should not be able to access the External Links admin pages', 'External Links');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet(self::EXTLINK_ADMIN_PATH);
    $this->assertSession()->pageTextNotContains(t('Access denied'), 'Admin users should be able to access the External Links admin pages', 'External Links');
  }

}
