Feature: Basic ticket

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session

#-------------------------------------------------------------------------------------------
# CHECK TICKETS
#-------------------------------------------------------------------------------------------
  Scenario: User can place bet to game and create ticket
    Given I have account with "100" "CHF" in balance
      And I have "1" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created

  Scenario: User can create single ticket
    Given I have account with "100" "CHF" in balance
      And I have "3" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I select "single" ticket type
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket type is "single"

  Scenario: User can create double ticket
    Given I have account with "100" "CHF" in balance
      And I have "4" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I select "double" ticket type
      And I select double bets for each games
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket type is "double"

  Scenario: User can create multi ticket
    Given I have account with "100" "CHF" in balance
      And I have "3" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I select "multi" ticket type
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket type is "multi"

  Scenario: User can create system ticket
    Given I have account with "100" "CHF" in balance
      And I have "4" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I select "system" ticket type
      And I select two banks
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket type is "system"

  Scenario: User can place bet to outright game
    Given I have account with "100" "CHF" in balance
      And I have "1" "outright" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created

  Scenario: User can place bet to special odd
    Given I have account with "100" "CHF" in balance
      And I have "1" "special" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bet to any special odd
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created

  Scenario: User can mix regular bets to regular and outright games in one ticket
    Given I have account with "100" "CHF" in balance
      And I have match with "1" outright games and "4" regular games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
     Then Ticket can be only single type
     When I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket contain 1 outright and 4 regular games and ticket type is single

  Scenario: User can delete all selected bets
    Given I have account with "100" "CHF" in balance
      And I have "5" "regular" games, where league level = "*"
      And I login under account
      And I open game page
      And I select bets to "each" games
     When I delete two games
      And I set stake "5"
      And I click to "set" button
     Then Ticket is created
      And Ticket contain "3" games

  Scenario: Check ticket won
    Given I have account with "100" "CHF" in balance
      And I have "1" "regular" games, where league level = "*"
      And I login under account
      And I create ticket
     When Ticket is "won"
      And I open game page
     Then User balance is greater
      And Ticket mark as "won" in user interface

  Scenario: Check ticket lost
    Given I have account with "100" "CHF" in balance
      And I have "1" "regular" games, where league level = "*"
      And I login under account
      And I create ticket
     When Ticket is "lost"
      And I open game page
     Then User balance is stay same
      And Ticket mark as "lost" in user interface

  Scenario: Check ticket canceled
    Given I have account with "100" "CHF" in balance
      And I have "1" "regular" games, where league level = "*"
      And I login under account
      And I create ticket
     When Ticket is "canceled"
      And I open game page
     Then User balance is stay same
      And Ticket mark as "canceled" in user interface

  Scenario: User can place bet to not running livebet match
    Given I have "5" "not running" "livebet" game for livebet where league level = "*"
      And I have account with "100" "CHF" in balance
      And I open main page of regular site
      And I login under account on regular site
      And I open livebet page
      And I place bet to each livebet game
     When I set stake "10"
      And I click to "set" button
     Then Ticket is created

  Scenario: User can place bet to running livebet match
    Given I have "5" "running" "livebet" game for livebet where league level = "*"
      And I have account with "100" "CHF" in balance
      And I open main page of regular site
      And I login under account on regular site
      And I open livebet page
      And I place bet to each livebet game
     When I set stake "10"
      And I click to "set" button
     Then Ticket is created


#-------------------------------------------------------------------------------------------
# CHECK TOP7 EVENTS
#-------------------------------------------------------------------------------------------
  Scenario: Check top7 feature
    Given I have account with "100" "CHF" in balance
      And I have "10" "regular" games, where league level = "*"
      And I create top7 list
      And I login under account
     When I open top7 list
      And I select 7 games
      And I click to "10 set and print coupon" button
      And I open 'bets' tab
     Then Ticket type is "top 7"
#  Scenario: CHECK DISABLE TOP7 LISTS

#-------------------------------------------------------------------------------------------
# CHECK MAIN EVENTS
#-------------------------------------------------------------------------------------------
   Scenario: Check main event feature
     Given I have account with "100" "CHF" in balance
       And I have "3" "regular" games, where league level = "*"
       And I create main event
       And I login under account
      When I open main event
       And I select bets to "each" games
       And I set stake "5"
       And I click to "set" button
      Then Ticket is created
       And Ticket type is "multi"

#------------------------------------------------------------------
# check stake block
#------------------------------------------------------------------
  Scenario: Check ticket block tabs: max button
    Given I open main page
     When User click to "max" button in stake block
     Then Set maximum stake
      And "+" button is disabled

  Scenario: Check cancel button in stake block
    Given I open main page
     When User click to "max" button in stake block
      And User click to "C" button in stake block
     Then Stake is null

  Scenario: Check "+" and "-" button in stake block
    Given I open main page
     When User "10" times click to "+" button
      And User "4" times click to "-" button
     Then Stake is "6"

  Scenario: Check button with stake amount
    Given I open main page
     When User click to "10" button in stake block
     Then Stake is "10"
     When User click to "50" button in stake block
     Then Stake is "50"
     When User click to "10" button in stake block
     Then Stake is "10"
     When User click to "100" button in stake block
     Then Stake is "100"
#     When User click to "100" button in stake block
#     Then Stake is "100"
     When User click to "500" button in stake block
     Then Stake is "500"
     When User click to "10" button in stake block
     Then Stake is "10"




