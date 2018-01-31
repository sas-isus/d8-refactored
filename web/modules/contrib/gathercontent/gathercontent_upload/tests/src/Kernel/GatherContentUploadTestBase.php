<?php

namespace Drupal\Tests\gathercontent_upload\Kernel;

use Cheppers\GatherContent\DataTypes\Item;
use Drupal\file\Entity\File;
use Drupal\gathercontent_upload\Export\Exporter;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

/**
 * Class GatherContentUploadTestBase.
 *
 * @package Drupal\Tests\gathercontent_upload\Kernel
 */
class GatherContentUploadTestBase extends EntityKernelTestBase {

  /**
   * Exporter class.
   *
   * @var \Drupal\gathercontent_upload\Export\Exporter
   */
  public $exporter;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'field',
    'image',
    'file',
    'taxonomy',
    'language',
    'content_translation',
    'entity_reference_revisions',
    'paragraphs',
    'metatag',
    'gathercontent',
    'gathercontent_upload',
    'gathercontent_upload_test_config',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('node');
    $this->installConfig(['gathercontent_upload_test_config']);
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('taxonomy_term');

    $container = \Drupal::getContainer();
    $this->exporter = Exporter::create($container);
  }

  /**
   * Returns the Node for the simple ProcessPane test.
   *
   * @return \Drupal\node\Entity\Node
   *   Node object.
   */
  public function getSimpleNode() {
    $image = File::create(['uri' => 'public://example1.png']);
    $image->save();

    $paragraph_1 = Paragraph::create([
      'type' => 'para',
      'field_text' => 'Test paragraph field',
      'field_image' => [['target_id' => $image->id()]],
    ]);
    $paragraph_1->save();

    $paragraph_2 = Paragraph::create([
      'type' => 'para_2',
      'field_text' => 'Test paragraph 2 field',
    ]);
    $paragraph_2->save();

    $term_1 = Term::create([
      'vid' => 'tags',
      'name' => 'First choice',
      'gathercontent_option_ids' => 'op1501678793028',
    ]);
    $term_1->save();

    $term_2 = Term::create([
      'vid' => 'tags',
      'name' => 'Choice1',
      'gathercontent_option_ids' => 'op1500994449663',
    ]);
    $term_2->save();

    return Node::create([
      'title' => 'Test node',
      'type' => 'page',
      'body' => 'Test body',
      'field_guidodo' => 'Test guide',
      'field_image' => [['target_id' => $image->id()]],
      'field_radio' => [['target_id' => $term_1->id()]],
      'field_tags_alt' => [['target_id' => $term_2->id()]],
      'field_para' => [
        [
          'target_id' => $paragraph_1->id(),
          'target_revision_id' => $paragraph_1->getRevisionId(),
        ],
        [
          'target_id' => $paragraph_2->id(),
          'target_revision_id' => $paragraph_2->getRevisionId(),
        ],
      ],
    ]);
  }

  /**
   * Returns Item for the simple ProcessPane test.
   *
   * @return \Cheppers\GatherContent\DataTypes\Item
   *   Item object.
   */
  public function getSimpleItem() {
    return new Item([
      'project_id' => 86701,
      'template_id' => 791717,
      'config' => [
        [
          'name' => 'tab1500994234813',
          'label' => 'Tab label',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1501675275975', 'Title', 'Title gc item'),
            $this->getSection('el1501679176743', 'Guido', 'Guido gc item'),
            $this->getRadio('el1501678793027', 'Radiogaga', [
              [
                'name' => 'op1501678793028',
                'label' => 'First choice',
                'selected' => FALSE,
              ],
              [
                'name' => 'op1501678793029',
                'label' => 'Second choice',
                'selected' => TRUE,
              ],
              [
                'name' => 'op1501678793030',
                'label' => 'Third choice',
                'selected' => FALSE,
              ],
            ]),
            $this->getRichText('el1500994248864', 'Body', 'Body gc item'),
            $this->getImage('el1501598415730', 'Image'),
            $this->getCheckbox('el1500994276297', 'Tags', [
              [
                'name' => 'op1500994449663',
                'label' => 'Choice1',
                'selected' => FALSE,
              ],
              [
                'name' => 'op1500994483697',
                'label' => 'Choice2',
                'selected' => FALSE,
              ],
            ]),
            $this->getRichText('el1501666239392', 'Para text', 'Para text gc item'),
            $this->getImage('el1501666248919', 'Para image'),
            $this->getRichText('el1501772184393', 'Para 2 text', 'Para 2 text gc item'),
          ],
        ],
      ],
    ]);
  }

  /**
   * Returns the Node for the multilang ProcessPane test.
   *
   * @return \Drupal\node\Entity\Node
   *   Node object.
   */
  public function getMultilangNode() {
    $manager = \Drupal::service('content_translation.manager');
    $image = File::create(['uri' => 'public://example1.png']);
    $image->save();

    $image2 = File::create(['uri' => 'public://example2.png']);
    $image2->save();

    $paragraph_1 = Paragraph::create([
      'type' => 'para',
      'langcode' => 'en',
      'field_text' => 'Test paragraph field',
      'field_image' => [['target_id' => $image->id()]],
    ]);
    $paragraph_1->save();
    $paragraph_1_hu = $paragraph_1->addTranslation('hu');
    $paragraph_1_hu->field_text->setValue('Test multilang paragraph HU');
    $paragraph_1_hu->field_image->setValue([['target_id' => $image2->id()]]);
    $manager->getTranslationMetadata($paragraph_1_hu)->setSource('en');
    $paragraph_1_hu->save();

    $paragraph_2 = Paragraph::create([
      'type' => 'para_2',
      'langcode' => 'en',
      'field_text' => 'Test paragraph 2 field',
    ]);
    $paragraph_2->save();
    $paragraph_2_hu = $paragraph_2->addTranslation('hu');
    $paragraph_2_hu->field_text->setValue('Test multilang paragraph 2 HU');
    $manager->getTranslationMetadata($paragraph_2_hu)->setSource('en');
    $paragraph_2_hu->save();

    $term_1 = Term::create([
      'vid' => 'tags',
      'langcode' => 'en',
      'name' => 'First choice',
      'gathercontent_option_ids' => 'op1503046753704',
    ]);
    $term_1->save();

    $term_1_hu = Term::create([
      'vid' => 'tags',
      'langcode' => 'en',
      'name' => 'Second choice',
      'gathercontent_option_ids' => 'op15030467537057882',
    ]);
    $term_1_hu->save();

    $term_2 = Term::create([
      'vid' => 'tags',
      'langcode' => 'en',
      'name' => 'Choice1',
      'gathercontent_option_ids' => 'op1503046763383',
    ]);
    $term_2->save();

    $term_2_hu = Term::create([
      'vid' => 'tags',
      'langcode' => 'en',
      'name' => 'Choice2',
      'gathercontent_option_ids' => 'op1503046763384321',
    ]);
    $term_2_hu->save();

    $node = Node::create([
      'title' => 'Test multilang node',
      'langcode' => 'en',
      'type' => 'test_content',
      'body' => 'Test multilang body',
      'field_guidodo' => 'Test guide',
      'field_image' => [['target_id' => $image->id()]],
      'field_radio' => [['target_id' => $term_1->id()]],
      'field_tags' => [['target_id' => $term_2->id()]],
      'field_para' => [
        [
          'target_id' => $paragraph_1->id(),
          'target_revision_id' => $paragraph_1->getRevisionId(),
        ],
        [
          'target_id' => $paragraph_2->id(),
          'target_revision_id' => $paragraph_2->getRevisionId(),
        ],
      ],
    ]);
    $node->save();

    $node_hu = $node->addTranslation('hu');
    $node_hu->setTitle('Test multilang node HU');
    $node_hu->body->setValue('Test multilang body HU');
    $node_hu->field_guidodo->setValue('Test multilang guide HU');
    $node_hu->field_image->setValue([['target_id' => $image2->id()]]);
    $node_hu->field_radio->setValue([['target_id' => $term_1_hu->id()]]);
    $node_hu->field_tags->setValue([['target_id' => $term_2_hu->id()]]);
    $node_hu->field_para->setValue([
      [
        'target_id' => $paragraph_1->id(),
        'target_revision_id' => $paragraph_1->getRevisionId(),
      ],
      [
        'target_id' => $paragraph_2->id(),
        'target_revision_id' => $paragraph_2->getRevisionId(),
      ],
    ]);
    $manager->getTranslationMetadata($node_hu)->setSource('en');
    $node_hu->save();

    return $node;
  }

  /**
   * Returns Item for the multilang ProcessPane test.
   *
   * @return \Cheppers\GatherContent\DataTypes\Item
   *   Item object.
   */
  public function getMultilangItem() {
    return new Item([
      'project_id' => 86701,
      'template_id' => 821317,
      'config' => [
        [
          'name' => 'tab1502959217871',
          'label' => 'EN',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1502959595615', 'Title', 'Title gc item'),
            $this->getRichText('el1502959226216', 'Body', 'Body gc item'),
            $this->getImage('el1503046930689', 'Image'),
            $this->getRadio('el1503046753703', 'Radiogaga', [
              [
                'name' => 'op1503046753704',
                'label' => 'First choice',
                'selected' => FALSE,
              ],
              [
                'name' => 'op1503046753705',
                'label' => 'Second choice',
                'selected' => TRUE,
              ],
              [
                'name' => 'op1503046753706',
                'label' => 'Third choice',
                'selected' => FALSE,
              ],
            ]),
            $this->getCheckbox('el1503046763382', 'Tags', [
              [
                'name' => 'op1503046763383',
                'label' => 'Choice1',
                'selected' => FALSE,
              ],
              [
                'name' => 'op1503046763384',
                'label' => 'Choice2',
                'selected' => FALSE,
              ],
            ]),
            $this->getRichText('el1503046796344', 'Para text', 'Para text gc item'),
            $this->getImage('el1503046889180', 'Para image'),
            $this->getRichText('el1503046917174', 'Para 2 text', 'Para 2 text gc item'),
            $this->getSection('el1503050151209', 'Guido', 'Guido gc item'),
          ],
        ],
        [
          'name' => 'tab1503046938794',
          'label' => 'HU',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1503046938794', 'Title', 'Title gc item HU'),
            $this->getRichText('el1503046938795', 'Body', 'Body gc item HU'),
            $this->getImage('el1503046938796', 'Image'),
            $this->getRadio('el1503046938797', 'Radiogaga', [
              [
                'name' => 'op15030467537046960',
                'label' => 'First choice',
                'selected' => TRUE,
              ],
              [
                'name' => 'op15030467537057882',
                'label' => 'Second choice',
                'selected' => FALSE,
              ],
              [
                'name' => 'op15030467537069199',
                'label' => 'Third choice',
                'selected' => FALSE,
              ],
            ]),
            $this->getCheckbox('el1503046938798', 'Tags', [
              [
                'name' => 'op1503046763383887',
                'label' => 'Choice1',
                'selected' => FALSE,
              ],
              [
                'name' => 'op1503046763384321',
                'label' => 'Choice2',
                'selected' => FALSE,
              ],
            ]),
            $this->getRichText('el1503046938799', 'Para text', 'Para text gc item'),
            $this->getImage('el1503046938800', 'Para image'),
            $this->getRichText('el1503046938801', 'Para 2 text', 'Para 2 text gc item'),
            $this->getSection('el1503050171534', 'Guido', 'Guido gc item'),
          ],
        ],
      ],
    ]);
  }

  /**
   * Returns the Node for the meta tag ProcessPane test.
   *
   * @return \Drupal\node\Entity\Node
   *   Node object.
   */
  public function getMetatagNode() {
    $node = Node::create([
      'title' => 'Test metatag node',
      'type' => 'test_content_meta',
      'body' => 'Test metatag body',
    ]);
    $node->get('field_meta_test')->setValue(serialize([
      'title' => 'Test meta title',
      'description' => 'Test meta description',
    ]));

    return $node;
  }

  /**
   * Returns Item for the meta tag ProcessPane test.
   *
   * @return \Cheppers\GatherContent\DataTypes\Item
   *   Item object.
   */
  public function getMetatagItem() {
    return new Item([
      'project_id' => 86701,
      'template_id' => 823399,
      'config' => [
        [
          'name' => 'tab1503044944021',
          'label' => 'Content',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1503045026098', 'Title', 'Title gc item'),
            $this->getRichText('el1503045033295', 'Body', 'Body gc item'),
          ],
        ],
        [
          'name' => 'tab1503045040084',
          'label' => 'Meta',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1503045047082', 'Title', 'Title gc item meta'),
            $this->getRichText('el1503045054663', 'Description', 'Description gc item meta'),
          ],
        ],
      ],
    ]);
  }

  /**
   * Returns the Node for the meta tag multilang ProcessPane test.
   *
   * @return \Drupal\node\Entity\Node
   *   Node object.
   */
  public function getMetatagMultilangNode() {
    $node = Node::create([
      'title' => 'Test metatag node',
      'type' => 'test_content',
      'body' => 'Test metatag body',
    ]);
    $node->get('field_meta_alt')->setValue(serialize([
      'title' => 'Test meta title',
      'description' => 'Test meta description',
    ]));

    return $node;
  }

  /**
   * Returns Item for the meta tag multilang ProcessPane test.
   *
   * @return \Cheppers\GatherContent\DataTypes\Item
   *   Item object.
   */
  public function getMetatagMultilangItem() {
    return new Item([
      'project_id' => 86701,
      'template_id' => 429623,
      'config' => [
        [
          'name' => 'tab1475138035227',
          'label' => 'Content',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1502978044104', 'Title', 'Title gc item'),
            $this->getRichText('el1475138048898', 'Body', 'Body gc item'),
          ],
        ],
        [
          'name' => 'tab1475138055858',
          'label' => 'Meta',
          'hidden' => FALSE,
          'elements' => [
            $this->getPlainText('el1475138068185', 'Title', 'Title gc item meta'),
            $this->getRichText('el1475138069769', 'Description', 'Description gc item meta'),
          ],
        ],
      ],
    ]);
  }

  /**
   * Returns plain text field array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param string $value
   *   Value string.
   *
   * @return array
   *   Plain text item array.
   */
  public function getPlainText($name, $label, $value) {
    return $this->getText($name, $label, $value, TRUE);
  }

  /**
   * Returns rich text field array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param string $value
   *   Value string.
   *
   * @return array
   *   Rich text item array.
   */
  public function getRichText($name, $label, $value) {
    return $this->getText($name, $label, $value, FALSE);
  }

  /**
   * Returns field array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param string $value
   *   Value string.
   * @param bool $isPlainText
   *   If TRUE then the field must be plain text.
   *
   * @return array
   *   Return item array.
   */
  public function getText($name, $label, $value, $isPlainText) {
    return [
      'name' => $name,
      'type' => 'text',
      'label' => $label,
      'required' => FALSE,
      'microcopy' => '',
      'limit_type' => 'words',
      'limit' => 0,
      'plain_text' => $isPlainText,
      'value' => $value,
    ];
  }

  /**
   * Returns section array.
   *
   * @param string $name
   *   Name string.
   * @param string $title
   *   Title string.
   * @param string $subtitle
   *   Subtitle string.
   *
   * @return array
   *   Return item array.
   */
  public function getSection($name, $title, $subtitle) {
    return [
      'name' => $name,
      'type' => 'section',
      'title' => $title,
      'subtitle' => $subtitle,
    ];
  }

  /**
   * Returns radio array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param array $option
   *   Option array.
   *
   * @return array
   *   Return item array.
   */
  public function getRadio($name, $label, array $option) {
    return $this->getSelection($name, $label, $option, 'choice_radio');
  }

  /**
   * Returns checkbox array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param array $option
   *   Option array.
   *
   * @return array
   *   Return item array.
   */
  public function getCheckbox($name, $label, array $option) {
    return $this->getSelection($name, $label, $option, 'choice_checkbox');
  }

  /**
   * Returns selection (radio/checkbox) array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   * @param array $option
   *   Option array.
   * @param string $type
   *   Type string.
   *
   * @return array
   *   Return item array.
   */
  public function getSelection($name, $label, array $option, $type) {
    return [
      'name' => $name,
      'type' => $type,
      'label' => $label,
      'required' => FALSE,
      'microcopy' => '',
      'options' => $option,
      'other_option' => FALSE,
    ];
  }

  /**
   * Returns image array.
   *
   * @param string $name
   *   Name string.
   * @param string $label
   *   Label string.
   *
   * @return array
   *   Return item array.
   */
  public function getImage($name, $label) {
    return [
      'name' => $name,
      'type' => 'files',
      'label' => $label,
      'required' => FALSE,
      'microcopy' => '',
    ];
  }

}
