<?php

namespace Drupal\google_tag\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides an interface for a Google tag container manager.
 */
interface ContainerManagerInterface {

  /**
   * Constructs a ContainerManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler);

  /**
   * Prepares directory for and saves snippet files for a container.
   *
   * @todo Which class-interface to use on @param?
   *
   * @param Drupal\Core\Config\Entity\ConfigEntityInterface $container
   *   The container configuration entity.
   *
   * @return bool
   *   Whether the files were saved.
   */
  public function createAssets(ConfigEntityInterface $container);

  /**
   * Saves JS snippet files based on current settings.
   *
   * @param Drupal\Core\Config\Entity\ConfigEntityInterface $container
   *   The container configuration entity.
   *
   * @return bool
   *   Whether the files were saved.
   */
  public function saveSnippets(ConfigEntityInterface $container);

  /**
   * Adds render array items of page attachments.
   *
   * @param array $attachments
   *   The attachments render array.
   */
  public function getScriptAttachments(array &$attachments);

  /**
   * Adds render array items of page top attachments.
   *
   * @param array $page
   *   The page render array.
   */
  public function getNoScriptAttachments(array &$page);

  /**
   * Prepares directory for and saves snippet files for all containers.
   *
   * @return bool
   *   Whether the files were saved.
   */
  public function createAllAssets();

}
