<?php

namespace Drupal\Tests\gathercontent\Kernel;

use Drupal\gathercontent\Import\ContentProcess\ContentProcessor;
use Drupal\gathercontent\Import\Importer;
use Drupal\gathercontent\MetatagQuery;
use Drupal\gathercontent_test\MockData;
use Drupal\gathercontent_test\MockDrupalGatherContentClient;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Base class for import kernel tests.
 */
class GcImportTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node', 'text', 'field', 'user', 'image', 'file', 'taxonomy', 'language',
    'content_translation', 'paragraphs', 'entity_reference_revisions', 'system',
    'metatag', 'menu_ui', 'menu_link_content', 'link', 'gathercontent', 'gathercontent_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('node');
    $this->installEntitySchema('gathercontent_operation');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('user');
    $this->installEntitySchema('menu_link_content');
    $this->installConfig(['gathercontent_test']);
    MockData::$drupalRoot = $this->getDrupalRoot();
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = MockData::createTaxonomyTerms();
    foreach ($terms as $term) {
      $term->save();
    }
  }

  /**
   * Get singleton Importer injected with mock object.
   */
  public static function getImporter() {
    return new Importer(
      static::getClient(),
      static::getProcessor(),
      \Drupal::service('event_dispatcher')
    );
  }

  /**
   * Get singleton ContentProcessor injected with mock object.
   */
  public static function getProcessor() {
    static $processor = NULL;
    if ($processor === NULL) {
      $processor = new ContentProcessor(
        static::getClient(),
        static::getMetatag()
      );
    }
    return $processor;
  }

  /**
   * Get singleton MetatagQuery object.
   */
  public static function getMetatag() {
    static $metatag = NULL;
    if ($metatag === NULL) {
      $metatag = new MetatagQuery(
        \Drupal::service('entity_field.manager')
      );
    }
    return $metatag;
  }

  /**
   * Get singleton Mock client.
   */
  public static function getClient() {
    static $client = NULL;
    if ($client === NULL) {
      $client = new MockDrupalGatherContentClient(
        \Drupal::service('http_client')
      );
    }
    return $client;
  }

}
