Feature: Handle json payload

    In order to benefit of complex json payload
    As a developper
    I dump json payload into request attributes

    Scenario: Send valid json
        Given I set "Content-Type" header equal to "application/json"
        When I send a POST request to "/echo" with body:
            """
            {
                "name": "Bond"
            }
            """
        Then the response should be in JSON
        And the JSON node "name" should be equal to "Bond"

    Scenario: Send invalid json
        Given I set "Content-Type" header equal to "application/json"
        When I send a POST request to "/echo" with body:
            """
            {
                "name": "Bond
            }
            """
        Then the response should be in JSON
        And the response status code should be 400

    Scenario: Send unsupported content-type
        Given I set "Content-Type" header equal to "text/html"
        When I send a POST request to "/echo" with body:
            """
            {
                "name": "Bond"
            }
            """
        Then the response status code should be 415
        And the JSON node "name" should not exist

    Scenario: Send invalid data
        Given I set "Content-Type" header equal to "application/json"
        When I send a POST request to "/echo" with body:
            """
            {
                "firstname": "James"
            }
            """
        Then the response status code should be 400
        And the JSON node "errors[0].parameter" should be equal to "name"

