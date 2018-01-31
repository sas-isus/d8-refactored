<?php

namespace Drupal\gathercontent_ui\Form\MappingEditSteps;

use Cheppers\GatherContent\DataTypes\Template;
use Drupal\gathercontent\Entity\MappingInterface;

/**
 * Class MappingStepFactory.
 *
 * @package Drupal\gathercontent_ui\Form\MappingEditSteps
 */
class MappingStepService {

  protected $newStep;

  protected $editStep;

  protected $entityReferenceStep;

  /**
   * Returns new step object.
   *
   * @param \Drupal\gathercontent\Entity\MappingInterface $mapping
   *   Mapping object.
   * @param \Cheppers\GatherContent\DataTypes\Template $template
   *   Template object.
   *
   * @return \Drupal\gathercontent_ui\Form\MappingEditSteps\MappingStepNew
   *   MappingStepNew object.
   */
  public function getNewStep(MappingInterface $mapping, Template $template) {
    if ($this->newStep === NULL) {
      $this->newStep = new MappingStepNew($mapping, $template);
    }

    return $this->newStep;
  }

  /**
   * Returns new step object.
   *
   * @param \Drupal\gathercontent\Entity\MappingInterface $mapping
   *   Mapping object.
   * @param \Cheppers\GatherContent\DataTypes\Template $template
   *   Template object.
   *
   * @return \Drupal\gathercontent_ui\Form\MappingEditSteps\MappingStepEdit
   *   MappingStepEdit object.
   */
  public function getEditStep(MappingInterface $mapping, Template $template) {
    if ($this->editStep === NULL) {
      $this->editStep = new MappingStepEdit($mapping, $template);
    }

    return $this->editStep;
  }

  /**
   * Returns new step object.
   *
   * @param \Drupal\gathercontent\Entity\MappingInterface $mapping
   *   Mapping object.
   * @param \Cheppers\GatherContent\DataTypes\Template $template
   *   Template object.
   *
   * @return \Drupal\gathercontent_ui\Form\MappingEditSteps\MappingStepEntityReference
   *   MappingStepEntityReference object.
   */
  public function getEntityReferenceStep(MappingInterface $mapping, Template $template) {
    if ($this->entityReferenceStep === NULL) {
      $this->entityReferenceStep = new MappingStepEntityReference($mapping, $template);
    }

    if ($this->newStep !== NULL) {
      $this->entityReferenceStep->setEntityReferenceFields($this->newStep->getEntityReferenceFields());
      $this->entityReferenceStep->setEntityReferenceFieldsOptions($this->newStep->getEntityReferenceFieldsOptions());
    }

    if ($this->editStep !== NULL) {
      $this->entityReferenceStep->setEntityReferenceFields($this->editStep->getEntityReferenceFields());
      $this->entityReferenceStep->setEntityReferenceFieldsOptions($this->editStep->getEntityReferenceFieldsOptions());
    }

    return $this->entityReferenceStep;
  }

}
