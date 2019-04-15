@lightning @api @lightning_media
Feature: Image media assets
  A media asset representing a locally hosted image.

  @13eacffd
  Scenario: Cropping should be allowed when creating an image
    Given I am logged in as a user with the "create media" permission
    When I visit "/media/add/image"
    And I attach the file "test.jpg" to "Image"
    And I press the "Upload" button
    Then I should be able to crop the image

  @b23435a5
  Scenario: Uploading an image to be ignored by the media library
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/image"
    And I attach the file "test.jpg" to "Image"
    And I press the "Upload" button
    And I enter "Blorg" for "Name"
    And I uncheck the box "Show in media library"
    And I press "Save"
    And I visit "/entity-browser/modal/media_browser"
    Then I should see "There are no media items to display."
