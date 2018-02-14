Feature: Static pages


#----------------------------------------------------
# Footer: Help & Legal
#----------------------------------------------------
  Scenario: Check 'Terms and conditions' page
    Given I open main page
     When I click to "Terms and conditions" link in footer
     Then I see "Terms and conditions" page
      And "Terms and conditions" is correct

  Scenario: Check 'Privacy policy' page
    Given I open main page
     When I click to "Privacy policy" link in footer
     Then I see "Privacy policy" page
      And "Privacy policy" is correct

  Scenario: Check 'Responsibility' page
    Given I open main page
     When I click to "Responsibility" link in footer
     Then I see "Responsibility" page
      And "Responsibility" is correct

  Scenario: Check 'Bonus program' page
    Given I open main page
     When I click to "Bonus program" link in footer
     Then I see "Bonus program" page
      And "Bonus program" is correct

#----------------------------------------------------
# Footer: Main menu
#----------------------------------------------------
  Scenario: Check 'Bets' link in footer
    Given I open main page
     When I click to "Bets" link in footer
     Then "Bets" page is opened

  Scenario: Check 'Live' link in footer
    Given I open main page
     When I click to "Live" link in footer
     Then "Live" page is opened

  Scenario: Check 'Results' link in footer
    Given I open main page
     When I click to "Results" link in footer
     Then "Results" page is opened

  Scenario: Check 'Live Score' link in footer
    Given I open main page
     When I click to "Live Score" link in footer
     Then "Live Score" page is opened


#----------------------------------------------------
# Footer: Left banners
#----------------------------------------------------
  Scenario: Check 'Mobile version' banner
    Given I open main page
     When I click to "Mobile version" banner
     Then I navigate to mobile site

  Scenario: Check 'Bonus program' banner
    Given I open main page
     When I click to "Bonus program" banner
     Then I see "Bonus program" page
      And "Bonus program" is correct


##-------------------------------------------------------------------------------------------
## CHECK STATIC PAGES
##-------------------------------------------------------------------------------------------
#  Scenario: CHECK AVAILABLE LOCALIZATION OF BONUS SYSTEM PAGES (/INDEX.PHP?AC=USER/BONUS/INFO). (DE, EN, FR, IT, TR, RO, SR, HU)
#  Scenario: CHECK AVAILABLE LOCALIZATION OF 'BY CARDS AT THE AGENCY' PAGE (/INDEX.PHP?AC=USER/REGISTER/AGENCY).
#  Scenario: CHECK AVAILABLE LOCALIZATION OF INFO PAGE (/INDEX.PHP?AC=USER/ABOUT). AVAILABLE LOCALIZATION: EN, DE, FR, IT, ES, TR, PT, SQ, RO, HU,
#  Scenario: CHECK AVAILABLE LOCALIZATION OF MAIN PAGES (WWW.WIR-WETTEN.COM). AVAILABLE LOCALIZATIONS: EN, DE, FR, IT, TR, RO, SR, HU.
#  Scenario: CHECK AVAILABLE LOCALIZATION OF PRIVACY POLICE PAGE (/INDEX.PHP?AC=USER/PRIVACY). AVAILABLE LOCALIZATIONS: EN, DE, FR, IT, ES, TR, PT, SQ, RO, HU.
#

##-------------------------------------------------------------------------------------------
## CHECK LIVESCORE SERVICE
##-------------------------------------------------------------------------------------------
#  Scenario: LOGIN TO WW, OPEN LIVESCORE PAGE AND CHECK BASIC ELEMEMENTS
#  - CHECK SEARCH BY KEYWORDS:
#  - CHANGE REFRESH TIME AND CHECK THAT AUTO-REFRESH IS WORK
#
##-------------------------------------------------------------------------------------------
## LOGIN PAGE TESTS
##-------------------------------------------------------------------------------------------
#    Scenario: LOGIN PAGE TESTS
#      - OPEN LOGIN PAGE AND CHECK IT.
#      - FORGOT PASSWORD FORM IS AVAILABLE AND WORK
#      - wrong password check
#
##-------------------------------------------------------------------------------------------
##  CHECK RESULTS PAGE
##-------------------------------------------------------------------------------------------
#  Scenario: Check results page