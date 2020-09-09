<?php

namespace Drupal\Tests\menu_admin_per_menu\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the interaction of the node system with menu links.
 *
 * @group menu_admin_per_menu
 */
class MenuAdminPerMenuNodeFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'node',
    'menu_admin_per_menu_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to create nodes but not administer menu.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $contentOnlyUser;

  /**
   * A user with permission to create nodes and administer menu.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $contentAndMenuUser;

  /**
   * A user entity.
   *
   * This user has the 'administer menu_1 permissions'.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $menu1User;

  /**
   * A user entity.
   *
   * This user has the 'administer menu_2 permissions'.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $menu2User;

  /**
   * A user entity.
   *
   * This user has permission to alter menu items on menu_3 because of
   * hook_menu_admin_per_menu_get_permissions_alter that is provided in the
   * test module.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $menu3User;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(
      [
        'type' => 'page',
        'name' => 'Basic page',
        'display_submitted' => FALSE,
      ]
    );

    $this->drupalCreateContentType(
      [
        'type' => 'menu_test',
        'name' => 'Menu test',
        'display_submitted' => FALSE,
        'third_party_settings' => [
          'menu_ui' => [
            'available_menus' => [
              'menu_1',
              'menu_2',
              'menu_3',
            ],
          ],
        ],
      ]
    );

    $this->drupalPlaceBlock('system_menu_block:main');

    $this->contentOnlyUser = $this->drupalCreateUser(
      [
        'access content',
        'administer content types',
      ]
    );
    $this->contentAndMenuUser = $this->drupalCreateUser(
      [
        'access content',
        'administer content types',
        'administer main menu items',
      ]
    );
    $this->menu1User = $this->createUser([
      'access content',
      'administer content types',
      'administer menu_1 menu items',
    ], 'Menu 1 user');
    $this->menu2User = $this->createUser([
      'access content',
      'administer content types',
      'administer menu_2 menu items',
    ], 'Menu 2 user');
    // Access to menu_3 is added in menu_admin_per_menu_hook_test.
    $this->menu3User = $this->createUser([
      'access content',
      'administer content types',
    ], 'Menu 3 user');
  }

  /**
   * Test menu re-save by users without permission.
   *
   * Tests that a menu still exists and remains existing if a user without the
   * menu permissions re-saves a node.
   */
  public function testResaveMenuLinkWithoutAccess() {
    $menu_link_title = $this->randomString();

    // Save the node with the menu.
    $this->drupalLogin($this->contentAndMenuUser);
    $edit = [
      'title[0][value]' => $this->randomString(),
      'body[0][value]' => $this->randomString(),
      'menu[enabled]' => 1,
      'menu[title]' => $menu_link_title,
    ];
    $this->drupalPostForm('node/add/page', $edit, 'Save');

    // Ensure the menu is in place.
    $this->assertSession()->linkExists($menu_link_title);

    // Logout.
    $this->drupalLogout();

    // Save the node again as someone without permission.
    $this->drupalLogin($this->contentOnlyUser);
    $edit = [
      'title[0][value]' => $this->randomString(),
      'body[0][value]' => $this->randomString(),
    ];
    $this->drupalPostForm('node/add/page', $edit, 'Save');

    // Ensure the menu is still in place.
    $this->assertSession()->linkExists($menu_link_title);

    // Ensure anonymous users with "access content" permission can see this
    // menu.
    $this->drupalLogout();
    $this->drupalGet('');
    $this->assertSession()->linkExists($menu_link_title);
  }

  /**
   * Test menu_admin_per_menu permissions on node forms.
   */
  public function testMenuPermissionsOnNodeForm() {
    $assert_session = $this->assertSession();

    // Content only user does not have access to the menu settings.
    $this->drupalLogin($this->contentOnlyUser);

    $this->drupalGet('node/add/menu_test');
    $assert_session->fieldNotExists('menu[enabled]');
    $assert_session->fieldNotExists('menu[menu_parent]');

    // User with 'administer menu_1 menu items' can only add menu items to
    // menu_1.
    $this->drupalLogin($this->menu1User);

    $this->drupalGet('node/add/menu_test');
    $assert_session->fieldExists('menu[enabled]');
    $assert_session->optionExists('menu[menu_parent]', 'menu_1:');
    $assert_session->optionExists('menu[menu_parent]', 'menu_1:menu_1.link');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_2:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_2:menu_2.link');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_3:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_3:menu_3.link');

    $this->drupalLogin($this->menu2User);

    $this->drupalGet('node/add/menu_test');
    $assert_session->fieldExists('menu[enabled]');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_1:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_1:menu_1.link');
    $assert_session->optionExists('menu[menu_parent]', 'menu_2:');
    $assert_session->optionExists('menu[menu_parent]', 'menu_2:menu_2.link');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_3:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_3:menu_3.link');

    $this->drupalLogin($this->menu3User);

    $this->drupalGet('node/add/menu_test');
    $assert_session->fieldExists('menu[enabled]');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_1:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_1:menu_1.link');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_2:');
    $assert_session->optionNotExists('menu[menu_parent]', 'menu_2:menu_2.link');
    $assert_session->optionExists('menu[menu_parent]', 'menu_3:');
    $assert_session->optionExists('menu[menu_parent]', 'menu_3:menu_3.link');
  }

}
