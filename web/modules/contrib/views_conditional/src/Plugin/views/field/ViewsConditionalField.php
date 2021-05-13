<?php

namespace Drupal\views_conditional\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_conditional_field")
 */
class ViewsConditionalField extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatter $dateFormatter, TimeInterface $dateTime) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $dateFormatter;
    $this->dateTime = $dateTime;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('date.formatter'), $container->get('datetime.time'));
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
    $this->field_alias = 'views_conditional_' . $this->position;
  }

  /**
   * Conditional operators.
   *
   * @var array
   */
  public $conditions = [
    'eq' => 'Equal to',
    'neq' => 'NOT equal to',
    'gt' => 'Greater than',
    'gte' => 'Greater than or equals',
    'lt' => 'Less than',
    'lte' => 'Less than or equals',
    'em' => 'Empty',
    'nem' => 'NOT empty',
    'cn' => 'Contains',
    'ncn' => 'Does NOT contain',
    'leq' => 'Length Equal to',
    'lneq' => 'Length NOT equal to',
    'lgt' => 'Length Greater than',
    'llt' => 'Length Less than',
  ];

  /**
   * Define the available options.
   *
   * @return array
   *   Returns the available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['label']['default'] = NULL;

    $options['if'] = ['default' => ''];
    $options['condition'] = ['default' => ''];
    $options['equalto'] = ['default' => ''];
    $options['then'] = ['default' => ''];
    $options['then_translate'] = ['default' => TRUE];
    $options['or'] = ['default' => ''];
    $options['or_translate'] = ['default' => TRUE];
    $options['strip_tags'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['views_conditional.settings'];
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['relationship']['#access'] = FALSE;
    $previous = $this->getPreviousFieldLabels();
    $fields = ['- ' . $this->t('no field selected') . ' -'];
    foreach ($previous as $id => $label) {
      $field[$id] = $label;
    }
    $fields += $field;

    $form['if'] = [
      '#type' => 'select',
      '#title' => $this->t('If this field...'),
      '#options' => $fields,
      '#default_value' => $this->options['if'],
    ];
    $form['condition'] = [
      '#type' => 'select',
      '#title' => $this->t('Is...'),
      '#options' => $this->conditions,
      '#default_value' => $this->options['condition'],
    ];
    $form['equalto'] = [
      '#type' => 'textfield',
      '#title' => $this->t('This value'),
      '#description' => $this->t('Input a value to compare the field against. Replacement variables may be used'),
      '#default_value' => $this->options['equalto'],
    ];
    $form['then'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Then output this...'),
      '#description' => $this->t('Input what should be output. Replacement variables may be used.'),
      '#default_value' => $this->options['then'],
    ];
    $form['then_translate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Translate "Then" output'),
      '#description' => $this->t('Translate custom text before any replacement values are substituted.'),
      '#default_value' => $this->options['then_translate'],
    ];
    $form['or'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Otherwise, output this...'),
      '#description' => $this->t('Input what should be output if the above conditions do NOT evaluate to true.'),
      '#default_value' => $this->options['or'],
    ];
    $form['or_translate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Translate "Or" output'),
      '#description' => $this->t('Translate custom text before any replacement values are substituted.'),
      '#default_value' => $this->options['or_translate'],
    ];
    $form['strip_tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Strip html tags from the output'),
      '#default_value' => $this->options['strip_tags'],
    ];
    $form['replacements'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Replacement Variables'),
    ];
    $form['replacements']['notice'] = [
      '#markup' => 'You may use any of these replacement variables in the "equals" or the "output" text fields. If you wish to use brackets ([ or ]), replace them with %5D or %5E.',
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    $items = [
      'DATE_UNIX => Current date / time, in UNIX timestamp format (' . $this->dateTime->getRequestTime() . ')',
      'DATE_STAMP => Current date / time, in standard format (' . $this->dateFormatter->format($this->dateTime->getRequestTime()) . ')',
    ];
    $views_fields = $this->view->display_handler->getHandlers('field');
    foreach ($views_fields as $field => $handler) {
      // We only use fields up to (not including) this one.
      if ($field == $this->options['id']) {
        break;
      }
      $items[] = "{{ $field }}";
    }
    $form['replacements']['variables'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (empty($values['options']['if']) || empty($values['options']['condition']) || empty($values['options']['equalto'])) {
      if (empty($values['options']['if'])) {
        $form_state->setErrorByName('if', $this->t("Please specify a valid field to run a condition on."));
      }
      if (empty($values['options']['condition'])) {
        $form_state->setErrorByName('condition', $this->t("Please select a conditional operator."));
      }
      // We using there is_numeric because values '0', '0.0' counts as empty.
      if (empty($values['options']['equalto']) && !in_array($values['options']['condition'], [
        'em',
        'nem',
      ]) && !is_numeric($values['options']['equalto'])
      ) {
        $form_state->setErrorByName('condition', $this->t("Please specify something to compare with."));
      }
    }
  }

  /**
   * Cleans a variable for handling later.
   */
  public function cleanVar($var) {
    $unparsed = isset($var->last_render) ? $var->last_render : '';
    return $this->options['strip_tags'] ? trim(strip_tags($unparsed)) : trim($unparsed);
  }

  /**
   * Create renderable markup for field values.
   *
   * @param $value
   *   The value to be displayed.
   *
   * @return
   *   The rendered value.
   */
  private function markup($value) {
    $value = [
      '#markup' => $value,
    ];
    return \Drupal::service('renderer')->render($value);
  }

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values) {
    $if = $this->options['if'];
    $condition = $this->options['condition'];
    $equalto = $this->options['equalto'];
    $then = $this->options['then'];
    $or = ($this->options['or'] ?: '');

    // Translate text to be displayed with a context specific to this module,
    // view and display.
    $translation_context = "views_conditional:view:{$this->view->id()}";

    // Translate prior to replacements, otherwise the dynamic replacement
    // content results in endless translations:
    if ($this->options['then_translate']) {
      $then = $this->t($then, ['context' => $translation_context]);
    }
    if ($this->options['or_translate']) {
      $or = $this->t($or, ['context' => $translation_context]);
    }

    // Gather field information.
    $fields = $this->view->display_handler->getHandlers('field');
    $labels = $this->view->display_handler->getFieldLabels();
    // Search through field information for possible replacement variables.
    foreach ($labels as $key => $var) {
      // If we find a replacement variable, replace it.
      if (strpos($equalto, "{{ $key }}") !== FALSE) {
        $field = $this->cleanVar($fields[$key]);
        $equalto = str_replace("{{ $key }}", $field, $equalto);
      }
      if (strpos($then, "{{ $key }}") !== FALSE) {
        $field = $this->cleanVar($fields[$key]);
        $then = str_replace("{{ $key }}", $field, $then);
      }
      if (strpos($or, "{{ $key }}") !== FALSE) {
        $field = $this->cleanVar($fields[$key]);
        $or = str_replace("{{ $key }}", $field, $or);
      }
    }

    // If we find a date stamp replacement, replace that.
    if (strpos($equalto, 'DATE_STAMP') !== FALSE) {
      $equalto = str_replace('DATE_STAMP', $this->dateFormatter->format($this->dateTime->getRequestTime()), $equalto);
    }
    if (strpos($then, 'DATE_STAMP') !== FALSE) {
      $then = str_replace('DATE_STAMP', $this->dateFormatter->format($this->dateTime->getRequestTime()), $then);
    }
    if (strpos($or, 'DATE_STAMP') !== FALSE) {
      $or = str_replace('DATE_STAMP', $this->dateFormatter->format($this->dateTime->getRequestTime()), $or);
    }

    // If we find a unix date stamp replacement, replace that.
    if (strpos($equalto, 'DATE_UNIX') !== FALSE) {
      $equalto = str_replace('DATE_UNIX', $this->dateTime->getRequestTime(), $equalto);
    }
    if (strpos($then, 'DATE_UNIX') !== FALSE) {
      $then = str_replace('DATE_UNIX', $this->dateTime->getRequestTime(), $then);
    }
    if (strpos($or, 'DATE_UNIX') !== FALSE) {
      $or = str_replace('DATE_UNIX', $this->dateTime->getRequestTime(), $or);
    }

    // Strip tags on the "if" field. Otherwise it appears to output as
    // <div class="xxx">Field data</div>...
    // ...which of course makes it difficult to compare.
    $r = isset($fields["$if"]->last_render) ? trim(strip_tags($fields["$if"]->last_render, '<img><video><iframe><audio>')) : NULL;

    // Run conditions.
    switch ($condition) {
      // Equal to.
      case 'eq':
        if ($r == $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Not equal to.
      case 'neq':
        if ($r !== $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Greater than.
      case 'gt':
        if ($r > $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Greater than or equals.
      case 'gte':
        if ($r >= $equalto) {
          return $then;
        }
        else {
          return $this->markup($or);
        }
        break;

      // Less than.
      case 'lt':
        if ($r < $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Less than or equals.
      case 'lte':
        if ($r <= $equalto) {
          return $then;
        }
        else {
          return $this->markup($or);
        }
        break;

      // Empty.
      case 'em':
        if (empty($r)) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Not empty.
      case 'nem':
        if (!empty($r)) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Contains.
      case 'cn':
        if (mb_stripos($r, $equalto) !== FALSE) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Does NOT contain.
      case 'ncn':
        if (mb_stripos($r, $equalto) === FALSE) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Length Equal to.
      case 'leq':
        if (mb_strlen($r) == $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Length Not equal to.
      case 'lneq':
        if (mb_strlen($r) !== $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Length Greater than.
      case 'lgt':
        if (mb_strlen($r) > $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;

      // Length Less than.
      case 'llt':
        if (mb_strlen($r) < $equalto) {
          return $this->markup($then);
        }
        else {
          return $this->markup($or);
        }
        break;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function clickSortable() {
    return FALSE;
  }

}
