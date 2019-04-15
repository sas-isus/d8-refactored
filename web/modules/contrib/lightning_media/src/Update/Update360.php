<?php

namespace Drupal\lightning_media\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_roles\ContentRoleManager;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("3.6.0")
 */
final class Update360 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity browser entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $entityBrowserStorage;

  /**
   * The embed button entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $embedButtonStorage;

  /**
   * The content role manager service.
   *
   * @var \Drupal\lightning_roles\ContentRoleManager
   */
  private $contentRoleManager;

  /**
   * Update360 constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_browser_storage
   *   The entity browser entity storage handler.
   * @param \Drupal\Core\Entity\EntityStorageInterface $embed_button_storage
   *   The embed button entity storage handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   (optional) The string translation service.
   * @param \Drupal\lightning_roles\ContentRoleManager $content_role_manager
   *   (optional) The content role manager service.
   */
  public function __construct(EntityStorageInterface $entity_browser_storage, EntityStorageInterface $embed_button_storage, TranslationInterface $translation = NULL, ContentRoleManager $content_role_manager = NULL) {
    $this->entityBrowserStorage = $entity_browser_storage;
    $this->embedButtonStorage = $embed_button_storage;

    if ($translation) {
      $this->setStringTranslation($translation);
    }
    $this->contentRoleManager = $content_role_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    $arguments = [
      $entity_type_manager->getStorage('entity_browser'),
      $entity_type_manager->getStorage('embed_button'),
      $container->get('string_translation'),
    ];
    if ($container->has('lightning.content_roles')) {
      $arguments[] = $container->get('lightning.content_roles');
    }

    return (new \ReflectionClass(static::class))
      ->newInstanceArgs($arguments);
  }

  /**
   * Changes the media browser to display in a modal dialog.
   *
   * @update
   */
  public function cloneMediaBrowser(StyleInterface $io) {
    /** @var \Drupal\embed\EmbedButtonInterface $button */
    $button = $this->embedButtonStorage->load('media_browser');
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->entityBrowserStorage->load('media_browser');

    // If either the embed button or the media browser doesn't exist, there's
    // nothing we can really do here.
    if (empty($button) || empty($browser)) {
      return;
    }
    // If the media browser isn't using an iFrame, then there's nothing we need
    // to do here anyway.
    if ($browser->getDisplay()->getPluginId() !== 'iframe') {
      return;
    }
    // If the embed button doesn't use the media browser, we can assume that
    // the configuration has deviated too far from what we ship for us to bother
    // with the update.
    if ($button->getTypePlugin()->getConfigurationValue('entity_browser') !== $browser->id()) {
      return;
    }

    $variables = [
      '@browser' => $browser->label(),
    ];

    $question = (string) $this->t('Do you want to display the @browser entity browser in a modal dialog? This will create a duplicate of the entity browser, specifically for use by CKEditor.', $variables);
    if ($io->confirm($question) === FALSE) {
      return;
    }

    $clone_id = $io->ask((string) $this->t('Enter the machine name of the CKEditor-only duplicate.'), 'ckeditor_media_browser');
    $clone_label = $io->ask((string) $this->t('Enter the label of the CKEditor-only duplicate.'), (string) $this->t('@browser (CKEditor)', $variables));

    $clone = $browser
      ->createDuplicate()
      ->setName($clone_id)
      ->setLabel($clone_label);
    $this->entityBrowserStorage->save($clone);

    // Ensure that media roles can access the new entity browser.
    $permissions = ["access $clone_id entity browser pages"];
    user_role_grant_permissions('media_creator', $permissions);
    user_role_grant_permissions('media_manager', $permissions);
    if ($this->contentRoleManager) {
      $this->contentRoleManager->grantPermissions('creator', $permissions);
    }

    // For reasons unknown, $button->getTypePlugin()->setConfigurationValue()
    // does not work for this.
    $settings = [
      'entity_browser' => $clone->id(),
    ];
    $button->set('type_settings', $settings + $button->getTypeSettings());
    $this->embedButtonStorage->save($button);

    $browser->setDisplay('modal')->getDisplay()->setConfiguration([
      'width' => '',
      'height' => '',
      'link_text' => (string) $this->t('Add media'),
      'auto_open' => FALSE,
    ]);
    $this->entityBrowserStorage->save($browser);
  }

}
