<?php

namespace Drupal\permissions_by_term\Listener;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\permissions_by_term\Event\PermissionsByTermDeniedEvent;
use Drupal\permissions_by_term\Service\AccessCheck;
use Drupal\permissions_by_term\Service\AccessStorage;
use Drupal\permissions_by_term\Service\TermHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class KernelEventListener.
 *
 * @package Drupal\permissions_by_term
 */
class KernelEventListener implements EventSubscriberInterface
{

  /**
   * @var AccessCheck
   */
  private $accessCheckService;

  /**
   * @var TermHandler
   */
  private $term;

  /**
   * @var ContainerAwareEventDispatcher
   */
  private $eventDispatcher;

  /**
   * @var AccessStorage
   */
  private $accessStorageService;

  /**
   * Instantiating of objects on class construction.
   */
  public function __construct()
  {
    $this->accessCheckService = \Drupal::service('permissions_by_term.access_check');
    $this->accessStorageService = \Drupal::service('permissions_by_term.access_storage');
    $this->term = \Drupal::service('permissions_by_term.term_handler');
    $this->eventDispatcher = \Drupal::service('event_dispatcher');
  }

  /**
   * Access restriction on kernel request.
   */
  public function onKernelRequest(GetResponseEvent $event)
  {
    // Restricts access to nodes (views/edit).
    if ($this->canRequestGetNode($event->getRequest())) {
      $nid = $event->getRequest()->attributes->get('node')->get('nid')->getValue()['0']['value'];
      if (!$this->accessCheckService->canUserAccessByNodeId($nid, false, $this->accessStorageService->getLangCode($nid))) {
        $accessDeniedEvent = new PermissionsByTermDeniedEvent($nid);
        $this->eventDispatcher->dispatch(PermissionsByTermDeniedEvent::NAME, $accessDeniedEvent);

        $this->sendUserToAccessDeniedPage();
      }
    }

    // Restrict access to taxonomy terms by autocomplete list.
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {
      $query_string = $event->getRequest()->get('q');
      $query_string = trim($query_string);

      $tid = $this->term->getTermIdByName($query_string);

      $term = $this->term->getTerm();
      $termLangcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($term instanceof \Drupal\taxonomy\Entity\Term) {
        $termLangcode = $term->language()->getId();
      }

      if (!$this->accessCheckService->isAccessAllowedByDatabase($tid, \Drupal::currentUser()->id(), $termLangcode)) {
        $this->sendUserToAccessDeniedPage();
      }
    }
  }

  /**
   * Restricts access on kernel response.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $this->restrictTermAccessAtAutoCompletion($event);
  }

  /**
   * Restricts access to terms on AJAX auto completion.
   */
  private function restrictTermAccessAtAutoCompletion(FilterResponseEvent $event) {
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete'
    ) {
      $json_suggested_terms = $event->getResponse()->getContent();
      $suggested_terms = json_decode($json_suggested_terms);
      $allowed_terms = [];
      foreach ($suggested_terms as $term) {
        $tid = $this->term->getTermIdByName($term->label);
        $termLangcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        if ($this->term->getTerm() instanceof \Drupal\taxonomy\Entity\Term) {
          $termLangcode = $this->term->getTerm()->language()->getId();
        }

        if ($this->accessCheckService->isAccessAllowedByDatabase($tid, \Drupal::currentUser()->id(), $termLangcode)) {
          $allowed_terms[] = [
            'value' => $term->value,
            'label' => $term->label,
          ];
        }
      }

      $json_response = new JsonResponse($allowed_terms);
      $event->setResponse($json_response);
    }
  }

  /**
   * The subscribed events.
   */
  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => 'onKernelRequest',
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  private function canRequestGetNode(Request $request) {
    if (method_exists($request->attributes, 'get') && !empty($request->attributes->get('node'))) {
      if (method_exists($request->attributes->get('node'), 'get')) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private function sendUserToAccessDeniedPage() {
    $redirect_url = new \Drupal\Core\Url('system.403');
    $response = new RedirectResponse($redirect_url->toString());
    $response->send();
    return $response;
  }

}
