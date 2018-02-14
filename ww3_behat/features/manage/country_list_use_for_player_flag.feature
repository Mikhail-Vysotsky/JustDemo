Feature: Country list 'use for player' flag
  Background:
    Given I am start new browser session

  Scenario: Disabled 'use for player' flag on country list remove this country from select boxes in player profile.
    Given I have account with "0" "EUR" in balance
      And I login under account on regular site
      And I go to personal data tab
      And I store available countries
     When I login as admin
      And I open "countries" page
      And I disable several counties
      And I login under account on regular site
      And I go to personal data tab
     Then I see that disabled countries is not available
      But If i login as admin
      And I open "countries" page
      And I enable all countries
      And I login under account on regular site
      And I go to personal data tab
     Then I see that all countries available

  Scenario: If user country disabled user see empty country field on personal data tab
    Given I have account with "0" "EUR" in balance
      And I login under account on regular site
      And I go to personal data tab
      And I set country
     When I login as admin
      And I open "countries" page
      And I disable user country
      And I login under account on regular site
      And I go to personal data tab
     Then I see that country field is not set

