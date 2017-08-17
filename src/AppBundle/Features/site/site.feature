@site
Feature: Manage sites via the RESTful API
  Client should have a possibility to CRUD sites that he owns.
  In order to CRUD sites
  As an client
  I need to be able to CRUD sites

  Background:
    Given there are following users
      | id | username | email              | password | role        | enabled |
      | 1  | client   | client@example.com | testpass | ROLE_CLIENT | 1       |
    Given there are following sites
      | id | active | name                 | seo_strategy_keyword_page | deleted | acl(user_id,bitmask) |
      | 1  | 1      | https://somesite.com | 0                         | 0       | 1,7                  |
      | 2  | 1      | https://somesit2.com | 0                         | 0       |                      |


  # cRud ---------------------------------------------------------------------------------------------------------------
  Scenario: Client can list sites that he owns
    Given I am logged in as "client"
    When I request "GET api/v1/site"
    Then the response status code should be 200
    And the "Content-Type" header should be "application/json; charset=utf-8"
    And the "result" property should be an array
    And the "result.totalRecords" property should equal "1"
    # Site with id=1 available for user
    And the "result.sites.0.name" property should equal "https://somesite.com"
    # Site with id=2 NOT available for user
    And the "result.sites.1" property should not exist

  # crUd ---------------------------------------------------------------------------------------------------------------
  Scenario: Client can update site that he owns
    Given I am logged in as "client"
    Given I have the payload:
      """
      {
        "name": "https://123.com"
      }
      """
    When I request "PUT api/v1/site/1"
    Then the response status code should be 200
    And the "result.name" property should equal "https://123.com"

  Scenario: Client can not update site that he not owns
    Given I am logged in as "client"
    Given I have the payload:
      """
      {
        "name": "https://123.com"
      }
      """
    When I request "PUT api/v1/site/2"
    Then the response status code should be 403