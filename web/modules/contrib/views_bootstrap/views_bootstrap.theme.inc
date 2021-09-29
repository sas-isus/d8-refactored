<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\views_bootstrap\ViewsBootstrap;
use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for views accordion templates.
 *
 * Default template: views-bootstrap-accordion.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_accordion(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $group_title_field = isset($view->style_plugin->options['grouping'][0]) ? $view->style_plugin->options['grouping'][0]['field'] : "";
  $panel_title_field = $view->style_plugin->options['panel_title_field'];
  $vars['behavior'] = $view->style_plugin->options['behavior'];
  $label_field = $view->style_plugin->options['label_field'] ?? '';
  $vars['attributes']['class'][] = 'panel-group';
  if ($panel_title_field) {
    foreach ($vars['rows'] as $id => $row) {
      $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
      $vars['rows'][$id] = [];
      $vars['rows'][$id]['content'] = $row;
      $vars['rows'][$id]['label'] = $label_field ? $view->style_plugin->getField($id, $label_field) : '';
      $vars['rows'][$id]['title'] = [
        '#markup' => Xss::filter($view->style_plugin->getField($id, $panel_title_field), [
          'img',
          'br',
          'h2',
          'h3',
          'h4',
          'h5',
          'h6',
          'span',
          'strong',
          'em',
          'i',
          'small',
        ]),
      ];
    }
  }
  else {
    // @TODO: This would be better as validation errors on the style plugin options form.
    Drupal::messenger()->addWarning(
      t('@style style will not display without the "@field" setting.', [
        '@style' => $view->style_plugin->definition['title'],
        '@field' => 'Panel title',
      ]
    ));
  }
  // @TODO: Make sure that $vars['rows'] is a rendered array.
  // @SEE: Have a look at template_preprocess_views_view_unformatted()
  // and views-view-unformatted.html.twig
}

/**
 * Prepares variables for views cards templates.
 *
 * Default template: views-bootstrap-cards.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_cards(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $wrapper_attributes = ['class' => ['card-group']];
  $classes = array_filter(explode(' ', $view->style_plugin->options['card_group_class_custom']));
  foreach ($classes as &$class) {
    $class = Html::cleanCssIdentifier($class);
  }
  if (!empty($classes)) {
    $wrapper_attributes['class'] = array_merge($wrapper_attributes['class'], $classes);
  }
  $vars['attributes'] = new Attribute($wrapper_attributes);

  // Card rows.
  $image = $view->style_plugin->options['card_image_field'];
  $title = $view->style_plugin->options['card_title_field'];
  $content = $view->style_plugin->options['card_content_field'];

  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['image'] = $view->style_plugin->getField($id, $image);
    $vars['rows'][$id]['title'] = $view->style_plugin->getField($id, $title);
    $vars['rows'][$id]['content'] = $view->style_plugin->getField($id, $content);
    $row_attributes = ['class' => ['card']];
    // Add custom card classes.
    $row_class = array_filter(explode(' ', $view->style_plugin->getCustomClass($id, 'card')));
    if (!empty($row_class)) {
      $row_attributes['class'] = array_merge($row_attributes['class'], $row_class);
    }
    $vars['rows'][$id]['attributes'] = new Attribute($row_attributes);
  }
}

/**
 * Prepares variables for views carousel template.
 *
 * Default template: views-bootstrap-carousel.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_carousel(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $vars['attributes']['class'][] = 'views-bootstrap-media-object';
  $vars['attributes']['class'][] = 'media-list';

  // Carousel options.
  $vars['interval'] = $view->style_plugin->options['interval'];
  $vars['navigation'] = $view->style_plugin->options['navigation'];
  $vars['indicators'] = $view->style_plugin->options['indicators'];
  $vars['pause'] = $view->style_plugin->options['pause'] ? 'hover' : FALSE;
  $vars['wrap'] = $view->style_plugin->options['wrap'];
  $vars['columns'] = $view->style_plugin->options['columns'];
  $vars['breakpoints'] = $view->style_plugin->options['breakpoints'];

  // Carousel rows.
  if ($view->style_plugin->options['display'] != 'content' && $view->style_plugin->usesFields()) {
    $image = $view->style_plugin->options['image'];
    $title = $view->style_plugin->options['title'];
    $description = $view->style_plugin->options['description'];
    $fieldLabels = $view->display_handler->getFieldLabels(TRUE);
    $vars['display'] = 'fields';
  }
  else {
    $vars['display'] = 'content';
  }

  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $row_attributes = ['class' => []];
    $class = $view->style_plugin->options['row_class'];
    if ($vars['display'] == 'fields') {
      $vars['rows'][$id]['image'] = $view->style_plugin->getField($id, $image);
      $vars['rows'][$id]['title'] = $view->style_plugin->getField($id, $title);
      $vars['rows'][$id]['description'] = $view->style_plugin->getField($id, $description);
      // Add any additional fields to result.
      foreach (array_keys($fieldLabels) as $label) {
        if (!in_array($label, [$image, $title, $description])) {
          $vars['rows'][$id][$label] = $view->style_plugin->getField($id, $label);
        }
      }
      $class = strip_tags($view->style_plugin->tokenizeValue($class, $id));
      $class = Html::cleanCssIdentifier($class);
    }
    else {
      $vars['rows'][$id]['content'] = $row;
    }
    $classes = explode(' ', $class);
    foreach ($classes as &$class) {
      $class = Html::cleanCssIdentifier($class);
    }
    $row_class = array_filter($classes);
    if (!empty($row_class)) {
      $row_attributes['class'] = array_merge($row_attributes['class'], $row_class);
    }
    $vars['rows'][$id]['attributes'] = new Attribute($row_attributes);
  }

}

/**
 * Prepares variables for views grid templates.
 *
 * Default template: views-bootstrap-grid.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_grid(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $vars['attributes']['class'][] = 'grid';
  $options = $view->style_plugin->options;
  $options['automatic_width'] = ['default' => FALSE];

  foreach (['xs', 'sm', 'md', 'lg'] as $size) {
    $vars["col_" . $size] = $options["col_" . $size];
    // Get the value from the size sting.
    $vars['sizes'][$size] = ViewsBootstrap::getColSize($options["col_" . $size]);
  }
  $vars['options'] = $options;

}

/**
 * Prepares variables for views list group templates.
 *
 * Default template: views-bootstrap-list-group.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_list_group(array &$vars) {
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $vars['attributes']['class'][] = 'views-bootstrap-list-group';
  $group_title_field = isset($view->style_plugin->options['grouping'][0]) ? $view->style_plugin->options['grouping'][0]['field'] : "";
  $vars['panels'] = isset($view->style_plugin->options['panels']) ? $view->style_plugin->options['panels'] : FALSE;
  foreach ($vars['rows'] as $id => $row) {
    $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['content'] = $row;
    $vars['rows'][$id]['title'] = $vars['view']->style_plugin->getField($id, $options['title_field']);
  }

}

/**
 * Prepares variables for views media object templates.
 *
 * Default template: views-bootstrap-media-object.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_media_object(array &$vars) {
  $vars['id'] = ViewsBootstrap::getUniqueId($vars['view']);
  $vars['image_class'] = $vars['view']->style_plugin->options['image_class'];
  $image_field = $vars['view']->style_plugin->options['image_field'];
  $heading_field = $vars['view']->style_plugin->options['heading_field'];
  $vars['attributes']['class'][] = 'media-object';

  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['image'] = $vars['view']->style_plugin->getField($id, $image_field);
    $vars['rows'][$id]['heading'] = $vars['view']->style_plugin->getField($id, $heading_field);
    if (count($row["#view"]->field) > 2) {
      $vars['rows'][$id]['body'] = $row;
    }
  }
}

/**
 * Prepares variables for views panel templates.
 *
 * Default template: views-bootstrap-panel.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_panel(array &$vars) {
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $group_title_field = isset($view->style_plugin->options['grouping'][0]) ? $view->style_plugin->options['grouping'][0]['field'] : "";
  $panel_title_field = $view->style_plugin->options['panel_title_field'];
  $panel_label_field = $view->style_plugin->options['panel_label_field'] ?? '';
  $panel_footer_field = $view->style_plugin->options['panel_footer_field'];
  $vars['attributes']['class'][] = 'panel-group';
  $vars['panel_class'] = $view->style_plugin->options['contextual_class'];
  foreach ($vars['rows'] as $id => $row) {
    $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['content'] = $row;
    $vars['rows'][$id]['title'] = $view->style_plugin->getField($id, $panel_title_field);
    $vars['rows'][$id]['label'] = $panel_label_field ? $view->style_plugin->getField($id, $panel_label_field) : '';
    $vars['rows'][$id]['footer'] = $view->style_plugin->getField($id, $panel_footer_field);
  }
  // @TODO: Make sure that $vars['rows'] is rendered array.
  // @SEE: Have a look template_preprocess_views_view_unformatted()
  // and views-view-unformatted.html.twig
}

/**
 * Prepares variables for views tab templates.
 *
 * Default template: views-bootstrap-tab.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_tab(array &$vars) {
  $vars['id'] = ViewsBootstrap::getUniqueId($vars['view']);
  $view = $vars['view'];
  $group_title_field = isset($view->style_plugin->options['grouping'][0]) ? $view->style_plugin->options['grouping'][0]['field'] : "";
  $tab_field = $view->style_plugin->options['tab_field'];
  $vars['tab_type'] = $view->style_plugin->options['tab_type'];
  $vars['tab_position'] = $view->style_plugin->options['tab_position'];
  $vars['tab_fade'] = $view->style_plugin->options['tab_fade'] ? 'fade' : '';

  // Get tabs.
  if ($tab_field) {
    if (isset($view->field[$tab_field])) {
      foreach (array_keys($vars['rows']) as $key) {
        $vars['tabs'][$key] = [
          '#markup' => Xss::filter($view->style_plugin->getField($key, $tab_field), [
            'img',
            'br',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'span',
            'strong',
            'em',
            'i',
            'small',
          ]),
        ];
      }
    }
    foreach ($vars['rows'] as $id => $row) {
      $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
      $vars['rows'][$id] = [];
      $vars['rows'][$id]['content'] = $row;
      $vars['rows'][$id]['attributes'] = new Attribute();
      if ($row_class = $view->style_plugin->getRowClass($id)) {
        $vars['rows'][$id]['attributes']->addClass($row_class);
      }
    }
  }
  else {
    // @TODO: This would be better as validation errors on the style plugin options form.
    Drupal::messenger()->addWarning(
      t('@style style will not display without the "@field" setting.', [
        '@style' => $view->style_plugin->definition['title'],
        '@field' => 'Tab title',
      ]
    ));
  }
}

/**
 * Prepares variables for views table templates.
 *
 * Default template: views-bootstrap-table.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_table(array &$vars) {
  $vars['responsive'] = $vars['view']->style_plugin->options['responsive'];
  $vars['attributes']['class'][] = 'table';
  foreach (array_filter($vars['view']->style_plugin->options['bootstrap_styles']) as $style) {
    $vars['attributes']['class'][] = 'table-' . $style;
  }
}

/**
 * Prepares variables for views dropdown templates.
 *
 * Adds 'dropdown' classes and some aria roles to the list structure.
 *
 * Default template: views-bootstrap-dropdown.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 *
 * @see template_preprocess_views_view_list()
 */
function template_preprocess_views_bootstrap_dropdown(array &$vars) {
  /** @var \Drupal\views\ViewExecutable $view */
  $view = $vars['view'];
  $vars['id'] = ViewsBootstrap::getUniqueId($view);

  /** @var  \Drupal\views\Plugin\views\style\StylePluginBase $handler */
  $handler = $vars['view']->style_plugin;

  // Fetch classes from handler options. Sanitize user input.
  $wrapper_class = explode(' ', $handler->options['wrapper_class']);
  $wrapper_class[] = 'dropdown';
  $wrapper_class = array_map('\Drupal\Component\Utility\Html::cleanCssIdentifier', $wrapper_class);
  $vars['attributes'] = new Attribute(['class' => $wrapper_class]);

  $class = explode(' ', $handler->options['class']);
  $class[] = "dropdown-menu";
  $class = array_map('\Drupal\Component\Utility\Html::cleanCssIdentifier', $class);
  $vars['list']['attributes'] = new Attribute(['class' => $class]);

  $vars['button']['text'] = $handler->options['button_text'];
  $button_class = explode(' ', $handler->options['button_class']);
  $button_class[] = 'dropdown-toggle';
  $button_class = array_map('\Drupal\Component\Utility\Html::cleanCssIdentifier', $button_class);
  $vars['button']['attributes'] = new Attribute(['class' => $button_class]);

  // Inject additional dropdown aria attributes into the individual rows to
  // make them behave as menu items.
  // The most common case should be one linked field, but there seems
  // no reason why the whole rendered row can't be here if that's what you want.
  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['content'] = $row;
    // Using role=presentation here is supposed to diminish the screen readers
    // treatment of list items as "List Items". Being a menu item is sufficient.
    // tabindex -1 means that all these links will not waylay keyboard
    // navigation (until the user deliberately opens that list).
    $vars['rows'][$id]['attributes'] = new Attribute(['role' => 'menuitem presentation', 'tabindex' => -1]);
    if ($row_class = $handler->getRowClass($id)) {
      $vars['rows'][$id]['attributes']->addClass($row_class);
    }
  }
}
