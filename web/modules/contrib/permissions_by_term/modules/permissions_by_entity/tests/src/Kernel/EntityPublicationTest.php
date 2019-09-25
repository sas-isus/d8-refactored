<?php

namespace Drupal\Tests\permissions_by_entity\Kernel;

use Drupal\Core\Access\AccessResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\pbt_entity_test\Entity\TestEntity;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class EntityPublicationTest.
 *
 * @package Drupal\Tests\permissions_by_entity\Kernel
 */
class EntityPublicationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'pbt_entity_test',
    'dynamic_page_cache',
    'taxonomy',
    'user',
    'system',
    'permissions_by_term',
    'permissions_by_entity',
  ];

  /**
   * Nodes we will test against.
   *
   * @var \Drupal\node\Entity\Node[]
   */
  private $nodes;

  /**
   * An anonymous user to run our tests as.
   *
   * @var \Drupal\user\Entity\User
   */
  private $anonymousUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('test_entity');
    $this->installEntitySchema('user');
    $this->installSchema('system', ['key_value_expire', 'sequences']);

    $this->nodes['node_unpublished'] = TestEntity::create(['langcode' => 'en']);
    $this->nodes['node_unpublished']->setUnpublished()->save();
    $this->nodes['node_published'] = TestEntity::create(['langcode' => 'en']);
    $this->nodes['node_published']->setPublished()->save();

    $anonymousRole = Role::create(['id' => 'anonymous_users']);
    $anonymousRole->grantPermission('access content');
    $anonymousRole->save();
    $this->anonymousUser = User::create([
      'id' => 0,
      'name' => 'anonymous',
      'email' => 'anonymous@example.com',
    ]);
    $this->anonymousUser->addRole($anonymousRole->id());
    $this->anonymousUser->save();
  }

  /**
   * Published nodes without restrictions should be visible to anonymous users.
   */
  public function testAnonymousCanViewPublishedNodesWithoutTermPermissions(): void {
    $this->assertTrue($this->nodes['node_published']->isPublished());
    $this->assertEquals(AccessResult::neutral(), permissions_by_entity_entity_access($this->nodes['node_published'], 'view', $this->anonymousUser));
    $this->assertNotEqual(AccessResult::forbidden(), $this->nodes['node_published']->access('view', $this->anonymousUser, TRUE));
  }

  /**
   * Unpublished nodes without restrictions should not be visible to anonymous
   * users.
   */
  public function testAnonymousCannotViewUnpublishedNodesWithoutTermPermissions(): void {
    $this->assertFalse($this->nodes['node_unpublished']->isPublished());
    $this->assertEquals(AccessResult::neutral(), permissions_by_entity_entity_access($this->nodes['node_unpublished'], 'view', $this->anonymousUser));
    $this->assertFalse($this->nodes['node_unpublished']->access('view', $this->anonymousUser));
  }

}
