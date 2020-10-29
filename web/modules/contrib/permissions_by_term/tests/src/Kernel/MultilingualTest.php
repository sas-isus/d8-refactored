<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\permissions_by_term\Service\NodeEntityBundleInfo;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\taxonomy\Traits\TaxonomyTestTrait;
use Drupal\user\Entity\User;


/**
 * @group permissions_by_term
 */
class MultilingualTest extends PBTKernelTestBase {

  use TaxonomyTestTrait;

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

  public function testCanAccess() {
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

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id(), 'de');
    $this->assertTrue($this->accessCheck->canUserAccessByNode(Node::load($node->id())));
  }

  public function testCanNotAccess() {
    [$termDe, $nodeDe, $term, $node, $user] = $this->setupEntities();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id(), 'en');
    $this->accessStorage->addTermPermissionsByUserIds([$user->id()], $termDe->id(), 'de');

    /**
     * @var User $user
     */
    $user = user_load_by_name('some_username123');

    $this->assertTrue($this->accessCheck->canUserAccessByNode($node, \Drupal::service('current_user')->id()));
    $this->assertFalse($this->accessCheck->canUserAccessByNode($nodeDe, \Drupal::service('current_user')->id(), 'de'));
    $this->assertTrue($this->accessCheck->canUserAccessByNode($nodeDe, $user->id(), 'de'));
  }

  private function setupEntities() {
    $i = 0;
    foreach (['de'] as $langcode) {
      $language = ConfigurableLanguage::createFromLangcode($langcode);
      $language->set('weight', $i--);
      $language->save();
    }

    Vocabulary::create([
      'name'     => 'Test English',
      'vid'      => 'test_multilingual_en',
      'langcode' => 'en',
    ])->save();

    Vocabulary::create([
      'name'     => 'Test German',
      'vid'      => 'test_multilingual_de',
      'langcode' => 'de',
    ])->save();

    $term = Term::create([
      'name'     => 'term1',
      'vid'      => 'test_multilingual_en',
      'langcode' => 'en',
    ]);
    $term->save();

    $node = Node::create([
      'type'       => 'page',
      'title'      => 'test_title',
      'field_tags' => [
        [
          'target_id' => $term->id()
        ],
      ]
    ]);
    $node->save();

    $termDe = Term::create([
      'name'     => 'term DE',
      'vid'      => 'test_multilingual_de',
      'langcode' => 'de',
    ]);
    $termDe->save();

    $nodeDe = $node->addTranslation('de');
    $nodeDe->title = 'Node Übersetzung';
    $nodeDe->field_tags = [
      [
        'target_id' => $termDe->id()
      ],
    ];
    $nodeDe->save();

    $user = User::create([
      'name'   => 'some_username123',
      'status' => 1,
    ]);
    $user->save();

    return [$termDe, $nodeDe, $term, $node, $user];
  }

}
