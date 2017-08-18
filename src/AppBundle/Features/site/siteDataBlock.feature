Feature: Get keywords, pages, keywords positions for site
  Client should have a possibility to see "keywords, pages, keywords positions"(alias: siteDataBlock) for site.

  Background:
    Given there are following users
      | id | username | email              | password | role        | enabled |
      | 1  | client   | client@example.com | testpass | ROLE_CLIENT | 1       |

    Given there are following search engines
      | id | active | name   | shortName | type |
      | 1  | 1      | Google | G         | 0    |
      | 2  | 1      | Yandex | Y         | 1    |

    Given there are following sites
      | id | active | name                 | seo_strategy_keyword_page | deleted | acl(user_id,bitmask) |
      | 1  | 1      | https://somesite.com | 0                         | 0       | 1,1                  |
      | 2  | 1      | https://somesit2.com | 0                         | 0       |                      |

    Given there are following pages
      | id | active | name                 | site_id |
      | 1  | 1      | /123/page1.html      | 1       |
      | 2  | 1      | /hhhhhhhhhh?&a=page2 | 1       |

    Given there are following keywords
      | id | active | name              | site_id | pages_id | searchengines_id | from_place | search_engine_request_limit |
      | 1  | 1      | нержавеющая сталь | 1       | 1        | 1,2              | 47         | 500                         |
      | 2  | 1      | some keyword2     | 1       | 1        |                  |            |                             |

    Given there are following keywords positions
      | id | keyword_id | searchengine_id | position | url                                | created    |
      | 1  | 1          | 1               | 5        | http://www.site1.us/123/page1.html | now -1 day |
      | 1  | 1          | 2               | 545      | http://www.site1.us/123/           | now -1 day |
      | 1  | 1          | 1               | 5        | http://www.site1.us/123/           | now -2 day |
      | 1  | 1          | 1               | 15       | http://www.site1.us/123/page1.html | now -3 day |

  # cRud ---------------------------------------------------------------------------------------------------------------
  Scenario: Client can see keywords, pages, keywords positions for site he owns
    Given I am logged in as "client"
    When I request "GET api/v1/sitedatablock/1"
    Then the response status code should be 200
    And the "Content-Type" header should be "application/json; charset=utf-8"
    And the "result" property should be an array
    And the "result.totalRecords" property should equal "2"
    # Table header data
    And the "result.header.1.fulldate" property should be a string
    # Real keyword position
    And the "result.result.1.name" property should equal "нержавеющая сталь"
    And the "result.result.1.searchEngines.0._cell.1.pos.position" property should equal "5"
    And the "result.result.1.searchEngines.0._cell.1.pos.url" property should equal "http://www.site1.us/123/page1.html"
    And the "result.result.1.searchEngines.0._cell.4.pos" property should not exist