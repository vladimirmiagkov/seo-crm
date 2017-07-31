@user @usercrud @security
Feature: Manage Users data via the RESTful API
  Admin should have a possibility to create, read, update users.
  Super Admin should have a possibility to delete users.
  In order to CRUD users
  As an admin / super admin
  I need to be able to CRUD users

  Background:
    Given there are following users
      | id | username   | email                  | password | role             | enabled |
      | 1  | superadmin | superadmin@example.com | testpass | ROLE_SUPER_ADMIN | 1       |
      | 2  | admin      | admin@example.com      | testpass | ROLE_ADMIN       | 1       |
      | 3  | client     | client@example.com     | testpass | ROLE_CLIENT      | 1       |


  # Crud ---------------------------------------------------------------------------------------------------------------
  Scenario: Admin can create a client
    Given I am logged in as "admin"
    Given I have the payload:
      """
      {
        "id": "4",
        "username": "testclient",
        "email" : "testclient@example.com",
        "password": "testpass",
        "roles": "ROLE_CLIENT",
        "enabled": "1"
      }
      """
    When I request "POST api/v1/user"
    Then the response status code should be 201
    And the "result.username" property should equal "testclient"
    And the "result.id" property should equal "4"


  Scenario: Admin cannot create an superadmin
    Given I am logged in as "admin"
    Given I have the payload:
      """
      {
        "username": "testsuperadmin",
        "email" : "testsuperadmin@example.com",
        "password": "testpass",
        "roles": "ROLE_SUPER_ADMIN",
        "enabled": "1"
      }
      """
    When I request "POST api/v1/user"
    Then the response status code should be 412


  Scenario: Client cannot create a client
    Given I am logged in as "client"
    Given I have the payload:
      """
      {
        "username": "testclient1",
        "email" : "testclient1@example.com",
        "password": "testpass",
        "roles": "ROLE_CLIENT",
        "enabled": "1"
      }
      """
    When I request "POST api/v1/user"
    Then the response status code should be 403


  # cRud ---------------------------------------------------------------------------------------------------------------
  Scenario: Admin can list all users
    Given I am logged in as "admin"
    When I request "GET api/v1/user"
    Then the response status code should be 200
    And the "Content-Type" header should be "application/json; charset=utf-8"
    And the "result" property should be an array
    And the "result.0.username" property should equal "superadmin"
    And the "result.0.createdBy" property should be an null


  Scenario: Client cannot list all users
    Given I am logged in as "client"
    When I request "GET api/v1/user"
    Then the response status code should be 403


  # ?? Scenario: User not found


  # crUd ---------------------------------------------------------------------------------------------------------------
  Scenario: Admin can update client
    Given I am logged in as "admin"
    Given I have the payload:
      """
      {
        "username": "client123"
      }
      """
    When I request "PUT api/v1/user/3"
    Then the response status code should be 200
    And the "result.username" property should equal "client123"


  Scenario: Admin cannot update superadmin
    Given I am logged in as "admin"
    Given I have the payload:
      """
      {
        "username": "superadmin123"
      }
      """
    When I request "PUT api/v1/user/1"
    Then the response status code should be 412


  Scenario: Client cannot update client
    Given I am logged in as "client"
    Given I have the payload:
      """
      {
        "username": "client123"
      }
      """
    When I request "PUT api/v1/user/3"
    Then the response status code should be 403


  # cruD ---------------------------------------------------------------------------------------------------------------
  Scenario: Superadmin can delete admin
    Given I am logged in as "superadmin"
    When I request "DELETE api/v1/user/2"
    Then the response status code should be 204


  Scenario: Superadmin can delete client
    Given I am logged in as "superadmin"
    When I request "DELETE api/v1/user/3"
    Then the response status code should be 204


  Scenario: Superadmin cannot delete themself
    Given I am logged in as "superadmin"
    When I request "DELETE api/v1/user/1"
    Then the response status code should be 412


  Scenario: Admin cannot delete client
    Given I am logged in as "admin"
    When I request "DELETE api/v1/user/3"
    Then the response status code should be 403