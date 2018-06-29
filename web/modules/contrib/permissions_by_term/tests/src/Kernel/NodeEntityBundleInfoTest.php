<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\permissions_by_term\Service\NodeEntityBundleInfo;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class AccessStorageTest
 *
 * @package Drupal\Tests\permissions_by_term\Kernel
 * @group permissions_by_term
 */
class NodeEntityBundleInfoTest extends PBTKernelTestBase {

  /**
   * @var NodeEntityBundleInfo
   */
  private $nodeEntityBundleInfo;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->nodeEntityBundleInfo = $this->container->get('permissions_by_term.node_entity_bundle_info');
  }

  /**
   * @return void
   */
  public function testGetPermissionsByTids() {
    $testUser1 = User::create([
      'uid' => 5,
      'name' => 'testUser1',
      'mail' => 'foobar@example.com',
    ]);
    $testUser1->save();

    $role = Role::create([
      'id' => 'first-role',
      'label' => 'First Role'
    ]);
    $role->save();

    $testUser2 = User::create([
      'uid' => 6,
      'name' => 'testUser2',
      'mail' => 'foobar@example.com',
    ]);
    $testUser2->save();

    $role = Role::create([
      'id' => 'second-role',
      'label' => 'Second Role'
    ]);
    $role->save();

    $testUser3 = User::create([
      'uid' => 7,
      'name' => 'testUser3',
      'mail' => 'foobar@example.com',
    ]);
    $testUser3->save();

    $role = Role::create([
      'id' => 'third-role',
      'label' => 'Third Role'
    ]);
    $role->save();

    $firstTerm = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $firstTerm->save();

    $secondTerm = Term::create([
      'name' => 'term3',
      'vid' => 'test',
    ]);
    $secondTerm->save();

    $this->accessStorage->addTermPermissionsByUserIds([5, 6, 7], $firstTerm->id());
    $this->accessStorage->addTermPermissionsByRoleIds(['first-role', 'second-role', 'third-role'], $firstTerm->id());
    $this->accessStorage->addTermPermissionsByRoleIds(['second-role', 'third-role'], $secondTerm->id());

    $permissions = $this->nodeEntityBundleInfo->getPermissions();

    $expectedPermissions = [
      'roleLabels' => [1 => ['First Role', 'Second Role', 'Third Role'], 2 => ['Second Role', 'Third Role']],
      'userDisplayNames' => [1 => ['testUser1', 'testUser2', 'testUser3']]
    ];

    $this->assertArraySubset($expectedPermissions, $permissions);
  }

}