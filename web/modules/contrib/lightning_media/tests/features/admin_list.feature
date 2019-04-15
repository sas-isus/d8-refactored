@lightning @api @lightning_media
Feature: Media content list page

  Scenario: Managing media
    Given I am logged in as a user with the "access media overview, delete any media" permissions
    And I have items in the media library
    When I visit the media library
    Then I should be able to filter media by publishing status
    And I should be able to filter media by type
    And I should be able to filter media by name
    And I should be able to filter media by language
    And I should be able to select and delete media
