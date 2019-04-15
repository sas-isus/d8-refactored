<?php

namespace Drupal\lightning_media\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("2.4.0")
 */
final class Update240 implements ContainerInjectionInterface {

  /**
   * The field config storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fieldConfigStorage;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Update240 constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $field_config_storage
   *   The field config storage.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   */
  public function __construct(EntityStorageInterface $field_config_storage, ModuleInstallerInterface $module_installer) {
    $this->fieldConfigStorage = $field_config_storage;
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('field_config'),
      $container->get('module_installer')
    );
  }

  /**
   * Renames the "Save to my media library" field of all media types.
   *
   * @update
   *
   * @ask Do you want to rename the "Save to my media library" field of all
   * media types to "Show in media library"?
   */
  public function renameSaveToMyMediaLibrary() {
    /** @var \Drupal\field\Entity\FieldConfig[] $field_configs */
    $field_configs = $this->fieldConfigStorage->loadByProperties([
      'field_name' => 'field_media_in_library',
      'entity_type' => 'media',
    ]);

    foreach ($field_configs as $field_config) {
      $field_config
        ->setLabel('Show in media library')
        ->setSettings([
          'on_label' => 'Shown in media library',
          'off_label' => 'Hidden in media library',
        ])
        ->save();
    }
  }

  /**
   * Enables Audio file media.
   *
   * @update
   *
   * @ask Do you want to install "Audio file" media?
   */
  public function enableAudioFileMedia() {
    $this->moduleInstaller->install(['lightning_media_audio']);
  }

}
