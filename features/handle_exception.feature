Feature: Handle exception

    In order to let our application throw specific exception
    As a developper
    I map these exceptions to http status code

    Scenario: Throw mapped exception
        When I send a POST request to "/exception?supported"
        Then the response status code should be 409
        And the JSON node "errors.message" should be equal to "This is my app message"

    Scenario: Throw unmapped exception
        When I send a POST request to "/exception"
        Then the response status code should be 500
        And the JSON node "exception[0].message" should be equal to "Something wrong"
