@api @drupal
Feature: Permission mode

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
    Given permission mode is set
    Given node access records are enabled
    Given Node access records are rebuild

  Scenario: Editor cannot access disallowed node edit form
    Given I am logged in as a user with the "editor" role
    And I open node view by node title "No term relation"
    And I should see text matching "Access denied"

  Scenario: Users access nodes by view
    Given I am logged in as a user with the "editor" role
    Then I am on "/"
    And I should not see text matching "No term relation"
    And I should see text matching "Editor can access"

  Scenario: Editor cannot access disallowed node edit form
    Given I am logged in as a user with the "editor" role
    And I open node view by node title "Only admin can access"
    And I should see text matching "Access denied"
    Then I open node edit form by node title "No term relation"
    And I should see text matching "Access denied"
    Then I open node edit form by node title "Editor can access"
    And I should not see text matching "Access denied"
