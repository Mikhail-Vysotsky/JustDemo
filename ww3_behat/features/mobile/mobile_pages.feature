Feature: Mobile: static pages

  Background:
    Given I am start new browser session
      And I open main page of mobile site

  Scenario: Switch to regular site
    Given I on mobile main page
     When I click to "go to website"
     Then I navigate to regular wir-wetten site

#----------------------------------------------------
# main page
#----------------------------------------------------
  Scenario: Information page
     When I click to "information" button in header
     Then Mobile "Information" page is opened

  Scenario: Sports page
    When I click to "sports" button on page
    Then Mobile "Sports" page is opened

  Scenario: Live page
    When I click to "LIVE" button on page
    Then Mobile "LIVE" page is opened

  Scenario: Results page
    When I click to "results" button on page
    Then Mobile "Results" page is opened

  Scenario: Account page
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I click to "Account" button in footer
     Then Mobile "Account" page is opened

  Scenario: Language switcher
    When I choose "de" language
    Then Site translate to "de"
    When I choose "en" language
    Then Site translate to "en"

#----------------------------------------------------
# Information
#----------------------------------------------------

  Scenario: About Us
    Given I open information page
    When I click to "About Us" in information section
    Then Mobile "About Us" information is opened

  Scenario: Privacy police
    Given I open information page
    When I click to "Privacy police" in information section
    Then Mobile "Privacy police" information is opened

  Scenario: Bonus rules
    Given I open information page
    When I click to "Bonus rules" in information section
    Then Mobile "Bonus rules" information is opened