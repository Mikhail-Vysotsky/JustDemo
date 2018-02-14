Feature: User account

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session


##-------------------------------------------------------------------------------------------
## CHECK USER ACCOUNT
##-------------------------------------------------------------------------------------------
  Scenario: Register account
    Given I open main page
      And I click to "register" button
      And I fill account registration form
      And I submit registration
      And I see "success registration" message
     Then I get "account registration" email
     When I open confirm email url
      And I enter confirm phone key
     Then Account created
      But If i do logout
      And I clear browser session and cookies
      And I open main page
      And I login under account
      And I no need any confirmation again
     Then I authorized
      And User personal data is correct

  Scenario: Change password
    Given I have account with "10" "EUR" in balance
      And I login under account
      And I go to account page
      And I go to personal data tab
     When I fill and submit change password form
     Then I see "password was successful changed" message
      And I can not login via old password
      But I can login under new password

  Scenario: Forgot password
    Given I have account with "10" "EUR" in balance
      And I open main page
      And I click to "I forgot password" button
     When I fill email and click restore button
     Then I see "Instructions to restore password were sent to specified E-Mail" message
      And I get "Wir-Wetten account restore password" email
     When I open link for restore password
     Then I see "New password was generated and sent to E-Mail" message
      And I get "new password" email
      And I can not login via old password
      And I can login under new password

  Scenario: Edit existing account
    Given I have account with "10" "EUR" in balance and random phone
      And I login under account
      And I go to account page
      And I go to personal data tab
     When I change all personal data
      And I click to "save" button
      And I see "Successfully saved" message
     Then I see that all changed data is saved

  Scenario: User change email
    Given I have account with "10" "EUR" in balance and random phone
      And I login under account
      And I go to account page
      And I go to personal data tab
     When I change email
      And I click to "save" button
     Then I see "Need to confirm email" message
      And I get "Wir-Wetten account change email address" email
     When I open confirm change email url
      And I see "new e-mail address has successfully been confirmed" message
     Then I can login under new email
      And I see new email in personal data tab
      And I can not login under old email

  Scenario: User change phone
    Given I have account with "10" "EUR" in balance and random phone
      And I login under account
      And I go to account page
      And I go to personal data tab
     When I change phone number
      And I click to "save phone" button
      And I see "Confirm phone" form
      And I enter confirm phone key
     Then I can login under account
      And I see new phone in personal data tab

  Scenario: Close account
    Given I have account with "10" "EUR" in balance
      And I login under account
      And I go to account page
     When I click to "close account" button
      And I see "Account was successfully closed" message
     Then I can not login under account because account closed
      But If i enable account in backoffice
     Then I can login under account

#-------------------------------------------------------------------------------------------
# UPLOAD DOCUMENTS
#-------------------------------------------------------------------------------------------
  Scenario: User can upload documents in profile and admin can see this document in backoffice
    Given I have account with "10" "EUR" in balance and random phone
      And I login under account
      And I go to account page
     When I click to "upload documents" link
      And I fill upload documents form
      And I submit upload document form
     Then Document is uploaded
      And Admin can see document in backoffice

#-------------------------------------------------------------------------------------------
# DEPOSIT LIMITS
#-------------------------------------------------------------------------------------------
  Scenario: Max deposit limits
    Given I have account with "0" "EUR" in balance
      And I login under account
      And I open account page
      And I set "Max" deposit limits as "30"
     When I try to fill up deposit on "31" EUR
     Then I see that "Max" limits is work
      But I if i fill up deposit on "30" EUR
     Then User balance is "30"

  Scenario: Min deposit limits
    Given I have account with "0" "EUR" in balance
      And I login under account
      And I open account page
      And I set "Min" deposit limits as "5"
     When I try to fill up deposit on "4" EUR
     Then I see that "Min" limits is work
      But I if i fill up deposit on "5" EUR
     Then User balance is "5"

  Scenario: Daily deposit limits
    Given I have account with "0" "EUR" in balance
      And I login under account
      And I open account page
      And I set "Daily" deposit limits as "30"
     When I fill up deposit on "30" EUR
      And I try to fill up deposit on "1" EUR
     Then I see that "Daily" limits is work
      But If i change transaction date on one "day" back
     Then I can fill up deposit on "10" EUR
      And User balance is "40"

  Scenario: Weekly deposit limits
    Given I have account with "0" "EUR" in balance
      And I login under account
      And I open account page
      And I set "Weekly" deposit limits as "50"
     When I fill up deposit on "50" EUR
      And I try to fill up deposit on "1" EUR
     Then I see that "Weekly" limits is work
      But If i change transaction date on one "week" back
     Then I can fill up deposit on "10" EUR
      And User balance is "60"

  Scenario: Monthly deposit limits
    Given I have account with "0" "EUR" in balance
      And I login under account
      And I open account page
      And I set "Monthly" deposit limits as "100"
     When I fill up deposit on "100" EUR
      And I try to fill up deposit on "1" EUR
     Then I see that "Monthly" limits is work
      But If i change transaction date on one "month" back
     Then I can fill up deposit on "10" EUR
      And User balance is "110"
#-------------------------------------------------------------------------------------------
# PLAYER AND CUSTOMER PROTECTED PAGES
#-------------------------------------------------------------------------------------------
#    Scenario: Player protection information
#    Scenario: Customer protection page
#-------------------------------------------------------------------------------------------
# CHECK TRANSACTION PAGE
#-------------------------------------------------------------------------------------------
    Scenario: User see fill up deposit and withdraw transactions on transactions tab
      Given I have account with "0" "EUR" in balance
        And I login under account
        And I fill up deposit
        And I mark user as advanced
        And I withdraw money from deposit
        And I approve payout in backoffice
       When I go to transactions tab
       Then I see record about fill up and withdraw money


#-------------------------------------------------------------------------------------------
# TEMPORARILY BLOCK ACCOUNT
#-------------------------------------------------------------------------------------------
    Scenario: Check Temporarily block account
      Given I have account with "100" "EUR" in balance
        And I have "1" "regular" games, where league level = "*"
        And I login under account
        And I create ticket
        And I open account page
        And I click to "Temporarily block account" button
       When I click to "block" button
       Then I can "not" use "place bets" functionality
        And I can "not" use "livebetting" functionality
        And I can "not" use "fill up deposit" functionality
        And I can "not" use "add one more temporarily block" functionality
        And I can "" use "view tickets" functionality
        And I can "" use "view games" functionality

#  Scenario: Temporarily block account: Block betting
#    Given I have account with "100" "EUR" in balance
#      And I have "1" "regular" games, where league level = "*"
#      And I login under account
#      And I open account page
#      And I click to "Temporarily block account" button
#     When I choose "betting" functionality to block
#      And I click to "block" button
#     Then I can "not" use "place bets" functionality
#      And I can "" use "livebetting" functionality
#      And I can "" use "head-to-head" functionality
#      And I can "" use "fill up deposit" functionality
#      But If i remove all blocking from user in backoffice
#      And I login under account
#     Then I can "" use "place bets" functionality

#  Scenario: Temporarily block account: Block livebetting
#    Given I have account with "100" "EUR" in balance
#      And I have "1" "regular" games, where league level = "*"
#      And I login under account
#      And I open account page
#      And I click to "Temporarily block account" button
#     When I choose "livebetting" functionality to block
#      And I click to "block" button
#     Then I can "not" use "livebetting" functionality
#      And I can "" use "place bets" functionality
#      And I can "" use "head-to-head" functionality
#      And I can "" use "fill up deposit" functionality
#      But If i remove all blocking from user in backoffice
#      And I login under account
#     Then I can "" use "livebetting" functionality

#  Scenario: Temporarily block account: Block head-to-head feature
#    Given I have account with "100" "EUR" in balance
#      And I have "1" "regular" games, where league level = "*"
#      And I login under account
#      And I open account page
#      And I click to "Temporarily block account" button
#     When I choose "head-to-head" functionality to block
#      And I click to "block" button
#     Then I can "not" use "head-to-head" functionality
#      And I can "" use "place bets" functionality
#      And I can "" use "livebetting" functionality
#      And I can "" use "fill up deposit" functionality
#      But If i remove all blocking from user in backoffice
#      And I login under account
#     Then I can "" use "head-to-head" functionality

#  Scenario: Temporarily block account: Block deposit
#    Given I have account with "100" "EUR" in balance
#      And I have "1" "regular" games, where league level = "*"
#      And I login under account
#      And I open account page
#      And I click to "Temporarily block account" button
#     When I choose "deposit" functionality to block
#      And I click to "block" button
#     Then I can "not" use "fill up deposit" functionality
#      And I can "" use "place bets" functionality
#      And I can "" use "head-to-head" functionality
#      And I can "" use "livebetting" functionality
#      But If i remove all blocking from user in backoffice
#      And I login under account
#     Then I can "" use "fill up deposit" functionality
