Feature: Livescore search filter

  Background:
    Given I have account with "100" "CHF" in balance
      And I have "1" "regular" games, where league level = "*"

  Scenario: Check search by name
    Given I go to livescore page
     When I enter "home" team name
     Then Livescore found result by "home" team
     When I enter "away" team name
     Then Livescore found result by "away" team

  Scenario: Check search by sport
    Given I go to livescore page
     When I choose any sport
     Then Livescore page is refresh and some item found
     When I click to reset button in sport filter
     Then Livescore page is refresh

  Scenario: Check search by status
    Given I go to livescore page
     When I choose "all" in livescore filter
     Then Livescore page is refresh and "all" games present
     When I choose "result" in livescore filter
     Then Livescore page is refresh and "result" games present
     When I choose "inprogress" in livescore filter
     Then Livescore page is refresh and "inprogress" games present
     When I choose "live" in livescore filter
     Then Livescore page is refresh and "live" games present

  Scenario: Check auto-refresh work
    Given I go to livescore page
     When I choose refresh every 30 second
     Then Page refresh every 30 second

  Scenario: Check search by betslip
    Given I login under account on regular site
      And I create ticket
      And I go to livescore page
     When I enter ticket number
     Then I found game from ticket

  Scenario: Check "selected" tab
    Given I go to livescore page
      And I enter "home" team name
      And Livescore found result by "home" team
     When I click to checkbox to select game
      And I click to tab by name "selected"
     Then Opened tab by name selected
      And My game found
