Feature: Join an event
  In order to get notified
  As a user
  I need to join an event

  Scenario: Add a registered user to an event
    Given I'm registered
    And there is an event
    When I join the event
    Then I should be added to the event
