<?php

namespace Drupal\lightning_media\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("3.1.0")
 */
final class Update310 implements ContainerInjectionInterface {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Update310 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('module_installer'));
  }

  /**
   * Enables the Media Slideshow sub-component.
   *
   * @update
   *
   * @ask Do you want to add support for creating slideshows and carousels
   * of media assets?
   */
  public function enableMediaSlideshow() {
    $this->moduleInstaller->install(['lightning_media_slideshow']);
  }

}
