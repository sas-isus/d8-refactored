<?php

namespace Drupal\gathercontent_ui\Controller;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MappingController.
 *
 * @package Drupal\gathercontent\Controller
 */
class MappingController extends ControllerBase {

  /**
   * GatherContent client.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(GatherContentClientInterface $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gathercontent.client')
    );
  }

  /**
   * Page callback for connection testing page.
   *
   * @return array
   *   Content of the page.
   */
  public function testConnectionPage() {
    $message = $this->t('Connection successful.');

    try {
      $this->client->meGet();
    }
    catch (\Exception $e) {
      $message = $this->t("Connection wasn't successful.");
    }

    return [
      '#type' => 'markup',
      '#markup' => $message,
    ];
  }

}
