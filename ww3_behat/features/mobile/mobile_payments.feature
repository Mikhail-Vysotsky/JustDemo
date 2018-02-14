Feature: Mobile: add and get money from deposit

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session

#todo: need configure local instance
#-------------------------------------------------------------------------------------------
# CHECK FILL DEPOSIT
#-------------------------------------------------------------------------------------------
#  visa
  Scenario: Check fill deposit by Visa
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I go to payment methods page
      And I click to "visa" payment method
      And I set amount "10" EUR
      And I click to pay button
      And I enter "visa" credit card data on secure trading side
     Then I see that my deposit is "10 EUR"
      And "visa" payment method saved
      But If i try one more payment on "1" "EUR"
     Then Secure trading proccess payment without any redirect
      And I see that my deposit is "11 EUR"

#  mastercard
  Scenario: Check fill deposit by Mastercard
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I go to payment methods page
      And I click to "mastercard" payment method
      And I set amount "10" EUR
      And I click to pay button
      And I enter "mastercard" credit card data on secure trading side
     Then I see that my deposit is "10 EUR"
      And "mastercard" payment method saved
      But If i try one more payment on "1" "EUR"
     Then Secure trading proccess payment without any redirect
      And I see that my deposit is "11 EUR"

#  skrill
  Scenario: Check fill deposit by skrill
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I go to payment methods page
      And I click to "skrill" payment method
      And I set amount "1" EUR
      And I set email
      And I click to pay button
      And I enter "skrill" auth data on skrill payment side
     Then I see that my deposit is "1 EUR"
      And "skrill" payment method saved
      But If i try one more payment on "1" "EUR"
     Then I enter "skrill" auth data on skrill payment side
      And I see that my deposit is "2 EUR"

#  paypal | test does not work
  Scenario: Check fill deposit by paypal
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I go to payment methods page
      And I click to "paypal" payment method
      And I set amount "1" EUR
      And I set email
      And I click to pay button
      And I enter "paypal" credit card data on paypal side
     Then I see that my deposit is "1 EUR"
      And "paypal" payment method saved
      But If i do one more payment on "1" "EUR"
     Then I fill "paypal" credit card data on payment system side
      And I see that deposit is "2 EUR"

#  paysafe
  Scenario: Check fill deposit by paysafe
    Given I have account with "0" "EUR" in balance
      And I open main page of mobile site
      And I login under account on mobile site
     When I go to payment methods page
      And I click to "paysafe" payment method
      And I set amount "1" EUR
      And I click to pay button
      And I fill "paysafe" credit card data on paysafe side
     Then I see that my deposit is "1 EUR"
#      And "paysafe" payment method saved
#      But If i do one more payment on "1" "EUR"
#     Then I fill "paysafe" credit card data on payment system side
#      And I see that deposit is "2 EUR"

##-------------------------------------------------------------------------------------------
##  CHECK WITHDRAW DEPOSIT
##-------------------------------------------------------------------------------------------

# visa
  Scenario: Withdraw via visa card
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "visa" payment method on mobile site
      And I go to withdraw page
     When I set "5" EUR to withdraw
      And I click on "pay" button
     Then I see successful alert
#      And I see that my deposit is "10 EUR"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on mobile site
     Then I see that my deposit is "5 EUR"

# mastercard
  Scenario: Withdraw via mastercard card
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "mastercard" payment method on mobile site
      And I go to withdraw page
     When I set "5" EUR to withdraw
      And I click on "pay" button
     Then I see successful alert
      And I see that my deposit is "10 EUR"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on mobile site
     Then I see that my deposit is "5 EUR"



# skrill
  Scenario: Withdraw via skrill payment
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "skrill" payment method on mobile site
      And I go to withdraw page
     When I set "5" EUR to withdraw
      And I click on "pay" button
     Then I see successful alert
      And I see that my deposit is "10 EUR"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on mobile site
     Then I see that my deposit is "5 EUR"



# paypal
#  Scenario: Withdraw via paypal payment
#    Given I have account with "0" "EUR" in balance
#    And User mark as advanced
#    And I fill up deposit via "paypal" payment method
#    And I open account page
#    When Click to withdraw button
#    And I set "5" "EUR" to withdraw amount
#    And I click to "pay" button
#    Then I see successful message
#    And User balance is "5"

# paysafe
#  Scenario: Withdraw via paypal payment
#    Given I have account with "0" "EUR" in balance
#    And User mark as advanced
#    And I fill up deposit via "paysafe" payment method
#    And I open account page
#    When Click to withdraw button
#    And I set "5" "EUR" to withdraw amount
#    And I click to "pay" button
#    Then I see successful message
#    And User balance is "5"



#-------------------------------------------------------------------
# Payout via bank transfer
#-------------------------------------------------------------------
  Scenario: Payout via bank transfer
    Given I have account with "100" "EUR" in balance
      And User mark as advanced
      And I login under account on mobile site
      And I open account page
      And Click to withdraw button
     When I click to "Bank transfer" button
      And I fill bank transfer form and set amount "10"
      And I submit payout
     Then I see successful claim for payment create
     When I go to "payout claims" page
      And I find and approve test payout
      And I login under account on mobile site
     Then I see that my deposit is "90 EUR"
     When I open withdraw form again
      And I set "5" EUR to withdraw
     Then I no need fill bank transfer form again
     When I submit payout via saved method
      And I see successful claim for payment create
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on mobile site
     Then I see that my deposit is "85 EUR"