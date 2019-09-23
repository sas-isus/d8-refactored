<?php

namespace Drupal\google_tag\Entity;

use Drupal\google_tag\Entity\ContainerManagerInterface;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the Google tag container manager.
 */
class ContainerManager implements ContainerManagerInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function createAssets(ConfigEntityInterface $container) {
    $result = TRUE;
    $directory = $container->snippetDirectory();
    if (!is_dir($directory) || !_google_tag_is_writable($directory) || !_google_tag_is_executable($directory)) {
      $result = __file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    }
    if ($result) {
      $result = $this->saveSnippets($container);
    }
    else {
      $args = ['%directory' => $directory];
      $message = 'The directory %directory could not be prepared for use, possibly due to file system permissions. The directory either does not exist, or is not writable or searchable.';
      $this->displayMessage($message, $args, 'error');
      \Drupal::logger('google_tag')->error($message, $args);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function saveSnippets(ConfigEntityInterface $container) {
    // Save the altered snippets after hook_google_tag_snippets_alter().
    $result = TRUE;
    $snippets = $container->snippets();
    foreach ($snippets as $type => $snippet) {
      $uri = $container->snippetURI($type);
      $path = file_unmanaged_save_data($snippet, $uri, FILE_EXISTS_REPLACE);
      $result = !$path ? FALSE : $result;
    }
    $args = ['@count' => count($snippets)];
    if (!$result) {
      $message = 'An error occurred saving @count snippet files. Contact the site administrator if this persists.';
      $this->displayMessage($message, $args, 'error');
      \Drupal::logger('google_tag')->error($message, $args);
    }
    else {
      $message = 'Created @count snippet files based on configuration.';
      $this->displayMessage($message, $args);
      // @todo It may be sufficient to only call
      // $this->state->delete('system.js_cache_files');
      // which is first thing done by deleteAll(). This clears the list of JS
      // files to include on page in event our JS files are deleted (which would
      // be done manually as the module is not). This may have been noticed on
      // 7.x branch after uninstall. It may be that only the second statement
      // below is needed here to change the query argument on the JS url.
      // Drupal/Core/Asset/JsCollectionOptimizer->deleteAll();
      \Drupal::service('asset.js.collection_optimizer')->deleteAll();
      _drupal_flush_css_js();
    }
    return $result;
  }

  /**
   * Displays a message to admin users.
   *
   * See arguments to t() and drupal_set_message().
   *
   * @param string $message
   *   The message to display.
   * @param array $args
   *   (optional) An associative array of replacements.
   * @param string $type
   *   (optional) The message type. Defaults to 'status'.
   */
  public function displayMessage($message, array $args = [], $type = 'status') {
    global $_google_tag_display_message;
    if ($_google_tag_display_message) {
      drupal_set_message($this->t($message, $args), $type);
    }
  }

  /**
   * Adds page attachments.
   *
   * @param array $attachments
   *   The attachments array.
   */
  public function loadContainerIDs() {
    return \Drupal::entityQuery('google_tag_container')
      ->condition('status', 1)
      ->sort('weight')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getScriptAttachments(array &$attachments) {
    $ids = $this->loadContainerIDs();
    $containers = $this->entityTypeManager->getStorage('google_tag_container')->loadMultiple($ids);
    foreach ($containers as $container) {
      if (!$container->insertSnippet()) {
        continue;
      }

      static $weight = 9;
      $include_script_as_file = \Drupal::config('google_tag.settings')->get('include_file');
      $include_classes = $container->get('include_classes');
      // @todo Only want one data_layer snippet even with multiple containers.
      // If user sorts containers such that the first does not define the data
      // layer, then validate this or adjust for it here.
      // Sort the items being added and put the data_layer at top?
      $types = $include_classes ? ['data_layer', 'script'] : ['script'];

      // Add data_layer and script snippets to head (no longer by default).
      if ($include_script_as_file) {
        foreach ($types as $type) {
          // @todo Will it matter if file is empty?
          // @todo Check config for the whitelist and blacklist classes before adding.
          $attachments['#attached']['html_head'][] = $container->fileTag($type, $weight++);
        }
      }
      else {
        foreach ($types as $type) {
          // @see drupal_get_js() in 7.x core.
          // For inline JavaScript to validate as XHTML, all JavaScript containing
          // XHTML needs to be wrapped in CDATA.
          $attachments['#attached']['html_head'][] = $container->inlineTag($type, $weight++);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNoScriptAttachments(array &$page) {
    $ids = $this->loadContainerIDs();
    $containers = $this->entityTypeManager->getStorage('google_tag_container')->loadMultiple($ids);
    foreach ($containers as $container) {
      if (!$container->insertSnippet()) {
        continue;
      }

      $page += $container->noscriptTag();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createAllAssets() {
    $ids = $this->loadContainerIDs();
    if (!$ids) {
      return;
    }
    // Remove any stale files (e.g. module update or machine name change).
    @file_unmanaged_delete_recursive(\Drupal::config('google_tag.settings')->get('uri') . '/');
    // Create snippet files for enabled containers.
    $containers = $this->entityTypeManager->getStorage('google_tag_container')->loadMultiple($ids);
    $result = TRUE;
    foreach ($containers as $container) {
      $result = !$this->createAssets($container) ? FALSE : $result;
    }
    return $result;
  }

}
