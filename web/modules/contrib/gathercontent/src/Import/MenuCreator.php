<?php

namespace Drupal\gathercontent\Import;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;

/**
 * Class for creating menus after import.
 */
class MenuCreator {

  /**
   * Create a menu link to the imported node.
   */
  public static function createMenu(NodeInterface $entity, $parentMenuItem) {
    $isContentTypeTranslatable = Importer::isContentTypeTranslatable($entity->bundle());
    $isMenuTranslatable = static::isMenuTranslatable();
    $menuLinkDefaults = menu_ui_get_menu_link_defaults($entity);
    if (!(bool) $menuLinkDefaults['id']) {
      if ($isContentTypeTranslatable && $isMenuTranslatable) {
        $languages = $entity->getTranslationLanguages();
        $originalLinkId = NULL;
        foreach ($languages as $langcode => $language) {
          $localized_entity = $entity->hasTranslation($langcode) ? $entity->getTranslation($langcode) : NULL;
          if (!is_null($localized_entity)) {
            static::createMenuLink($entity->id(), $localized_entity->getTitle(), $parentMenuItem, $langcode, $originalLinkId);
          }
        }
      }
      else {
        static::createMenuLink($entity->id(), $entity->getTitle(), $parentMenuItem);
      }
    }
  }

  /**
   * Create menu link if requested.
   *
   * @param int $nid
   *   ID of \Drupal\node\NodeInterface object.
   * @param string $title
   *   Title for menu link.
   * @param string $plid
   *   Parent menu link ID, null if we don't want to create menu link.
   * @param null|string $lang
   *   Langcode for menu link.
   * @param null|int $original_link_id
   *   ID of menu link item in default language.
   */
  protected static function createMenuLink($nid, $title, $plid, $lang = NULL, &$original_link_id = NULL) {
    $weight = 1;
    if (!empty($plid)) {
      if (is_null($lang)) {
        // Single language node.
        list($menu_name, $mlid) = explode(':', $plid);
        // Get parent menu link ID.
        if ($menu_name === 'node') {
          static::getMenuByGcId($mlid, $menu_name);
        }
        $link = [
          'link' => ['uri' => 'entity:node/' . $nid],
          'title' => $title,
          'menu_name' => $menu_name,
          'parent' => $mlid,
        ];
        MenuLinkContent::create($link)->set('weight', $weight)->save();
      }
      elseif (static::isMenuTranslatable()) {
        if (!is_null($lang) && is_null($original_link_id)) {
          // Multi language node - first language.
          list($menu_name, $mlid) = explode(':', $plid);
          // Get parent menu link ID.
          if ($menu_name === 'node') {
            static::getMenuByGcId($mlid, $menu_name, $lang);
          }
          $link = [
            'link' => ['uri' => 'entity:node/' . $nid],
            'title' => $title,
            'menu_name' => $menu_name,
            'parent' => $mlid,
            'langcode' => $lang,
          ];
          $menu_link = MenuLinkContent::create($link);
          $menu_link->set('weight', $weight);
          $menu_link->save();

          $original_link_id = $menu_link->id();
        }
        elseif (!is_null($lang) && !is_null($original_link_id)) {
          // Multi language node - other language.
          list($menu_name, $mlid) = explode(':', $plid);
          if ($menu_name === 'node') {
            static::getMenuByGcId($mlid, $menu_name, $lang);
          }
          $link = [
            'link' => ['uri' => 'entity:node/' . $nid],
            'title' => $title,
            'menu_name' => $menu_name,
            'parent' => $mlid,
            'langcode' => $lang,
          ];

          // Load parent item.
          $original_item = MenuLinkContent::load($original_link_id);
          $original_item->addTranslation($lang, $link);
          $original_item->save();
        }
      }
    }
  }

  /**
   * Load menu name and menu link id for other languages by node ID.
   *
   * @param int $mlid
   *   Menu link ID.
   * @param string $menu_name
   *   Name of the menu.
   * @param string|null $language
   *   Langcode if menu link item will be translatable.
   */
  protected static function getMenuByGcId(&$mlid, &$menu_name, $language = NULL) {
    // Load node by gc_id.
    $node_ids = \Drupal::entityQuery('node')
      ->condition('gc_id', $mlid)
      ->execute();
    if (!empty($node_ids)) {
      // Load menu_link by node_id.
      $node = reset($node_ids);
      $ml_result = \Drupal::entityQuery('menu_link_content')
        ->condition('link.uri', 'entity:node/' . $node);
      if (!is_null($language)) {
        $ml_result->condition('langcode', $language);
      }
      $mls = $ml_result->execute();
      if (!empty($mls)) {
        $ml = reset($mls);
        $ml_object = MenuLinkContent::load($ml);
        $menu_name = $ml_object->getMenuName();
        $mlid = 'menu_link_content:' . $ml_object->uuid();
      }
    }
  }

  /**
   * Decide whether a menu is translatable.
   */
  public static function isMenuTranslatable() {
    return \Drupal::moduleHandler()
      ->moduleExists('content_translation')
      && \Drupal::service('content_translation.manager')
        ->isEnabled('menu_link_content');
  }

}
