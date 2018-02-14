Feature: Add and get money from deposit

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session


#-------------------------------------------------------------------------------------------
# CHECK FILL DEPOSIT
#-------------------------------------------------------------------------------------------
#  visa
  Scenario: Check fill deposit by Visa
    Given I have account with "0" "EUR" in balance
      And I login under account
     When I click to "deposit" button
      And I select "visa" payment method
      And I set payment amount "10"
      And I submit "visa" payment
      And I fill "visa" credit card data on secure trading side
     Then I see that deposit is "10 EUR"
      And "visa" payment method stored
      But If i do one more payment on "1" "EUR"
     Then I not redirect to secure trading
      And I see that deposit is "11 EUR"

#  mastercard
  Scenario: Check fill deposit by Mastercard
    Given I have account with "0" "EUR" in balance
      And I login under account
     When I click to "deposit" button
      And I select "mastercard" payment method
      And I set payment amount "10"
      And I submit "mastercard" payment
      And I fill "mastercard" credit card data on secure trading side
     Then I see that deposit is "10 EUR"
      And "mastercard" payment method stored
      But If i do one more payment on "1" "EUR"
     Then I not redirect to secure trading
      And I see that deposit is "11 EUR"

#  skrill
  Scenario: Check fill deposit by skrill
    Given I have account with "0" "EUR" in balance
      And I login under account
     When I click to "deposit" button
      And I select "skrill" payment method
      And I set payment amount "1"
      And I submit "skrill" payment
      And I fill "skrill" credit card data on payment system side
     Then I see that deposit is "1 EUR"
      And "skrill" payment method stored
      But If i do one more payment on "1" "EUR"
     Then I fill "skrill" credit card data on payment system side
      And I see that deposit is "2 EUR"

#  paypal | test does not work
  Scenario: Check fill deposit by paypal
    Given I have account with "0" "EUR" in balance
      And I login under account
     When I click to "deposit" button
      And I select "paypal" payment method
      And I set payment amount "1"
      And I submit "paypal" payment
      And I fill "paypal" credit card data on paypal side
     Then I see that deposit is "1 EUR"
      And "paypal" payment method stored
      But If i do one more payment on "1" "EUR"
     Then I fill "paypal" credit card data on payment system side
      And I see that deposit is "2 EUR"

#  paysafe
  Scenario: Check fill deposit by paysafe
    Given I have account with "0" "EUR" in balance
      And I login under account
     When I click to "deposit" button
      And I select "paysafe" payment method
      And I set payment amount "1"
      And I click to "pay" button
#      And I submit "paysafe" payment
      And I fill "paysafe" credit card data on paysafe payment side
     Then I see that deposit is "1 EUR"
#      And "paysafe" payment method stored
#      But If i do one more payment on "1" "EUR"
#     Then I fill "paysafe" credit card data on paysafe payment side
#      And I see that deposit is "2 EUR"

##-------------------------------------------------------------------------------------------
##  CHECK WITHDRAW DEPOSIT
##-------------------------------------------------------------------------------------------

# visa
  Scenario: Withdraw via visa card
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "visa" payment method
      And I open account page
     When Click to withdraw button
      And I set "5" "EUR" to withdraw amount
      And I click to "pay" button
     Then I see payout information message
      And User balance is "10"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on regular site
     Then User balance is "5"

# mastercard
  Scenario: Withdraw via mastercard card
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "mastercard" payment method
      And I open account page
     When Click to withdraw button
      And I set "5" "EUR" to withdraw amount
      And I click to "pay" button
     Then I see payout information message
      And User balance is "10"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on regular site
     Then User balance is "5"

# skrill
  Scenario: Withdraw via skrill payment
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "skrill" payment method
      And I open account page
     When Click to withdraw button
      And I set "5" "EUR" to withdraw amount
      And I click to "pay" button
     Then I see payout information message
      And User balance is "10"
     When I login as admin
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account on regular site
     Then User balance is "5"

# paypal
  Scenario: Withdraw via paypal payment
    Given I have account with "0" "EUR" in balance
      And User mark as advanced
      And I fill up deposit via "paypal" payment method
      And I open account page
     When Click to withdraw button
      And I set "5" "EUR" to withdraw amount
      And I click to "pay" button
     Then I see successful message
      And User balance is "5"

#----------------------------------------------------
# Not business logic issues
#----------------------------------------------------

  Scenario: Admin can't make payout if user balance is null
    Given I have account with "0" "EUR" in balance
      And I have "1" "regular" games, where league level = "*"
      And User mark as advanced
      And I fill up deposit via "visa" payment method
      And I open account page
     When Click to withdraw button
      And I set "10" "EUR" to withdraw amount
      And I click to "pay" button
      And I see payout information message
      And User balance is "10"
      And I open game page
      And I select bets to "each" games
      And I set stake "5"
      And I click to "set" button
      And Ticket is created
      And User balance is "5"
     When I login as admin
      And I go to "payout claims" page
      And admin try to approve payout and see error
      And I login under account on regular site
     Then User balance is "5"

#-------------------------------------------------------------------
# Payout via bank transfer
#-------------------------------------------------------------------
  Scenario: Payout via bank transfer
    Given I have account with "100" "EUR" in balance
      And User mark as advanced
      And I login under account
      And I open account page
      And Click to withdraw button
     When I click to "Bank transfer" button
      And I fill bank transfer form and set amount "10"
      And I submit payout
     Then I see successful claim for payment create
     When I go to "payout claims" page
      And I find and approve test payout
      And I login under account
     Then I see that my deposit is "90 EUR"
     When I open withdraw form again
      And I set "5" EUR to withdraw
     Then I no need fill bank transfer form again
     When I submit payout via saved method
      And I see successful claim for payment create
      And I go to "payout claims" page
      And I find and approve test payout
      And I login under account
     Then I see that my deposit is "85 EUR"