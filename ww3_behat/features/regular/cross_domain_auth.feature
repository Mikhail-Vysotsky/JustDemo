Feature: Cross domain player authorization

  Background:
    Given I am start new browser session

  Scenario: If user sign in on mobile site then him auth on regular site
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
     When I login under account on mobile site
     Then I authorized on regular site

  Scenario: If user sign in on regular site then him auth on mobile site
    Given I have account with "0" "EUR" in balance
      And I open main page of regular site
     When I login under account on regular site
     Then I authorized on mobile site

  Scenario: If user logout on mobile site then him logout on regular site
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
      And I check that i authorized on regular site
     When I open main page of mobile site
      And I do logout from mobile site
     Then I not authorized on regular site

  Scenario: If user logout on regular site then him logout on mobile site
    Given I have account with "0" "EUR" in balance
      And I open main page of regular site
      And I login under account on regular site
      And I check that i authorized on mobile site
     When I open main page of regular site
      And I do logout from regular site
     Then I not authorized on mobile site