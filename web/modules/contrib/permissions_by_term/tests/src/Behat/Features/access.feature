@api @drupal
Feature: Access
  Several automated tests for the Permissions by Term Drupal 8 module.

  Background:
    Given editor role exists
    Given restricted "tags" terms:
      | name          | access_user   | access_role                             |
      | Tag one       |               | administrator                           |
      | Tag two       |               | authenticated                           |
      | Tag three     |               |                                         |
      | Tag admin     | admin         |                                         |
      | Tag anonymous |               | anonymous, administrator, authenticated |
    Given article content:
      | title                          | author     | status | created           | field_tags    | alias                 |
      | Only admin can access          | Admin      | 1      | 2014-10-17 8:00am | Tag one       | only-admin-can-access |
      | Everybody can access           | Admin      | 1      | 2014-10-17 8:00am |               | no-term               |
      | Term accessible                | Admin      | 1      | 2014-10-17 8:00am | Tag three     | term-no-restriction   |
      | Unpublished node               | Admin      | 0      | 2014-10-17 8:00am |               | unpublished           |
      | Only admin user can edit       | Admin      | 0      | 2014-10-17 8:00am | Tag admin     | unpublished           |
      | Authenticated user can access  | Admin      | 0      | 2014-10-17 8:00am | Tag two       | unpublished           |
      | Anonymous user can access      | Admin      | 1      | 2014-10-17 8:00am | Tag anonymous | anonymous             |
      | Node with tag without perm     | Admin      | 1      | 2014-10-17 8:00am | Tag three     | anonymous             |
    Given users:
      | name          | mail            | pass     |
      | Joe           | joe@example.com | password |
    Given node access records are enabled

  Scenario: Anonymous users cannot see restricted node
    Given I open node view by node title "Authenticated user can access"
    Then I should see text matching "Access denied"

  Scenario: Anonymous users can see allowed node with term with multiple user role relation in view
    Given I am on "/"
    And the cache has been cleared
    Then I should see text matching "Anonymous user can access"

  Scenario: Users access nodes by view
    Given I am logged in as a user with the "administrator" role
    Then I am on "/"
    And I should see text matching "Only admin can access"
    Given I am logged in as "Joe"
    Then I am on "/"
    And I should not see text matching "Only admin can access"

  Scenario: Users cannot visit node, after term with permission is being added
    Given I am logged in as a user with the "administrator" role
    Then I open node view by node title "Node with tag without perm"
    And I click "Tag three"
    Then I click "Edit"
    And I click by selector "#fieldset_term_access summary" via JavaScript
    And I check checkbox with id "edit-access-role-administrator"
    Then I click by selector "#edit-submit" via JavaScript
    And I am on "/user/logout"
    Then I am on "/"
    And I should not see the text "Node with tag without perm"

  Scenario: I do not see fixed error when I want to edit an node
    Given I am logged in as a user with the "administrator" role
    Then I open node view by node title "Node with tag without perm"
    And I click "Edit"
    Then I should not see the text "The website encountered an unexpected error. Please try again later."

  Scenario: Editor cannot access disallowed node edit form
    Given I am logged in as a user with the "editor" role
    And I open node view by node title "Only admin can access"
    And I should see text matching "Access denied"
    Then I open node edit form by node title "Only admin can access"
    And I should see text matching "Access denied"

  Scenario: I do want to be able to re-save an node
    Given I am logged in as a user with the "administrator" role
    Then I open node view by node title "Only admin can access"
    And I click "Edit"
    And I fill in "field_tags[target_id]" with ""
    And I scroll to element with id "edit-footer"
    Then I click by selector "#edit-submit" via JavaScript
    And I should not see the text "The website encountered an unexpected error. Please try again later."
