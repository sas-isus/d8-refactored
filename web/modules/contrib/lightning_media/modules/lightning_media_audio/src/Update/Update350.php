<?php

namespace Drupal\lightning_media_audio\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("3.5.0")
 */
final class Update350 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The media type entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $mediaTypeStorage;

  /**
   * The field config entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fieldStorage;

  /**
   * Update350 constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $media_type_storage
   *   The media type entity storage handler.
   * @param \Drupal\Core\Entity\EntityStorageInterface $field_storage
   *   The field config entity storage handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   (optional) The string translation service.
   */
  public function __construct(EntityStorageInterface $media_type_storage, EntityStorageInterface $field_storage, TranslationInterface $translation = NULL) {
    $this->mediaTypeStorage = $media_type_storage;
    $this->fieldStorage = $field_storage;

    if ($translation) {
      $this->setStringTranslation($translation);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('media_type'),
      $container->get('entity_type.manager')->getStorage('field_config'),
      $container->get('string_translation')
    );
  }

  /**
   * Makes field_media_in_library non-translatable.
   *
   * @update
   *
   * @param \Symfony\Component\Console\Style\StyleInterface $io
   *   The I/O handler.
   */
  public function removeAudioFileLibraryFieldTranslatability(StyleInterface $io) {
    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = $this->fieldStorage->load('media.audio_file.field_media_in_library');
    if (empty($field)) {
      return;
    }

    $question = (string) $this->t('Do you want to remove translatability for the @field field of @media_type media?', [
      '@field' => $field->label(),
      '@media_type' => $this->mediaTypeStorage->load('audio_file')->label(),
    ]);
    if ($io->confirm($question)) {
      $this->fieldStorage->save($field->setTranslatable(FALSE));
    }
  }

}
