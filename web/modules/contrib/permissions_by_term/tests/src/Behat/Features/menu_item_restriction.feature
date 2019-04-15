@api @drupal
Feature: Menu Item Restriction

  Background:
    Given restricted "tags" terms:
      | name          | access_user   | access_role                             |
      | Tag one       |               | administrator                           |
      | Tag anonymous |               | anonymous, administrator, authenticated |
    Given article content:
      | title                          | author     | status | created           | field_tags    | alias                 |
      | Only admin can access          | Admin      | 1      | 2014-10-17 8:00am | Tag one       | only-admin-can-access |
      | Anonymous user can access      | Admin      | 1      | 2014-10-17 8:00am | Tag anonymous | anonymous             |
    Given users:
      | name          | mail            | pass     |
      | Joe           | joe@example.com | password |

  Scenario: Anonymous users see menu item with disabled node access records
    Given node access records are disabled
    And Node access records are rebuild
    And I create main menu item for node with title "Only admin can access"
    And I am on "/"
    Then I should see menu item text matching "Only admin can access"

  Scenario: Anonymous users do not see menu item with enabled node access records
    Given node access records are enabled
    And Node access records are rebuild
    And I create main menu item for node with title "Only admin can access"
    And I am on "/"
    Then I should not see menu item text matching "Only admin can access"

  Scenario: Admin users see menu item with enabled node access records
    Given node access records are enabled
    And Node access records are rebuild
    And I am logged in as a user with the "administrator" role
    And I create main menu item for node with title "Only admin can access"
    And I am on "/"
    Then I should see menu item text matching "Only admin can access"
