<?php

namespace Drupal\Tests\permissions_by_entity\Kernel;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\KernelTests\KernelTestBase;
use Drupal\pbt_entity_test\Entity\TestEntity;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class EntityAccessCheckTest
 *
 * @group permissions_by_term
 */
class EntityAccessCheckTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'pbt_entity_test',
    'permissions_by_entity',
    'taxonomy',
    'user',
    'field',
    'text',
    'language',
    'system',
    'dynamic_page_cache',
    'permissions_by_term',
  ];

  /**
   * The access storage.
   *
   * @var \Drupal\permissions_by_term\Service\AccessStorage
   */
  private $accessStorage;

  /**
   * The access checker.
   *
   * @var \Drupal\permissions_by_entity\Service\AccessChecker
   */
  private $accessChecker;

  /**
   * The terms and users.
   *
   * @var array
   */
  private $terms;

  /**
   * The nodes.
   *
   * @var \Drupal\node\Entity\Node[]
   */
  private $nodes;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', ['key_value_expire', 'sequences']);
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('test_entity');
    $this->installConfig(['language', 'permissions_by_term']);
    $this->installSchema('permissions_by_term', 'permissions_by_term_user');
    $this->installSchema('permissions_by_term', 'permissions_by_term_role');

    $this->accessStorage = $this->container->get('permissions_by_term.access_storage');
    $this->accessChecker = $this->container->get('permissions_by_entity.access_checker');

    $this->setupUsersAndTerms();
    $this->createNodesForEachUser();
  }

  /**
   * Tests basic access control.
   */
  public function testBaseAccessControl() {
    self::assertTrue($this->accessChecker->isAccessAllowed($this->nodes['test_entity_term_a'], $this->terms['term_user_a']['user']->id()));
    self::assertTrue($this->accessChecker->isAccessAllowed($this->nodes['test_entity_term_b'], $this->terms['term_user_b']['user']->id()));

    self::assertFalse($this->accessChecker->isAccessAllowed($this->nodes['test_entity_term_b'], $this->terms['term_user_a']['user']->id()));
    self::assertFalse($this->accessChecker->isAccessAllowed($this->nodes['test_entity_term_a'], $this->terms['term_user_b']['user']->id()));
  }

  /**
   * Tests even listener based access control.
   */
  public function testAnonymousAccessDeniedUsingKernel() {
    $dispatcher = $this->getPopulatedDispatcher();

    $this->setExpectedException(AccessDeniedHttpException::class);
    $dispatcher->dispatch(KernelEvents::REQUEST, $this->getRequestResponseEvent());
  }

  /**
   * Tests even listener based access control.
   */
  public function testAuthenticatedAccessUsingKernel() {
    $dispatcher = $this->getPopulatedDispatcher();

    $this->container->get('current_user')->setAccount($this->terms['term_user_a']['user']);
    $dispatcher->dispatch(KernelEvents::REQUEST, $this->getRequestResponseEvent());
  }

  /**
   * Tests even listener based access control.
   */
  public function testAuthenticatedDeniedOnCachedAccessUsingKernel() {
    $dispatcher = $this->getPopulatedDispatcher();

    // Execute first request for allowed user.
    $this->container->get('current_user')->setAccount($this->terms['term_user_a']['user']);
    $dispatcher->dispatch(KernelEvents::REQUEST, $this->getRequestResponseEvent());
    $dispatcher->dispatch(KernelEvents::RESPONSE, $this->getCachableResponseEvent());

    // Reset the cache to emulate a new request.
    $this->container->get('permissions_by_entity.checked_entity_cache')->clear();

    // Execute second request for disallowed user.
    $this->container->get('current_user')->setAccount($this->terms['term_user_b']['user']);
    $this->setExpectedException(AccessDeniedHttpException::class);
    $dispatcher->dispatch(KernelEvents::REQUEST, $this->getRequestResponseEvent());
  }

  /**
   * Creates nods for each user.
   *
   * @see setupUsersAndTerms()
   */
  private function createNodesForEachUser() {
    $nodes['test_entity_term_a'] = TestEntity::create([
      'terms' => [$this->terms['term_user_a']['term']->id()],
      'langcode' => 'en',
    ]);
    $nodes['test_entity_term_a']->save();

    $nodes['test_entity_term_b'] = TestEntity::create([
      'terms' => [$this->terms['term_user_b']['term']->id()],
      'langcode' => 'en',
    ]);
    $nodes['test_entity_term_b']->save();

    $this->nodes = $nodes;
  }

  /**
   * Configures users and connected terms.
   */
  private function setupUsersAndTerms() {
    Vocabulary::create([
      'name' => 'test',
      'vid' => 'test',
    ])->save();

    # First user.
    $term_array['term_user_a']['user'] = User::create([
      'name' => 'term_user_a',
      'mail' => 'term_user_a@example.com',
    ]);
    $term_array['term_user_a']['user']->save();

    $term_array['term_user_a']['term'] = Term::create([
      'name' => 'term_user_a',
      'vid' => 'test',
    ]);
    $term_array['term_user_a']['term']->save();

    $this->accessStorage->addTermPermissionsByUserIds([$term_array['term_user_a']['user']->id()], $term_array['term_user_a']['term']->id());

    # Second user.
    $term_array['term_user_b']['user'] = User::create([
      'name' => 'term_user_b',
      'mail' => 'term_user_b@example.com',
    ]);
    $term_array['term_user_b']['user']->save();

    $term_array['term_user_b']['term'] = Term::create([
      'name' => 'term_user_b',
      'vid' => 'test',
    ]);
    $term_array['term_user_b']['term']->save();

    $this->accessStorage->addTermPermissionsByUserIds([$term_array['term_user_b']['user']->id()], $term_array['term_user_b']['term']->id());

    $this->terms = $term_array;
  }

  /**
   * Gets a populated dispatcher.
   *
   * @return \Symfony\Component\EventDispatcher\EventDispatcher
   */
  private function getPopulatedDispatcher() {
    $dispatcher = new EventDispatcher();
    $cache_subscriber = $this->container->get('mocked_dynamic_page_cache_subscriber');
    $access_subscriber = $this->container->get('permissions_by_entity.kernel_event_subscriber');
    $dispatcher->addSubscriber($cache_subscriber);
    $dispatcher->addSubscriber($access_subscriber);

    return $dispatcher;
  }

  /**
   * Gets a request response event for term A.
   *
   * @return \Symfony\Component\HttpKernel\Event\GetResponseEvent
   */
  private function getRequestResponseEvent() {
    $request = new Request();
    $request->attributes->set('_entity', $this->nodes['test_entity_term_a']);

    $kernel = $this->getMock(HttpKernelInterface::class);
    return new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
  }

  /**
   * Gets a cachable filter response for term a.
   *
   * @return \Symfony\Component\HttpKernel\Event\FilterResponseEvent
   */
  private function getCachableResponseEvent() {
    $response = new CacheableResponse();
    $kernel = $this->getMock(HttpKernelInterface::class);
    $request = new Request();
    $request->attributes->set('_entity', $this->nodes['test_entity_term_a']);

    return new FilterResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
  }
}
