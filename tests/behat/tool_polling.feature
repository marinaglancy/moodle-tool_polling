@tool @tool_polling
Feature: Testing basic functionality of tool_polling
  In order to configure the site
  As an admin
  I need to be able to modify settings of settings of tool_polling

  Scenario: Modifying settings of tool_polling
    Given I log in as "admin"
    And I navigate to "Appearance > AJAX and Javascript" in site administration
    And I should see "Enable polling for updates"
    And I should see "Alternative polling URL"
    And I press "Save changes"

  Scenario: Basic test of polling for updates
    Given the following config values are set as admin:
      | Enable polling for updates | 1 |
    And I log in as "admin"
    When I am on fixture page "/admin/tool/polling/tests/behat/fixtures/polling.php"
    And I should not see "Polling works"
    And I press "Test polling"
    And I wait "2" seconds
    And I should see "Polling works"
    And I log out
