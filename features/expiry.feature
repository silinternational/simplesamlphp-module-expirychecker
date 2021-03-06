Feature: Password expiration warning/notice feature

  Scenario: Password will expire in the distant future
    Given I provide credentials that will expire in the distant future
    When I login
    Then I should end up at my intended destination

  Scenario: Password will expire tomorrow
    Given I provide credentials that will expire very soon
    When I login
    Then I should see a warning that my password will expire soon
      And there should be a way to go change my password now
      And there should be a way to continue without changing my password

  Scenario: Password has expired
    Given I provide credentials that have expired
    When I login
    Then I should see a message that my password has expired
      And there should be a way to go change my password now
      But there should NOT be a way to continue without changing my password

  Scenario: Reject missing expiration date
    Given I provide credentials that have no password expiration date
    When I login
    Then I should see an error message

  Scenario: Reject invalid expiration date
    Given I provide credentials that have an invalid password expiration date
    When I login
    Then I should see an error message
