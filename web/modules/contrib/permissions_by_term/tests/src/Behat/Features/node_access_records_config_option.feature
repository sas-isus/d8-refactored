@api @drupal @nodeAccessRecordsConfigOption
Feature: Node access records config option

  Background:
    Given editor role exists
    Given restricted "tags" terms:
      | name          | access_user   | access_role                             |
      | Tag one       |               | administrator                           |
      | Tag two       |               | authenticated                           |
      | Tag three     |               |                                         |
      | Tag admin     | admin         |                                         |
      | Tag anonymous |               | anonymous, administrator, authenticated |
      | Tag editor    |               | editor                                  |
    Given article content:
      | title                          | author     | status | created           | field_tags    | alias                 |
      | Only admin can access          | Admin      | 1      | 2014-10-17 8:00am | Tag one       | only-admin-can-access |
      | No term relation               | Admin      | 1      | 2014-10-17 8:00am |               | no-term               |
      | Term accessible                | Admin      | 1      | 2014-10-17 8:00am | Tag three     | term-no-restriction   |
      | Unpublished node               | Admin      | 0      | 2014-10-17 8:00am |               | unpublished           |
      | Only admin user can edit       | Admin      | 0      | 2014-10-17 8:00am | Tag admin     | unpublished           |
      | Authenticated user can access  | Admin      | 0      | 2014-10-17 8:00am | Tag two       | unpublished           |
      | Anonymous user can access      | Admin      | 1      | 2014-10-17 8:00am | Tag anonymous | anonymous             |
      | Node with tag without perm     | Admin      | 1      | 2014-10-17 8:00am | Tag three     | anonymous             |
      | Editor can access              | Admin      | 1      | 2014-10-17 8:00am | Tag editor    | editor-can-access     |
    Given users:
      | name          | mail            | pass     |
      | Joe           | joe@example.com | password |
    Given Node access records are rebuild

  Scenario: As an admin user I want to be able to set the config option via backend
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/permissions-by-term/settings"
    Then I should see text matching "Disable node access records"
    And the "edit-disable-node-access-records" checkbox should be unchecked
    And I submit the form
    And I should not see text matching "The content access permissions have been rebuilt." after a while
    Then I check checkbox with id "edit-disable-node-access-records"
    And I submit the form
    And I should see text matching "The content access permissions have been rebuilt." after a while
    Then I am on "/admin/permissions-by-term/settings"
    Then I should see text matching "Disable node access records"
    And the "edit-disable-node-access-records" checkbox should be checked
    Then I uncheck checkbox with id "edit-disable-node-access-records"
    And I submit the form
    And I should see text matching "The content access permissions have been rebuilt." after a while

  Scenario: Content access permissions rebuild message appears after term save
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/taxonomy/manage/tags/add"
    And I fill in "name[0][value]" with "Some term name"
    And I click by selector "#fieldset_term_access > summary" via JavaScript
    Then I check checkbox with id "edit-access-role-administrator"
    And I submit the form
    And I should see text matching "The content access permissions have been rebuilt." after a while

  Scenario: Content access permissions rebuild message does not appear after term save
    Given node access records are disabled
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/taxonomy/manage/tags/add"
    And I fill in "name[0][value]" with "Some term name"
    And I click by selector "#fieldset_term_access > summary" via JavaScript
    Then I check checkbox with id "edit-access-role-administrator"
    And I submit the form
    Then I should not see text matching "Rebuilding"
    And I should not see text matching "The content access permissions have been rebuilt." after a while

  Scenario: Editor cannot access disallowed node edit form
    Given node access records are disabled
    And I am logged in as a user with the "editor" role
    And I open node view by node title "Only admin can access"
    And I should see text matching "Access denied"
    Then I open node edit form by node title "Only admin can access"
    And I should see text matching "Access denied"

  Scenario: Nodes in views are not restricted
    Given node access records are disabled
    And I am logged in as "Joe"
    Then I am on "/"
    And I should see text matching "Only admin can access"

