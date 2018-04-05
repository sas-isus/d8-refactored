@api @drupal
Feature: Access
  Several automated tests for the Permissions by Term Drupal 8 module.

  Background:
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
    Given users:
      | name          | mail            | pass     |
      | Joe           | joe@example.com | password |
    Given Node access records are rebuild.

  Scenario: Anonymous users cannot see restricted node
    Given I open node view by node title "Authenticated user can access"
    Then I should see text matching "Access denied"

  Scenario: Anonymous users can see allowed node with term with multiple user role relation in view
    Given I am on "/"
    And the cache has been cleared
    Then I should see text matching "Anonymous user can access"

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags.
#  Scenario: Anonymous users can see node, which is not connected to any permission.
#    Given I am logged in as a user with the "administrator" role
#    Then I am on "/admin/config/search/pages"
#    And I press "edit-wipe"
#    And I run cron
#    And I am on "/user/logout"
#    Then I am on "node/search"
#    And I fill in "edit-keys" with "Everybody can"
#    And I press "edit-submit--2"
#    Then I should not see text matching "Your search yielded no results."

  Scenario: Anonymous users do not see unpublished nodes.
    Given I am on "node/search"
    And I fill in "edit-keys" with "Unpublished node"
    And I press "edit-submit--2"
    Then I should see text matching "Your search yielded no results."

#  Scenario: Anonymous users see nodes with term and no restriction
#    Given I run cron
#    Given I am on "/"
#    Given I am on "node/search"
#    And I fill in "edit-keys" with "Term accessible"
#    And I press "edit-submit--2"
#    Then I should not see text matching "Your search yielded no results."

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags.
#  Scenario: Users cannot access restricted terms in taxonomy term field widgets.
#    Given I am on "/"
#    Then I am logged in as a user with the "administrator" role
#    And I am on "/admin/structure/types/manage/article/form-display"
#    And I select index 3 in dropdown named "fields[field_tags][type]"
#    And I scroll to element with id "edit-submit"
#    And I press "edit-submit"
#    Then I am on "/node/add/article"
#    Then I should not see "Tag admin" in the "#edit-field-tags" element
#    Then I should see "Tag three" in the "#edit-field-tags" element

  Scenario: Users cannot access node edit forms without having access to related terms.
    Given I am on "/"
    Then I am logged in as a user with the "administrator" role
    And I am on "/admin/people/roles/add"
    And I fill in "edit-label" with "Editor"
    And I fill in "edit-id" with "editor"
    Then I press "edit-submit"
    Then I am on "/admin/people/permissions"
    And I check checkbox with id "edit-editor-access-content-overview" by JavaScript
    And I check checkbox with id "edit-editor-administer-nodes" by JavaScript
    And I check checkbox with id "edit-editor-administer-content-types" by JavaScript
    And I check checkbox with id "edit-editor-create-article-content" by JavaScript
    And I check checkbox with id "edit-editor-delete-any-article-content" by JavaScript
    And I check checkbox with id "edit-editor-delete-own-article-content" by JavaScript
    And I check checkbox with id "edit-editor-edit-any-article-content" by JavaScript
    And I check checkbox with id "edit-editor-edit-own-article-content" by JavaScript
    And I scroll to element with id "edit-submit"
    And I press "edit-submit"
    Then I am on "/admin/people/create"
    And I fill in "edit-name" with "editor"
    And I fill in "edit-pass-pass1" with "password"
    And I fill in "edit-pass-pass2" with "password"
    And I check checkbox with id "edit-roles-editor" by JavaScript
    And I scroll to element with id "edit-submit"
    And I press "edit-submit"
    Then I am on "/user/logout"
    Then I am on "/user/login"
    And I fill in "edit-name" with "editor"
    And I fill in "edit-pass" with "password"
    And I scroll to element with id "edit-submit"
    And I press "edit-submit"
    Then I am on "/admin/content"
    And I should not see text matching "Only admin user can edit"
    And I should see text matching "Term accessible"
    And I should see text matching "Everybody can access"
    Then I open node edit form by node title "Only admin user can edit"
    And I should see text matching "Access denied"

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags.
#  Scenario: Users are accessing an node directly
#    Given I am logged in as a user with the "administrator" role
#    Then I am on "only-admin-can-access"
#    Then I should see text matching "Only admin can access"
#    Given I am logged in as "Joe"
#    Then I am on "only-admin-can-access"
#    Then I should see text matching "Access denied"

#@TODO: Test needs to be fixed. Implement a method, which waits for HTML tags.
#  Scenario: Users search for an node
#    Given I create 1000 nodes of type "article"
#    Given I am logged in as a user with the "administrator" role
#    And I am on "admin/config/system/cron"
#    And I press "edit-run"
#    Then I am on "node/search"
#    And I fill in "edit-keys" with "Only admin can access"
#    And I press "edit-submit"
#    Then I should see text matching "Only admin can access"
#    Given I am logged in as "Joe"
#    Then I am on "node/search"
#    And I fill in "edit-keys" with "Only admin can access"
#    And I press "edit-submit"
#    Then I should see text matching "Your search yielded no results."

  Scenario: Users access an node by menu
    Given I am logged in as a user with the "administrator" role
    And I am on "admin/structure/menu/manage/main"
    And I click "Add link"
    And I fill in "edit-title-0-value" with "Only admin can access"
    And I fill in "edit-link-0-uri" with "Only admin can access (1)"
    And I press "edit-submit"
    Then I click "Home"
    Then I should see "Only admin can access" in the ".region-primary-menu" element
    Given I am logged in as "Joe"
    Then I should not see "Only admin can access" in the ".region-primary-menu" element

  Scenario: Users access an nodes by view
    Given I am logged in as a user with the "administrator" role
    Then I am on "/"
    And I should see text matching "Only admin can access"
    Given I am logged in as "Joe"
    Then I am on "/"
    And I should not see text matching "Only admin can access"
