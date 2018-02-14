Feature: Mobile: category disable

#------------------------------------------
# Run before each scenario
#------------------------------------------
  Background:
    Given I am start new browser session
      And I have account with "100" "CHF" in balance

  Scenario: User do not see game if game not published
    Given I have "2" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I check that games is available in public side
     When I unpublish first game
      And I open main page of mobile site
      And I login under account on mobile site
     Then I can not see unpublished game
      And User can not set bet by direct link
      And I check what second game are selected but disabled game is not selected
      And All categories page and second game is available

  Scenario: User can't open tournament page if tournament category disabled
    Given I have "2" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I check that games is available in public side
     When I disable "tournament" category
      And I open main page of mobile site
      And I login under account on mobile site
     Then "tournament" page is not available
      And "sport" page is available
      And "country" page is available
      And User can not set bet by direct link
      But if "tournament" category page enabled in backoffice
      And I open main page of mobile site
     Then All game categories is available

  Scenario: User can't open country page if country category disabled
    Given I have "2" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I check that games is available in public side
     When I disable "country" category
      And I open main page of mobile site
      And I login under account on mobile site
     Then User can not set bet by direct link
#     "tournament" page is not available
#      And "country" page is not available
#      And "sport" page is available
#      And User can not set bet by direct link
#     Then User can not set bet by direct link
#      But if "country" category page enabled in backoffice
#      And I open main page of mobile site
#     Then All game categories is available

  Scenario: User can't open sport  page if sport category disabled
    Given I have "2" "regular" games, where league level = "*"
      And I open main page of mobile site
      And I check that games is available in public side
     When I disable "sport" category
      And I open main page of mobile site
      And I login under account on mobile site
     Then User can not set bet by direct link
#     "tournament" page is not available
#      And "country" page is not available
#      And "sport" page is not available
#      And User can not set bet by direct link
#     Then User can not set bet by direct link
#      But if "sport" category page enabled in backoffice
#     Then All game categories is available
