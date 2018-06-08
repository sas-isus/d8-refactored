<?php

namespace Drupal\permissions_by_entity\EventSubscriber;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\permissions_by_entity\Service\AccessCheckerInterface;
use Drupal\permissions_by_entity\Service\CheckedEntityCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PermissionsByEntityKernelEventSubscriber.
 *
 * @package Drupal\permissions_by_entity\EventSubscriber
 */
class PermissionsByEntityKernelEventSubscriber implements EventSubscriberInterface {

  /**
   * The access checker.
   *
   * @var \Drupal\permissions_by_entity\Service\AccessCheckerInterface
   */
  private $accessChecker;

  /**
   * The core string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translation;

  /**
   * The cache for checked entities.
   *
   * @var \Drupal\permissions_by_entity\Service\CheckedEntityCache
   */
  private $checkedEntityCache;

  /**
   * PermissionsByEntityKernelEventSubscriber constructor.
   *
   * @param \Drupal\permissions_by_entity\Service\AccessCheckerInterface $access_checker
   *   The service to check if the current user is allowed to access an entity.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The core string translator.
   * @param \Drupal\permissions_by_entity\Service\CheckedEntityCache $checked_entity_cache
   *   The cache for checked entities.
   */
  public function __construct(
    AccessCheckerInterface $access_checker,
    TranslationInterface $translation,
    CheckedEntityCache $checked_entity_cache
  ) {
    $this->accessChecker = $access_checker;
    $this->translation = $translation;
    $this->checkedEntityCache = $checked_entity_cache;
  }

  /**
   * {@inheritdoc}
   *
   * @see DynamicPageCacheSubscriber
   *
   * This is required to run before the DynamicPageCacheSubscriber as otherwise
   * the response would be cached which can lead to false access.
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 28],
    ];
  }

  /**
   * Callback method for the KernelEvents::REQUEST event.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event instance.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    // Get the current request from the event.
    $request = $event->getRequest();

    // Get the entity.
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entity = NULL;
    if ($request->attributes->has('node')) {
      $entity = $request->attributes->get('node');
    }
    elseif ($request->attributes->has('_entity')) {
      $entity = $request->attributes->get('_entity');
    }

    // If there is no entity abort here.
    if (!$entity instanceof FieldableEntityInterface) {
      return;
    }

    // If we already checked this entity, we do nothing.
    if ($this->checkedEntityCache->isChecked($entity)) {
      return;
    }

    // Add this entity to the cache.
    $this->checkedEntityCache->add($entity);

    // Check if the current user is allowed to access this entity.
    if (
      $entity && $entity instanceof FieldableEntityInterface &&
      !$this->accessChecker->isAccessAllowed($entity)
    ) {

      // If the current user is not allowed to access this entity,
      // we throw an AccessDeniedHttpException.
      throw new AccessDeniedHttpException(
        $this->translation->translate(
          'You are not allowed to view content of this entity type.'
        )
      );
    }
  }

}
