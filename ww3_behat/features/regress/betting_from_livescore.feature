Feature: Betting from livescore

  Background:
    Given I have account with "100" "CHF" in balance

  Scenario: Place bet to simple regular match
    Given I have "1" "regular" games, where league level = "*"
      And I login under account
      And I go to livescore page
      And I found test game
     When I select bet to regular game from livescore tab
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created

  Scenario: Place bet to not running livebetting match
    Given I have "5" "not running" "livebet" game for livebet where league level = "*"
      And I login under account
      And I go to livescore page
      And I found test game
     When I select bet to each livebet game from livescore tab
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created

  Scenario: Place bet to running livebetting match
    Given I have "1" "running" "livebet" game for livebet where league level = "*"
      And I login under account
      And I go to livescore page
      And I found test game
     When I select bet to running livebet game from livescore tab
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
