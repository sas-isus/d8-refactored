<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\node\Entity\Node;
use Drupal\permissions_by_term\Service\AccessStorage;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;

/**
 * Class AccessCheckTest
 *
 * @package Drupal\Tests\permissions_by_term\Kernel
 * @group permissions_by_term
 */
class AccessCheckTest extends PBTKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * @return void
   */
  public function testDisabledSingleTermRestriction() {
    $database = $this->container->get('database');
    $database->truncate('node_access')->execute();
    $this->createRelationOneGrantedTerm();
    $this->createRelationAllGrantedTerms();

    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', FALSE)->save();
    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($this->getNidOneGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(2, $permittedNids);
  }

  /**
   * @return void
   */
  public function testNoGrantedTermRestriction() {
    $database = $this->container->get('database');
    $database->truncate('node_access')->execute();
    $this->createRelationNoGrantedTerm();

    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', FALSE)->save();
    $this->assertFalse($this->accessCheck->canUserAccessByNodeId($this->getNidNoGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(0, $permittedNids);
  }

  /**
   * @return void
   */
  public function testNoTermRestriction() {
    $database = $this->container->get('database');
    $database->truncate('node_access')->execute();
    $this->createRelationWithoutRestriction();

    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', FALSE)->save();
    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($this->getNidNoRestriction()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(0, $permittedNids);
  }

  /**
   * @return void
   */
  public function testSingleTermRestrictionWithRestrictedTerms() {
    $database = $this->container->get('database');
    $database->truncate('node_access')->execute();
    $this->createRelationOneGrantedTerm();
    $this->createRelationAllGrantedTerms();

    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', TRUE)->save();
    $this->assertFalse($this->accessCheck->canUserAccessByNodeId($this->getNidOneGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(1, $permittedNids);
  }

  /**
   * @return void
   */
  public function testSingleTermRestrictionWithNoRestrictedTerms() {
    $database = $this->container->get('database');
    $database->truncate('node_access')->execute();
    $this->createRelationWithoutRestriction();

    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', TRUE)->save();
    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($this->getNidOneGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(0, $permittedNids);
  }

  public function testCheckAccessAsGuest() {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();

    $this->accessCheck->isAccessAllowedByDatabase($term->id(), 0);
  }

  public function testBypassNodeAccess() {
    Vocabulary::create([
      'name'     => 'Test Multilingual',
      'vid'      => 'test_multilingual',
      'langcode' => 'de',
    ])->save();

    $term = Term::create([
      'name'     => 'term1',
      'vid'      => 'test',
      'langcode' => 'de',
    ]);
    $term->save();

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $term->id()
        ],
      ]
    ]);
    $node->save();

    $this->accessStorage->addTermPermissionsByUserIds([99], $term->id(), 'de');
    $this->assertFalse($this->accessCheck->canUserAccessByNodeId($node->id(), \Drupal::currentUser()->id(), 'de'));

    $editorRole = Role::create(['id' => 'editor']);
    $editorRole->grantPermission('bypass node access');
    $editorRole->save();

    $user = User::load(\Drupal::currentUser()->id());

    $user->addRole('editor');
    $user->save();

    $accountSwitcher = \Drupal::service('account_switcher');
    $accountSwitcher->switchTo($user);

    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($node->id(), \Drupal::currentUser()->id(), 'de'));
  }

}
