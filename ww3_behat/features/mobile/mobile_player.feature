  Feature: Mobile: player account features

#---------------------------------------------
# Run before each scenario
#---------------------------------------------
    Background:
      Given I am start new browser session

#---------------------------------------------
# check user registration case
#---------------------------------------------
    Scenario: Register account
      Given I open main page of mobile site
#        And I click to "account"
        And I click to "sign in"
        And I click to "sign up"
        And I fill account registration form
        And I submit registration
        And I see "success registration" message
       Then I get "account registration" email
       When I open confirm email url
        And I enter confirm phone key
       Then Account created
#        But If i do logout
        But If i clear browser session and cookies
#        And I clear browser session and cookies
        And I open main page of mobile site
        And I login under account on mobile site
        And I no need any confirmation again
       Then I authorized
        And User personal data is correct

#---------------------------------------------
# check user change password
#---------------------------------------------
    Scenario: Change password
      Given I have account with "10" "EUR" in balance
        And I open main page of mobile site
        And I login under account on mobile site
        And I go to account page
        And I click on "Change password" button
       When I fill and submit change password form
       Then I see "password was successfully changed" message
        And I can not login via old password
        But I can login under new password

#---------------------------------------------
# check user forgot password
#---------------------------------------------
    Scenario: Forgot password
      Given I have account with "10" "EUR" in balance
        And I open main page of mobile site
        And I click to "sign in"
        And I click to "I forgot password" button
       When I fill email and click restore button
       Then I see "Instructions to restore password were sent to specified E-Mail" message
        And I get "Wir-Wetten account restore password" email
       When I open link for restore password
       Then I see "New password was generated and sent to E-Mail" message
        And I get "new password" email
        And I can not login via old password
        And I can login under new password

#---------------------------------------------
# check user change personal data
#---------------------------------------------
    Scenario: User change email
      Given I have account with "10" "EUR" in balance and random phone
        And I open main page of mobile site
        And I login under account on mobile site
        And I go to account page
        And I click to "edit personal data"
       When I change email
        And I click to "save" button
       Then I see "successfully saved you changed you e-mail address. confirmation instructions were sent to" message
        And I get "Wir-Wetten account change email address" email
       When I open confirm change email url
        And I see "new e-mail address has successfully been confirmed" message
#        And I see "New E-mail was confirmed successfully" message
       Then I can login under new email
        And I see new email in personal data tab
        And I can not login under old email

    Scenario: User change phone
      Given I have account with "10" "EUR" in balance and random phone
        And I open main page of mobile site
        And I login under account on mobile site
        And I go to account page
        And I go to personal data tab
       When I change phone number
        And I click to "save phone" button
        And I see "Confirm phone" form
        And I enter confirm phone key
       Then I can login under account
        And I see new phone in personal data tab

    Scenario: Edit player personal data
      Given I have account with "10" "EUR" in balance and random phone
        And I open main page of mobile site
        And I login under account on mobile site
        And I go to account page
        And I go to personal data tab
       When I change all personal data
        And I click to "save" button
       Then I see "Successfully saved" message
        And I see that all changed data is saved

#-------------------------------------------------------------------------------------------
# TEMPORARILY BLOCK ACCOUNT
#-------------------------------------------------------------------------------------------
    Scenario: Check Temporarily block account
      Given I have account with "100" "EUR" in balance
        And I have "1" "regular" games, where league level = "*"
        And I open main page of mobile site
        And I login under account on mobile site
        And I open game page
        And I select odd in "each" game
        And I click to "betslip" button in header
        And I create "single" type ticket
        And I open account page
        And I click to "Temporarily block account" button
       When I click to "block" button
       Then I can "not" use "place bets" functionality
        And I can "not" use "livebetting" functionality
        And I can "not" use "fill up deposit" functionality
        And I can "not" use "add one more temporarily block" functionality
        And I can "" use "view tickets" functionality
        And I can "" use "view games" functionality

#-------------------------------------------------------------------------------------------
# CLOSE ACCOUNT
#-------------------------------------------------------------------------------------------
    Scenario: Close account
      Given I have account with "10" "EUR" in balance
        And I open main page of mobile site
        And I login under account on mobile site
        And I go to account page
       When I click to "close account" button
       Then I can not login under account because account closed
        But If i enable account in backoffice
       Then I can login under account
