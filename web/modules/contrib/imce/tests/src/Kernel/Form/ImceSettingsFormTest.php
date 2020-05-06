<?php

namespace Drupal\Tests\imce\Kernel\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\imce\Form\ImceSettingsForm;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Kernel tests for ImceSettingsForm.
 *
 * @group imce
 */
class ImceSettingsFormTest extends KernelTestBase {

  use StringTranslationTrait;

  /**
   * The IMCE sttings form.
   *
   * @var \Drupal\imce\Form\ImceSettingsForm
   */
  protected $imceSettingsForm;

  /**
   * The IMCE settings.
   *
   * @var object
   */
  protected $imceConfig;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'imce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->imceConfig = $this->container->get('config.factory')->get('imce.settings');
    $this->imceSettingsForm = new ImceSettingsForm(
      $this->container->get('config.factory'),
      $this->container->get('entity_type.manager'),
      $this->container->get('stream_wrapper_manager')
    );
  }

  /**
   * Test formId().
   */
  public function testFormId() {
    $this->assertTrue(is_string($this->imceSettingsForm->getFormId()));
    $this->assertEquals('imce_settings_form', $this->imceSettingsForm->getFormId());
  }

  /**
   * Test method getProfileOptions().
   */
  public function testProfileOptions() {
    $options = $this->imceSettingsForm->getProfileOptions();
    $this->assertTrue(is_array($options));
    $this->assertArraySubset($options, ['' => '-' . $this->t('None') . '-']);
  }

  /**
   * Test method buildHeaderProfilesTable().
   */
  public function testBuildHeaderProfilesTable() {
    $headerProfiles = $this->imceSettingsForm->buildHeaderProfilesTable();
    $this->assertTrue(is_array($headerProfiles));
  }

  /**
   * Test method buildRolesProfilesTable().
   */
  public function testBuildRolesProfilesTable() {
    $this->assertTrue(is_array(
      $this->imceSettingsForm->buildRolesProfilesTable($this->imceConfig->get('roles_profiles')  ?: [])
    ));
  }

}
