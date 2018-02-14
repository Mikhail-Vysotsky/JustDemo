Feature: Main events
  Background:
    Given I am start new browser session

  Scenario: Check create main events
    Given I have account with "10" "EUR" in balance
      And I have "1" "regular" games, where league level = "*"
      And I open game page and store tournament label
     When I login as admin
      And I open "main events" page
      And I click to "main events" button
      And I fill create main events form
      And I submit create main events form
      And I login under account on regular site
     Then I can place ticket to main event
      And User balance is "0"

  Scenario: User can not see main event if is not active
    Given I have account with "0" "EUR" in balance
      And I have "1" "regular" games, where league level = "*"
      And I open game page and store tournament label
     When I login as admin
      And I open "main events" page
      And I click to "main events" button
      And I fill create main events form
      And I submit create main events form
      And I mark main event as not active
      And I login under account on regular site
     Then Main event is not available on public part