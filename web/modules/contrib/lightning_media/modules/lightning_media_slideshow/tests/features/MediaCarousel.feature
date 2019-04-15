@lightning @lightning_media @lightning_media_slideshow @api
Feature: Media slideshow blocks

  @6189e839 @javascript @orca_public
  Scenario: Creating a media slideshow block
    Given I am logged in as a user with the "access content, access media_browser entity browser pages, access media overview, view media, create media, update media, administer blocks" permissions
    And I have items in the media library
    When I visit "/block/add/media_slideshow"
    And I enter "Test Block" for "Block description"
    And I select 2 items from the media_browser entity browser
    And I press "Save"
    And I select "Content" from "Region"
    And I press "Save block"
    And I go to the homepage
    And I should see a slideshow of media assets
