<?php

namespace Drupal\twitter_profile_widget\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TwitterWidgetSubscriber.
 *
 * @package Drupal\twitter_profile_widget
 */
class TwitterWidgetSubscriber implements EventSubscriberInterface {

  /**
   * The language manager object for retrieving the correct language code.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * A config object for the system performance configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * A policy rule determining the cacheability of a request.
   *
   * @var \Drupal\Core\PageCache\RequestPolicyInterface
   */
  protected $requestPolicy;

  /**
   * A policy rule determining the cacheability of the response.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface
   */
  protected $responsePolicy;

  /**
   * The cache contexts manager service.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheContexts;

  /**
   * Whether to send cacheability headers for debugging purposes.
   *
   * @var bool
   */
  protected $debugCacheabilityHeaders = FALSE;

  /**
   * Constructs a TwitterWidgetSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager object for retrieving the correct language code.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $request_policy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $response_policy
   *   A policy rule determining the cacheability of a response.
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $cache_contexts_manager
   *   The cache contexts manager service.
   * @param bool $http_response_debug_cacheability_headers
   *   (optional) Whether to send cacheability headers for debugging purposes.
   */
  public function __construct(LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, CacheContextsManager $cache_contexts_manager, $http_response_debug_cacheability_headers = FALSE) {
    $this->languageManager = $language_manager;
    $this->config = $config_factory->get('system.performance');
    $this->requestPolicy = $request_policy;
    $this->responsePolicy = $response_policy;
    $this->cacheContextsManager = $cache_contexts_manager;
    $this->debugCacheabilityHeaders = $http_response_debug_cacheability_headers;
  }

  /**
   * Sets extra headers on successful responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {

    $request = $event->getRequest();
    $response = $event->getResponse();

    $is_cacheable = $response instanceof CacheableResponseInterface && ($this->requestPolicy->check($request) === RequestPolicyInterface::ALLOW) && ($this->responsePolicy->check($response, $request) !== ResponsePolicyInterface::DENY);

    if ($is_cacheable) {
      $response_cacheability = $response->getCacheableMetadata();
      $tags = $response_cacheability->getCacheTags();
      if (in_array('twitter_profile_widget', $tags)) {
        $config = \Drupal::config('twitter_profile_widget.settings');
        $expire = $config->get('expire_internal_cache');
        if ($expire) {
          // Only act on pages that render the twitter_widget_view.
          $this->setExpiresCacheLifetime($response);
        }
      }
    }

  }

  /**
   * Set cache lifetime to cache.page.max_age.
   *
   * This overrides the default logic provided by Internal Page Cache.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   A response object.
   */
  protected function setExpiresCacheLifetime(Response $response) {
    $max_age = \Drupal::config('twitter_profile_widget.settings')
      ->get('twitter_widget_cache_time');
    $response->setExpires(\DateTime::createFromFormat('U', time() + $max_age));
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
