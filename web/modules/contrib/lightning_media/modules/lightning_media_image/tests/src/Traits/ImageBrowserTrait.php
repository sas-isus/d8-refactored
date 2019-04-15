<?php

namespace Drupal\Tests\lightning_media_image\Traits;

use Behat\Mink\Element\ElementInterface;

trait ImageBrowserTrait {

  /**
   * Opens a modal image browser.
   *
   * @param string $label
   *   The label of the image field.
   */
  protected function openImageBrowser($label) {
    $this->getWrapper($label)->pressButton('Select Image(s)');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_image_browser');
  }

  /**
   * Finds a details element by its summary text.
   *
   * @param string $label
   *   The summary.
   *
   * @return NodeElement
   *   The details element.
   */
  protected function getWrapper($label) {
    $elements = $this->getSession()
      ->getPage()
      ->findAll('css', 'details > summary');
    $lowercase_label = mb_strtolower($label);

    $filter =  function (ElementInterface $element) use ($lowercase_label) {
      $lowercase_text = mb_strtolower($element->getText());

      return $lowercase_text === $lowercase_label;
    };
    $wrappers = array_filter($elements, $filter);
    $this->assertNotEmpty($wrappers);

    return reset($wrappers)->getParent();
  }

}
