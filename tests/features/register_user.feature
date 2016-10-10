Feature: Register user
  In order access the application
  As a user
  I need to provide my identity

  Scenario: New user successfully registers
    Given I'm not registered yet
    And I provide valid data:
      | email                | device_token                         |
      | john.doe@example.com | 654C4DB3-3F68-4969-8ED2-80EA16B46EB0 |
    When I try to register
    Then I should be registered

  Scenario: New user provides an invalid email address
    Given I'm not registered yet
    And I provide invalid data:
      | email      | device_token                         |
      | notanemail | 654C4DB3-3F68-4969-8ED2-80EA16B46EB0 |
    When I try to register
    Then My registration should be rejected with the message "Invalid email address"

  Scenario: New user provides an invalid email device token
    Given I'm not registered yet
    And I provide invalid data:
      | email                | device_token |
      | john.doe@example.com | 123          |
    When I try to register
    Then My registration should be rejected with the message "Invalid device token"
