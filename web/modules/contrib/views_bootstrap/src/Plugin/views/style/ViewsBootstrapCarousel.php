<?php

namespace Drupal\views_bootstrap\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item as a row in a Bootstrap Carousel.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_bootstrap_carousel",
 *   title = @Translation("Bootstrap Carousel"),
 *   help = @Translation("Displays rows in a Bootstrap Carousel."),
 *   theme = "views_bootstrap_carousel",
 *   theme_file = "../views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class ViewsBootstrapCarousel extends StylePluginBase {
  /**
   * Whether or not this style uses a row plugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Whether the config form exposes the class to provide on each row.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['interval'] = ['default' => 5000];
    $options['navigation'] = ['default' => TRUE];
    $options['indicators'] = ['default' => TRUE];
    $options['pause'] = ['default' => TRUE];
    $options['wrap'] = ['default' => TRUE];
    $options['columns'] = ['default' => 1];
    $options['breakpoints'] = ['default' => 'md'];

    $options['display'] = ['default' => 'fields'];
    $options['image'] = ['default' => ''];
    $options['title'] = ['default' => ''];
    $options['description'] = ['default' => ''];

    return $options;
  }

  /**
   * Render the given style.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['help'] = [
      '#markup' => $this->t('The Bootstrap carousel displays content as a slideshow (<a href=":docs">see documentation</a>).', [':docs' => 'https://www.drupal.org/docs/contributed-modules/views-bootstrap-for-bootstrap-3/carousel']),
      '#weight' => -99,
    ];

    $fields = ['' => $this->t('<None>')];
    $fields += $this->displayHandler->getFieldLabels(TRUE);

    $form['interval'] = [
      '#type' => 'number',
      '#title' => $this->t('Interval'),
      '#description' => $this->t('The amount of time to delay between automatically cycling an item. If false, carousel will not automatically cycle.'),
      '#default_value' => $this->options['interval'],
    ];

    $form['navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show navigation'),
      '#default_value' => $this->options['navigation'],
    ];

    $form['indicators'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show indicators'),
      '#default_value' => $this->options['indicators'],
    ];

    $form['pause'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Pause on hover'),
      '#description' => $this->t('Pauses the cycling of the carousel on mouseenter and resumes the cycling of the carousel on mouseleave.'),
      '#default_value' => $this->options['pause'],
    ];

    $form['wrap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wrap'),
      '#description' => $this->t('The carousel should cycle continuously or have hard stops.'),
      '#default_value' => $this->options['wrap'],
    ];

    $form['columns'] = [
      '#type' => 'select',
      '#title' => $this->t('Columns'),
      '#description' => $this->t('The number of columns to include in the carousel.'),
      '#options' => [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
      ],
      '#default_value' => $this->options['columns'],
    ];

    $form['breakpoints'] = [
      '#type' => 'select',
      '#title' => $this->t('Breakpoints'),
      '#description' => $this->t('The min-width breakpoint of the multicolumn carousel.'),
      '#options' => [
        'xs' => $this->t('Extra Small'),
        'sm' => $this->t('Small'),
        'md' => $this->t('Medium'),
        'lg' => $this->t('Large'),
      ],
      '#default_value' => $this->options['breakpoints'],
    ];

    if ($this->usesFields()) {
      $form['display'] = [
        '#type' => 'radios',
        '#title' => $this->t('Display'),
        '#options' => [
          'fields' => $this->t('Select by fields'),
          'content' => $this->t('Display fields as row content'),
        ],
        '#description' => $this->t('Displaying fields as row content will output the field rows as unformatted values within each carousel item.'),
        '#default_value' => $this->options['display'],
      ];
      $form['image'] = [
        '#type' => 'select',
        '#title' => $this->t('Image'),
        '#options' => $fields,
        '#default_value' => $this->options['image'],
        '#states' => [
          'visible' => [
            ':input[name="style_options[display]"]' => ['value' => 'fields'],
          ],
        ],
      ];

      $form['title'] = [
        '#type' => 'select',
        '#title' => $this->t('Title'),
        '#options' => $fields,
        '#default_value' => $this->options['title'],
        '#states' => [
          'visible' => [
            ':input[name="style_options[display]"]' => ['value' => 'fields'],
          ],
        ],
      ];

      $form['description'] = [
        '#type' => 'select',
        '#title' => $this->t('Description'),
        '#options' => $fields,
        '#default_value' => $this->options['description'],
        '#states' => [
          'visible' => [
            ':input[name="style_options[display]"]' => ['value' => 'fields'],
          ],
        ],
      ];
    }

  }

}
