Feature: Add event
  In order to schedule an event to evaluate global mood of people
  As an admin
  I need to add a new event

  Scenario: Add a new event
    When I add a new event with data:
      | id                                   | name | text | from                | to                  | day of week | splash screen |
      | dcc78e9f-7b74-4645-9146-916ba985c121 | foo  | bar  | 2016-01-15 18:00:00 | 2016-01-12 20:00:00 | 3           | true          |
    Then a new event should be added
