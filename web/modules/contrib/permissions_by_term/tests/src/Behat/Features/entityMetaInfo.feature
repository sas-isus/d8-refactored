@api @drupal
Feature: Entity Meta Info
  Several automated tests for the Permissions by Term Drupal 8 module.

  Background:
    Given users:
      | name          | mail               | pass     |
      | Joe           | joe@example.com    | password |
      | George        | george@example.com | password |
      | Andy          | andy@example.com   | password |
      | Tom           | tom@example.com    | password |
    Given restricted "tags" terms:
      | name          | access_user   | access_role                             |
      | Tag one       |               | administrator                           |
      | Tag two       |               | authenticated                           |
      | Tag three     |               |                                         |
      | Tag admin     | admin         |                                         |
      | Tag anonymous |               | anonymous, administrator, authenticated |
    Given I create vocabulary with name "tags2" and vid "tags2"
    Given I create vocabulary with name "tags3" and vid "tags3"
    Given restricted "tags2" terms:
      | name              | access_user | access_role                           |
      | Tag administrator | Joe, George | administrator                         |
      | Tag authenticated | Andy        | authenticated                         |
    Given restricted "tags3" terms:
      | name          | access_user   | access_role                             |
      | Tag tom       | Tom           | administrator                           |
    Given article content:
      | title                          | author     | status | created           | field_tags    | alias                 |
      | Only admin can access          | Admin      | 1      | 2014-10-17 8:00am | Tag one       | only-admin-can-access |
      | Everybody can access           | Admin      | 1      | 2014-10-17 8:00am |               | no-term               |
      | Term accessible                | Admin      | 1      | 2014-10-17 8:00am | Tag three     | term-no-restriction   |
      | Unpublished node               | Admin      | 0      | 2014-10-17 8:00am |               | unpublished           |
      | Only admin user can edit       | Admin      | 0      | 2014-10-17 8:00am | Tag admin     | unpublished           |
      | Authenticated user can access  | Admin      | 0      | 2014-10-17 8:00am | Tag two       | unpublished           |
      | Anonymous user can access      | Admin      | 1      | 2014-10-17 8:00am | Tag anonymous | anonymous             |
    Given Node access records are rebuild.

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags. Field ids has changed. Do not rely on fixed id's with numeric suffix.
#  Scenario: Users see permissions tab in node edit form
#    Given I am on "/"
#    Given I am logged in as a user with the "administrator" role
#    Then I am on "/admin/structure/types/manage/article/fields/add-field"
#    And I select index 15 in dropdown named "new_storage_type"
#    And I press "edit-submit"
#    Then I fill in "edit-label" with "Tags2"
#    Then I fill in "edit-field-name" with "tags2"
#    And I press "edit-submit"
#    And I select index 1 in dropdown named "cardinality"
#    And I press "edit-submit"
#    Then I scroll to element with id "edit-submit"
#    And I check checkbox with id "edit-settings-handler-settings-target-bundles-tags2" by JavaScript
#    And I press "edit-submit"
#    Then I am on "/admin/structure/types/manage/article/fields/add-field"
#    And I select index 15 in dropdown named "new_storage_type"
#    And I press "edit-submit"
#    Then I fill in "edit-label" with "Tags3"
#    Then I fill in "edit-field-name" with "tags3"
#    And I press "edit-submit"
#    And I select index 1 in dropdown named "cardinality"
#    And I press "edit-submit"
#    And I check checkbox with id "edit-settings-handler-settings-target-bundles-tags3" by JavaScript
#    And I scroll to element with id "edit-submit"
#    And I press "edit-submit"
#    Then I am on "/admin/structure/types/manage/article/form-display"
#    And I scroll to element with id "edit-submit"
#    And I select index 0 in dropdown named "fields[field_tags2][type]"
#    And I select index 3 in dropdown named "fields[field_tags3][type]"
#    And I press "edit-submit"
#    Then I am on "/admin/content"
#    And I scroll to element with id "edit-submit"
#    And I click "Only admin can access"
#    And I click "Edit"
#    Then I should see "Permissions by Term" in the "#edit-advanced" element
#    And I open open Permissions By Term advanced info
#    And I should see "Administrator" in the "#edit-permissions-by-term-info" element
#    And I should see "Allowed Users: No user restrictions." in the "#edit-permissions-by-term-info" element
#    And I scroll to element with id "edit-field-tags2"
#    And I check checkbox with id "edit-field-tags2"
#    And I uncheck checkbox with id "edit-field-tags2"
#    And I scroll to element with id "edit-permissions-by-term-info"
#    Then I should see "Allowed roles: Administrator" in the "#edit-permissions-by-term-info" element
#    Then I fill in "edit-field-tags-target-id" with "Tag one (5), Tag two (6)"
#    And I should see "Allowed users: Joe, George" in the "#edit-permissions-by-term-info" element
#    Then I select "Tag tom" in "edit-field-tags3"
#    And I should see "Allowed users: Joe, George, Tom" in the "#edit-permissions-by-term-info" element

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags.
#  Scenario: Users see permissions tab in node add form
#    Given I am on "/"
#    Given I am logged in as a user with the "administrator" role
#    Then I am on "/node/add/article"
#    And I open open Permissions By Term advanced info
#    And I should see "Allowed users: No user restrictions." in the "#edit-permissions-by-term-info" element
#    And I should see "Allowed roles: No role restrictions." in the "#edit-permissions-by-term-info" element
#    Then I select "Tag tom" in "edit-field-tags3"
#    And I should see "Allowed users: Tom" in the "#edit-permissions-by-term-info" element
#    Then I check checkbox with id "edit-field-tags2-14"
#    And I should see "Allowed users: Tom, Joe, George" in the "#edit-permissions-by-term-info" element
#    And I should see "Allowed roles: Administrator" in the "#edit-permissions-by-term-info" element
#    Then I uncheck checkbox with id "edit-field-tags2-14"
#    And I should see "Allowed users: Tom" in the "#edit-permissions-by-term-info" element
