Feature: Not categorized or very small features

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session

#------------------------------------------------------------------
# language choose
#------------------------------------------------------------------
  Scenario: Language switcher
    Given I open main page
     When I switch language switcher to each of available language
     Then Page translation switched
      And Language stored in browser cookies
      And Language stay same when i navigate to other pages
      And Language stay same when i restart browser session
#------------------------------------------------------------------
# check livebet section
#------------------------------------------------------------------
  Scenario: Livebet section
    Given I open main page
     When I switch to live section
     Then Livebet section is valid

#------------------------------------------------------------------
# smoke check result section
#------------------------------------------------------------------
  Scenario: Results section
    Given I open main page
     When I switch to result section
     Then Result page is valid

#------------------------------------------------------------------
# smoke check live score section
#------------------------------------------------------------------
  Scenario: Live score section
    Given I open main page
     When I switch to live score section
     Then Live score page is valid

