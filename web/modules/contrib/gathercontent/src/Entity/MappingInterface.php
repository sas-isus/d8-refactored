<?php

namespace Drupal\gathercontent\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining GatherContent Mapping entities.
 */
interface MappingInterface extends ConfigEntityInterface {

  /**
   * Getter for GatherContent project ID property.
   *
   * @return int
   *   GatherContent project ID.
   */
  public function getGathercontentProjectId();

  /**
   * Getter for GatherContent project property.
   *
   * @return string
   *   GatherContent project name.
   */
  public function getGathercontentProject();

  /**
   * Getter for GatherContent template ID property.
   *
   * @return int
   *   GatherContent template ID.
   */
  public function getGathercontentTemplateId();

  /**
   * Getter for GatherContent template property.
   *
   * @return string
   *   GatherContent template name.
   */
  public function getGathercontentTemplate();

  /**
   * Getter for content type machine name.
   *
   * @return string
   *   Content type machine name.
   */
  public function getContentType();

  /**
   * Setter for content type machine name.
   *
   * @param string $content_type
   *   Content type machine name.
   */
  public function setContentType($content_type);

  /**
   * Getter for content type human name.
   *
   * @return string
   *   Content type human name.
   */
  public function getContentTypeName();

  /**
   * Setter for content type human name.
   *
   * @param string $content_type_name
   *   Content type human name.
   */
  public function setContentTypeName($content_type_name);

  /**
   * Getter for GatherContent template serialized object.
   *
   * @return string
   *   Serialized GatherContent template.
   */
  public function getTemplate();

  /**
   * Setter for GatherContent template serialized object.
   *
   * @param string $template
   *   Serialized GatherContent template.
   */
  public function setTemplate($template);

  /**
   * Getter for mapping data.
   *
   * @return string
   *   Serialized object of mapping.
   */
  public function getData();

  /**
   * Setter for mapping data.
   *
   * @param string $data
   *   Serialized object of mapping.
   */
  public function setData($data);

  /**
   * Setter for updated drupal property.
   *
   * @param string $updated_drupal
   *   Timestamp when was mapping updated.
   */
  public function setUpdatedDrupal($updated_drupal);

  /**
   * Validate if object is configured with mapping.
   *
   * @return bool
   *   Return TRUE if object has mapping, otherwise FALSE.
   */
  public function hasMapping();

  /**
   * Formatter for content type property.
   *
   * @return string
   *   If not empty return human name for content type, else return None string.
   */
  public function getFormattedContentType();

  /**
   * Formatter for updated drupal property.
   *
   * @return string
   *   If not empty return formatted date, else return string Never.
   */
  public function getFormatterUpdatedDrupal();

}
