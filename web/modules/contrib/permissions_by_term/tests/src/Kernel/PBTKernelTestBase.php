<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\permissions_by_term\Service\AccessCheck;
use Drupal\permissions_by_term\Service\AccessStorage;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class PBTKernelTestBase
 *
 * @package Drupal\Tests\permissions_by_term\Kernel
 */
abstract class PBTKernelTestBase extends KernelTestBase {

  /**
   * @var int
   */
  protected $nidOneGrantedTerm;

  /**
   * @var int
   */
  protected $nidNoGrantedTerm;

  /**
   * @var int
   */
  protected $nidAllGrantedTerms;

  /**
   * @var int
   */
  protected $nidNoRestriction;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['taxonomy', 'node', 'user', 'text', 'field', 'system', 'permissions_by_term', 'language'];

  /**
   * @var AccessStorage
   */
  protected $accessStorage;

  /**
   * @var AccessCheck
   */
  protected $accessCheck;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', ['key_value_expire', 'sequences']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['permissions_by_term', 'language']);
    $this->installSchema('node', 'node_access');
    $this->installSchema('permissions_by_term', 'permissions_by_term_user');
    $this->installSchema('permissions_by_term', 'permissions_by_term_role');
    $this->accessStorage = $this->container->get('permissions_by_term.access_storage');
    $this->accessCheck = $this->container->get('permissions_by_term.access_check');
    \Drupal::configFactory()->getEditable('taxonomy.settings')->set('maintain_index_table', TRUE)->save();
    $this->createTestVocabularies();
    $this->createPageNodeType();
    $this->createCurrentUser();
    $this->createAdminUser();
  }

  protected function createTestVocabularies() {
    Vocabulary::create([
      'name' => 'test',
      'vid' => 'test',
    ])->save();

    Vocabulary::create([
      'name' => 'test2',
      'vid' => 'test2',
    ])->save();
  }

  protected function createPageNodeType() {
    NodeType::create([
      'type' => 'page',
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_tags',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();

    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_tags2',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_tags',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_tags2',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();
  }

  protected function createCurrentUser(): void {
    $testUser = User::create([
      'uid' => 2,
      'name' => 'foobar',
      'mail' => 'foobar@example.com',
    ]);

    $testUser->save();
    \Drupal::service('current_user')->setAccount($testUser);
  }

  protected function createAdminUser() {
    if (($role = Role::load('administrator')) === null) {
      $role = [
        'id' => 'administrator',
        'label' => 'administrator',
        'permissions' => [
          'access comments',
          'administer comments',
          'post comments',
          'post comments without approval',
          'access content',
          'administer content types',
          'administer nodes',
          'bypass node access',
        ]
      ];
      try {
        Role::create($role)->save();
      } catch (EntityStorageException $exception) {
        exit($exception->getMessage());
      }
    }


    $adminUser = User::create([
      'uid' => 1,
      'name' => 'admin',
      'roles' => ['administrator']
    ]);
    $adminUser->save();
  }

  protected function createRelationOneGrantedTerm(): void {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $term = Term::create([
      'name' => 'term3',
      'vid' => 'test2',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([99], $term->id());

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
        [
          'target_id' => $tids[1]
        ],
      ],
      'field_tags2' => [
        [
          'target_id' => $tids[2]
        ]
      ]
    ]);
    $node->save();
    $this->setNidOneGrantedTerm($node->id());
  }

  protected function createRelationNoGrantedTerm(): void {
    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([1], $term->id());

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
      ]
    ]);
    $node->save();
    $this->setNidNoGrantedTerm($node->id());
  }

  protected function createRelationAllGrantedTerms() {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
        [
          'target_id' => $tids[1]
        ],
      ]
    ]);
    $node->save();
    $this->setNidAllGrantedTerms($node->id());
  }

  protected function createRelationWithoutRestriction() {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
        [
          'target_id' => $tids[1]
        ],
      ]
    ]);
    $node->save();
    $this->setNidNoRestriction($node->id());
  }


  protected function getTaxonomyIndex() {
    return \Drupal::database()->select('taxonomy_index', 'ti')
      ->fields('ti', ['tid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * @return int
   */
  protected function getNidOneGrantedTerm() {
    return $this->nidOneGrantedTerm;
  }

  /**
   * @param int $nidOneGrantedTerm
   */
  protected function setNidOneGrantedTerm($nidOneGrantedTerm) {
    $this->nidOneGrantedTerm = $nidOneGrantedTerm;
  }

  /**
   * @return int
   */
  protected function getNidAllGrantedTerms() {
    return $this->nidAllGrantedTerms;
  }

  /**
   * @param int $nidAllGrantedTerms
   */
  protected function setNidAllGrantedTerms($nidAllGrantedTerms) {
    $this->nidAllGrantedTerms = $nidAllGrantedTerms;
  }

  /**
   * @return int
   */
  protected function getNidNoGrantedTerm() {
    return $this->nidNoGrantedTerm;
  }

  /**
   * @param int $nidNoGrantedTerm
   */
  protected function setNidNoGrantedTerm($nidNoGrantedTerm) {
    $this->nidNoGrantedTerm = $nidNoGrantedTerm;
  }

  /**
   * @return int
   */
  protected function getNidNoRestriction() {
    return $this->nidNoRestriction;
  }

  /**
   * @param int $nidNoRestiction
   */
  protected function setNidNoRestriction($nidNoRestiction) {
    $this->nidNoRestriction = $nidNoRestiction;
  }

}
