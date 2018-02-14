Feature: Player Vouchers

  Background:
    Given I am start new browser session

#----------------------------------------------------------------------------
# Scenarios for public part
#----------------------------------------------------------------------------

  Scenario: Admin can create voucher
    Given I login as admin
      And I open "player vouchers" page
     When I click to "Generate new Player Vouchers" button
      And I fill Player Voucher Generation form
      And I submit player vouchers generation form
     Then I see and activate player vouchers
      And I can activate player vouchers
      And User balance is "50"

  Scenario: Player can not redeem not active voucher
    Given I have account with "0" "EUR" in balance
      And I have not active voucher
     When I login under account on regular site
      And I go to account page
      And I click to "redeem voucher"
      And I enter and submit voucher code
     Then I see error message about voucher is locked
      And User balance is "0"

  Scenario: Player can redeem voucher
    Given I have account with "0" "EUR" in balance
      And I have '1' active voucher
     When I login under account on regular site
      And I go to account page
     Then I can redeem voucher
      And User balance is "50"

  Scenario: Player can activate several vouchers
    Given I have account with "0" "EUR" in balance
      And I have '3' active voucher
     When I login under account on regular site
      And I go to account page
      And I can redeem voucher
     Then I can redeem remaining two vouchers
      And User balance is "150"

   Scenario: One voucher can be activate only one player
    Given I have account with "0" "EUR" in balance
      And I have '1' active voucher
      And I login under account on regular site
      And I go to account page
      And I redeem voucher
      And User balance is "50"
     When I have one more account with "0" "EUR" in balance
      And I login under account
      And I go to account page
      And I click to "redeem voucher"
      And I enter and submit voucher code
     Then I see error message about voucher already been used
      And User balance is "0"


  Scenario: Player can not redeem one voucher more one times
    Given I have account with "0" "EUR" in balance
      And I have '1' active voucher
      And I login under account on regular site
      And I go to account page
      And I redeem voucher
     When I click to "redeem voucher"
      And I enter and submit voucher code
     Then I can not redeem voucher again
      And I see error message about voucher already been used
      And User balance is "50"

#----------------------------------------------------------------------------
# Scenarios for admin part
#----------------------------------------------------------------------------

  Scenario: Search form in player voucher page are work
    Given I login as admin
      And I have '1' active voucher
      And I open "player vouchers" page
     When I enter comment as search keyword
     Then I found this vouchers

  Scenario: Admin can view PDF document with player vouchers
    Given I login as admin
      And I have '1' active voucher
      And I open "player vouchers" page
      And I find test voucher
     When I click to "PDF" link in voucher row
     Then I download PDF document with vouchers

  Scenario: Admin can view EXCEL document with vouchers
    Given I login as admin
      And I have not active voucher
      And I have '1' active voucher
      And I find test voucher
     When I click to "Excel" link in voucher row
     Then I get excel document with vouchers

  Scenario: Admin can open popUp with voucher details
    Given I login as admin
      And I have '3' active voucher
      And I find test voucher
     When I click to "Vouchers" link in voucher row
     Then I see popUp with each voucher detail

