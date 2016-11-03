Feature: Register user
  In order to access the application
  As a user
  I need to provide my identity

  Scenario: New user successfully registers
    Given I'm not registered yet
    When I try to register with valid data:
      | email                | device_token                         |
      | john.doe@example.com | 654C4DB3-3F68-4969-8ED2-80EA16B46EB0 |
    Then I should be registered

  Scenario: New user provides an invalid email address
    Given I'm not registered yet
    When I try to register with invalid data:
      | email      | device_token                         |
      | notanemail | 654C4DB3-3F68-4969-8ED2-80EA16B46EB0 |
    Then My registration should be rejected with the message "Invalid email address"

  Scenario: New user provides an invalid device token
    Given I'm not registered yet
    When I try to register with invalid data:
      | email                | device_token |
      | john.doe@example.com | 123          |
    Then My registration should be rejected with the message "Invalid device token"

  Scenario: Existing user provides a new device token
    Given I'm registered
    When I add a new device token "654C4DB3-3F68-4969-8ED2-80EA16B46EB0"
    Then I should have my new device token registered
