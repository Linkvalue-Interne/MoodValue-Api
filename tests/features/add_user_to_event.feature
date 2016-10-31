Feature: Add a user to an event
  In order to get notified
  As a user
  I need to add myself to an event

  Scenario: Add a registered user to an event
    Given I'm registered
    And there is an event
    When I add myself to the event
    Then I should be added to the event
