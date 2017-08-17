@site @siteschedule
Feature: Manage site schedules via the RESTful API
  Admin should have a possibility to list, update site schedules.
  In order to CRUD site schedules
  As an admin
  I need to be able to list, update site schedules

  Background:
    Given there are following users
      | id | username | email              | password | role        | enabled |
      | 1  | admin    | admin@example.com  | testpass | ROLE_ADMIN  | 1       |
      | 2  | client   | client@example.com | testpass | ROLE_CLIENT | 1       |
    Given there are following sites
      | id | active | name                 | seo_strategy_keyword_page | deleted |
      | 1  | 1      | https://somesite.com | 0                         | 0       |
    Given there are following site schedules
      | id | active | site_id | interval_between_site_download |
      | 1  | 1      | 1       | 86001                          |


  # cRud ---------------------------------------------------------------------------------------------------------------
  Scenario: Admin can list all site schedules
    Given I am logged in as "admin"
    When I request "GET api/v1/siteschedule"
    Then the response status code should be 200
    And the "Content-Type" header should be "application/json; charset=utf-8"
    And the "result" property should be an array
    And the "result.0.intervalBetweenSiteDownload" property should equal "86001"

  Scenario: Client cannot list all site schedules
    Given I am logged in as "client"
    When I request "GET api/v1/siteschedule"
    Then the response status code should be 403

  # crUd ---------------------------------------------------------------------------------------------------------------
  Scenario: Admin can update site schedule
    Given I am logged in as "admin"
    Given I have the payload:
      """
      {
        "intervalBetweenSiteDownload": "81111"
      }
      """
    When I request "PUT api/v1/siteschedule/1"
    Then the response status code should be 200
    And the "result.intervalBetweenSiteDownload" property should equal "81111"

  Scenario: Client cannot update site schedule
    Given I am logged in as "client"
    Given I have the payload:
      """
      {
        "intervalBetweenSiteDownload": "81111"
      }
      """
    When I request "PUT api/v1/siteschedule/1"
    Then the response status code should be 403