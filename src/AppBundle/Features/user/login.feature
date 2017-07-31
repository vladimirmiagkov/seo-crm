@user @authentication @security
Feature: User authentication
  In order to gain access to my admin management area
  As an admin
  I need to be able to login

  Background:
    Given there are following users
      | id | username | email               | password | role        | enabled |
      | 1  | admin    | admin@example.com   | 123456   | ROLE_ADMIN  | 1       |
      | 2  | client   | client@example.com  | testpass | ROLE_CLIENT | 1       |
      | 3  | client2  | client2@example.com | testpass | ROLE_CLIENT | 0       |

  Scenario: Logging in as admin
    When I am logging in with username "admin", and password "123456", as role "admin"
    Then the response status code should be 200

  Scenario: Logging in as client
    When I am logging in with username "client", and password "testpass", as role "client"
    Then the response status code should be 200

  Scenario: Cannot login with disabled account
    When I am logging in with username "client2", and password "testpass", as role "client"
    Then the response status code should be 401

# TODO: Scenario: Cannot request any content with disabled account AND not expired JWT