<?php

namespace Drupal\gathercontent_test\EventSubscriber;

use Drupal\gathercontent\Event\GatherContentEvents;
use Drupal\gathercontent\Event\PostNodeSaveEvent;
use Drupal\gathercontent\Event\PreNodeSaveEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class for testing events.
 */
class MockGcEventSubscriber implements EventSubscriberInterface {

  public static $preNodeSaveCalled = 0;
  public static $postNodeSaveCalled = 0;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      GatherContentEvents::PRE_NODE_SAVE => 'preNodeSave',
      GatherContentEvents::POST_NODE_SAVE => 'postNodeSave',
    ];
  }

  /**
   * Pre Node Save event listener.
   */
  public function preNodeSave(PreNodeSaveEvent $event) {
    $node = $event->getNode();
    TestCase::assertTrue(
      $node->isNew(),
      'The event was recieved after the entity got saved.'
    );
    static::$preNodeSaveCalled++;
  }

  /**
   * Post Node Save event listener.
   */
  public function postNodeSave(PostNodeSaveEvent $event) {
    $node = $event->getNode();
    TestCase::assertFalse(
      $node->isNew(),
      'The event was recieved before the entity got saved.'
    );
    static::$postNodeSaveCalled++;
  }

}
