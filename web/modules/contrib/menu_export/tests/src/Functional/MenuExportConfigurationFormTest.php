<?php

namespace Drupal\Tests\menu_export\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * .
 * @group menu_export
 */
class MenuExportConfigurationFormTest extends BrowserTestBase {

  public static $modules = ['menu_export'];

  public function testFormLoad(){
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);
    $this->drupalGet("/admin/config/development/menu_export");
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet("/admin/config/development/menu_export/import");
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet("/admin/config/development/menu_export/export");
    $this->assertSession()->statusCodeEquals(403);

    $user = $this->drupalCreateUser(['export and import menu links']);
    $this->drupalLogin($user); 
    $this->drupalGet("/admin/config/development/menu_export"); 
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet("/admin/config/development/menu_export/import");
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet("/admin/config/development/menu_export/export");
    $this->assertSession()->statusCodeEquals(200);

  }
}
