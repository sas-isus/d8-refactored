<?php

namespace Acquia\LightningExtension\Context;

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Webmozart\Assert\Assert;

/**
 * Contains step definitions for interacting with entity browser instances.
 */
final class EntityBrowserContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * Indicates if the current scenario uses JavaScript.
   *
   * @var bool
   */
  private $isJS;

  /**
   * Performs pre-scenario tasks.
   *
   * @BeforeScenario
   */
  public function setUp(ScenarioScope $scope) {
    // Check if the feature or scenario has the 'javascript' tag.
    $tags = array_merge(
      $scope->getScenario()->getTags(),
      $scope->getFeature()->getTags()
    );
    $this->isJS = in_array('javascript', $tags, TRUE);
  }

  /**
   * Gets all items in an entity browser.
   *
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   An array of items in the entity browser.
   */
  private function getItems($browser_id = NULL) {
    if ($browser_id) {
      $selector = 'form#entity-browser-' . Html::cleanCssIdentifier($browser_id) . '-form';
    }
    else {
      $selector = 'form[data-entity-browser-uuid]';
    }

    return $this
      ->assertSession()
      ->elementExists('css', $selector)
      ->findAll('css', '[data-selectable]');
  }

  /**
   * Selects an item in an entity browser view.
   *
   * @param int $n
   *   The one-based index of the item to select.
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @throws \Behat\Mink\Exception\ExpectationException if the entity browser
   * contains fewer than $n items.
   *
   * @When I select item :n
   * @When I select item :n from the entity browser
   * @When I select item :n from the :browser_id entity browser
   */
  public function select($n, $browser_id = NULL) {
    $items = $this->getItems($browser_id);

    if ($n > count($items)) {
      throw new ExpectationException("Expected at least $n item(s) in the $browser_id entity browser.", $this->getSession()->getDriver());
    }
    else {
      $items[--$n]->click();
    }
  }

  /**
   * Asserts that a certain number of items are visible in the entity browser.
   *
   * @param int $n
   *   The number of items that should be visible.
   * @param string $browser_id
   *   (optional) The entity browser ID.
   *
   * @throws ExpectationException if the actual number of items in the entity
   * browser does not match the expected number.
   *
   * @Then I should see :n item(s) in the entity browser
   */
  public function assertCount($n, $browser_id = NULL) {
    $count = count($this->getItems($browser_id));

    if ($count !== (int) $n) {
      throw new ExpectationException("Expected $n items in the $browser_id entity browser, but there were $count.", $this->getSession()->getDriver());
    }
  }

  /**
   * Clicks on a tab with the specified text in an active entity browser and
   * waits for it to load.
   *
   * @param string $tab
   *   The text of the tab to switch to.
   *
   * @When I switch to the :tab Entity Browser tab
   */
  public function switchToEBTab($tab) {
    $this
      ->assertSession()
      ->elementExists('css', 'nav.eb-tabs')
      ->clickLink($tab);

    // I don't see any way to assert the tab specifically has loaded. So,
    // instead we just wait a reasonable amount of time.
    sleep(5);
  }

  /**
   * Submits the entity browser.
   *
   * @When I submit the entity browser
   */
  public function submit() {
    $session = $this->getSession();

    $button = $this->assertSession()
      ->elementExists('css', '[data-drupal-selector="edit-submit"]')
      ->getXpath();

    $frame = $session->evaluateScript('window.name') ?: $session->evaluateScript('window.active_iframe.name');
    assert(! empty($frame));

    // Switch out of the iFrame, because it will be destroyed as soon as we
    // press the button.
    $session->switchToIFrame();

    // If there are single quotes in the button's XPath query, WebDriver needs
    // them to be escaped.
    if ($session->getDriver() instanceof Selenium2Driver) {
      $button = addslashes($button);
    }
    $js = <<<END
document.evaluate('$button', window.$frame.document, null).iterateNext().click();
END;
    $session->executeScript($js);
    $this->awaitAjax();
  }

  /**
   * Opens an entity browser.
   *
   * @param string $id
   *   The entity browser ID.
   */
  public function open($id) {
    $this->isJS ? $this->openJS($id) : $this->openNoJS($id);
  }

  /**
   * Opens an entity browser using JavaScript.
   *
   * @param string $id
   *   The entity browser ID.
   */
  private function openJS($id) {
    $settings = $this->getEntityBrowserSettings($id);

    $this->assertSession()
      ->elementExists('css', '.entity-browser-handle[data-uuid="' . $settings['uuid'] . '"]')
      ->click();

    $frame = "window.entity_browser_iframe_$id";
    $this->awaitExpression($frame);
    $this->awaitExpression("$frame.document.readyState === 'complete'");
  }

  /**
   * Opens an entity browser without using JavaScript.
   *
   * @param string $id
   *   The entity browser ID.
   */
  private function openNoJS($id) {
    $settings = $this->getEntityBrowserSettings($id);
    Assert::notEmpty($settings['src']);
    $this->visitPath($settings['src']);
  }

  /**
   * Returns settings for a single entity browser.
   *
   * @param string $id
   *   The entity browser ID (not UUID).
   *
   * @return array
   *   The settings for the entity browser.
   *
   * @throws \Exception
   *   If there is not exactly one entity browser with the given ID.
   */
  private function getEntityBrowserSettings($id) {
    $filter = function (array $settings) use ($id) {
      return $settings['entity_browser_id'] === $id;
    };

    $settings = array_filter($this->getAllEntityBrowserSettings(), $filter);
    Assert::count($settings, 1);

    return reset($settings);
  }

  /**
   * Returns settings for all entity browser instances on the page.
   *
   * @return array[]
   *   The settings for all entity browser instances, keyed by UUID.
   */
  private function getAllEntityBrowserSettings() {
    $settings = $this->getAllSettings();

    Assert::isArray($settings['entity_browser']);
    Assert::notEmpty($settings['entity_browser']);

    $display_types = \Drupal::service('plugin.manager.entity_browser.display')
      ->getDefinitions();

    $settings = array_intersect_key($settings['entity_browser'], $display_types);

    $all = [];
    foreach ($settings as $display_type => $instances) {
      foreach ($instances as $uuid => $instance) {
        $instance['display_type'] = $display_type;
        $instance['uuid'] = $uuid;
        $all[$uuid] = $instance;
      }
    }
    return $all;
  }

  /**
   * Returns all Drupal JavaScript settings on the page.
   *
   * @return mixed[]
   *   The decoded settings.
   */
  private function getAllSettings() {
    $settings = $this->assertSession()
      ->elementExists('css', 'script[type="application/json"][data-drupal-selector="drupal-settings-json"]')
      ->getText();

    return Json::decode($settings);
  }

  /**
   * Selects several items from an entity browser.
   *
   * @param int $count
   *   The number of items to select.
   * @param string $id
   *   The entity browser ID.
   *
   * @When I select :count item(s) from the :id entity browser
   */
  public function selectItems($count, $id) {
    $this->isJS ? $this->switchJS($id) : $this->switchNoJS($id);
    $this->switchToEBTab('Library');
    $this->awaitAjax();
    for ($n = 0; $n < $count; $n++) {
      $this->select($n + 1);
    }
    $this->submit();
    $this->awaitAjax();
  }

  /**
   * Switches to an entity browser using JavaScript.
   *
   * @param string $id
   *   The entity browser ID.
   */
  private function switchJS($id) {
    $this->assertSession()
      ->elementExists('named', ['button', 'Add media'])
      ->press();

    $iframe_id = $this
      ->awaitElement('iframe[name="entity_browser_iframe_' . $id . '"]', 30)
      ->getAttribute('id');

    $this->getSession()->switchToIFrame($iframe_id);
  }

  /**
   * Switches to an entity browser without using JavaScript.
   *
   * @param string $id
   *   The entity browser ID.
   */
  private function switchNoJS($id) {
    $settings = $this->getEntityBrowserSettings($id);
    Assert::notEmpty($settings['src']);
    $this->visitPath($settings['src']);
  }

}
