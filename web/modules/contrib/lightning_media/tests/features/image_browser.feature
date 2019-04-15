@lightning @lightning_media @api @javascript
Feature: An entity browser for image fields

  @10a21ffe @with-module:test_10a21ffe
  Scenario: Uploading an image through the image browser
    Given I am logged in as a user with the media_creator role
    When I visit "/node/add/page"
    And I open the "Hero Image" image browser
    And I switch to the "Upload" Entity Browser tab
    And I attach the file "test.jpg" to "File"
    And I wait for AJAX to finish
    # Cropping should be enabled.
    And I wait 1 second
    Then I should be able to crop the image
    And I enter "Behold, a generic logo" for "Name"
    And I submit the entity browser
    Then I should not see a "table[drupal-data-selector='edit-image-current'] td.empty" element
