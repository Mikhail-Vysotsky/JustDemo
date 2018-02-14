Feature: Mobile: ticket features

#---------------------
# before each scenario steps
#---------------------
  Background:
    Given I am start new browser session
      And I have account with "100" "CHF" in balance

#---------------------------------------------------
# Basic ticket type features
#---------------------------------------------------
  Scenario: User can create single ticket
    Given I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each" game
      And I click to "betslip" button in header
     Then I see that "single" available
      And I can create "single" type ticket

  Scenario: User can create multi ticket
    Given I have "3" "regular" games, where league level = "1st"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each" game
      And I click to "betslip" button in header
     Then I see that "multi" available
      And I can create "multi" type ticket

  Scenario: User can create system ticket
    Given I have "5" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each" game
      And I click to "betslip" button in header
      And I click to "4/5" button for switch ticket to system
      And I choose "3" banks
     Then I can create "system" type ticket

  Scenario: User can place bet to outright
    Given I have "1" "outright" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "outright" game
      And I click to "betslip" button in header
     Then I see that "single" available
      And I can create "single" type ticket

  Scenario: User can place bet to special outcome
    Given I have game with "10" special outcomes, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "special" game
      And I click to "betslip" button in header
     Then I see that "single" available
      And I can create "single" type ticket

  Scenario: User can place bet to not running livebet match on mobile site
    Given I have "5" "not running" "livebet" game for livebet where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
      And I open mobile livebet page
      And I place bet to each livebet game
     When I click to "betslip" button in header
      And I set stake "1"
      And I click to bet button
     Then Ticket is created

  Scenario: User can place bet to running livebet match on mobile site
    Given I have "5" "running" "livebet" game for livebet where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
      And I open mobile livebet page
      And I place bet to each livebet game
     When I click to "betslip" button in header
      And I set stake "1"
      And I click to bet button
     Then Ticket is created

  Scenario: User can mix regular and outright events in single type bets
    Given I have "1" "outright" games, where league level = "*"
      And I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each" game
      And I click to "betslip" button in header
      And I set stake "5"
     Then I can create "mixed single" type ticket

#---------------------------------------------------
# Ticket status
#---------------------------------------------------
  Scenario: User can win in betslip
    Given I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I create "single" type ticket
     When My ticket is "won"
     Then I see that ticket "won" in public interface

  Scenario: User can get canceled betslip
    Given I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
      And I create "single" type ticket
     When My ticket is "canceled"
     Then I see that ticket "canceled" in public interface

  Scenario: User can lose in betslip
    Given I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I create "single" type ticket
     When My ticket is "lose"
     Then I see that ticket "lost" in public interface

  Scenario: User can login after select bets
    Given I have "1" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
      And I set stake "5"
     When I click to bet button
      And I enter login and password
     Then I see that "single" available
      And I can create "single" type ticket
