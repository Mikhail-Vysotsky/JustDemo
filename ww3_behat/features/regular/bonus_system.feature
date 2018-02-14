Feature: Bonus system

#---------------------
# Run before each scenario
#---------------------
  Background:
    Given I am start new browser session


#---------------------
# Check limit by quota
#---------------------

    Scenario: If user create ticket here put bonus point (if ticket quota more 1.5)
      Given I have "1" "regular" games, where league level = "*" and min quota = "1.5"
        And I have account with "100" "EUR" in balance
        And I login under account
       When I create ticket
       Then Player "5" get bonus points

    Scenario: If user create ticket here do not get bonus points if ticket quota less 1.5
      Given I have "1" "regular" games, where league level = "*" and max quota = "1.4"
        And I have account with "100" "EUR" in balance
        And I login under account
       When I create ticket
       Then Player do not have bonus points

    Scenario: If user create ticket with several games and ticket quota less 1.5 here do not get bonus points
      Given I have "3" "regular" games, where league level = "*" and max quota = "1.1"
        And I have account with "100" "EUR" in balance
        And I login under account
       When I create ticket
       Then Player do not have bonus points

    Scenario: If user create ticket with several games and ticket quota greater then 1.5 here get bonus points
      Given I have "3" "regular" games, where league level = "*", max quota = "1.2" and min quota = "1.2"
        And I have account with "100" "EUR" in balance
        And I login under account
       When I create ticket
       Then Player "5" get bonus points

#---------------------
# Check bonus shop
#---------------------
    Scenario: Check bonus shop page
      Given I have bonus shop items
        And I have account with "100" "EUR" in balance
        And I login under account
       When I go to bonus system page
        And I go to bonus shop page
       Then I see valid bonus shop page

    Scenario: User can buy clear card via bonus points
      Given I have account with "100" "EUR" in balance and "100000" bonus points
        And I have bonus shop items
        And I login under account
       When I go to bonus system page
        And I go to bonus shop page
        And I buy 'Deposit 50 EUR' in bonus shop
       Then I see that deposit is "150 EUR"
        And I see that bonus point balance is "61538"

    Scenario: User can buy dirty card via bonus points
      Given I have account with "100" "EUR" in balance and "100000" bonus points
        And I have bonus shop items
        And I login under account
       When I go to bonus system page
        And I go to bonus shop page
        And I buy 'Card 500 EUR' in bonus shop
       Then I see that deposit is "100 EUR"
        And I see that bonus point balance is "88210"
        And I see card in bonus points tab
        And I see card in money source select box

    Scenario: I can not buy clear card if i have bonus points less price
      Given I have account with "100" "EUR" in balance and "35000" bonus points
        And I have bonus shop items
        And I login under account
       When I go to bonus system page
        And I go to bonus shop page
        And I try to buy 'Deposit 50 EUR' in bonus shop
       Then I see that deposit is "100 EUR"
        And I see that bonus point balance is "35000"

    Scenario: User can not buy dirty card if i have bonus points less price
      Given I have account with "100" "EUR" in balance and "1000" bonus points
        And I have bonus shop items
        And I login under account
       When I go to bonus system page
        And I go to bonus shop page
        And I try to buy 'Card 50 EUR' in bonus shop
       Then I see that deposit is "100 EUR"
        And I see that bonus point balance is "1000"

#---------------------
# Check clean up card condition
#---------------------
    Scenario: Check that user get points for card only for using bonus card
      Given I have account with "100" "EUR" in balance and "300" bonus points
        And I have "1" "regular" games, where league level = "*" and min quota = "1.5"
        And I have bonus shop items
        And I login under account
        And I buy bonus card
       When I use money for create ticket
       Then I do not get bonus points on card
        But If i use bonus card to create ticket
       Then I get bonus point to card

    Scenario: User should collect points to get money and should create 10 tickets after it all money from card move to user balance and card is close
      Given I have account with "100" "EUR" in balance and "300" bonus points
        And I have "10" "regular" games, where league level = "*" and min quota = "1.5"
        And I have bonus shop items
        And I login under account
        And I buy bonus card
       When I create "10" tickets
        And each ticket win
       Then I get real money from card to my deposit
#      Then User balance is greater
        And bonus card automatical closed

#      Given I have account with "100" "EUR" in balance and "300" bonus points


#    Scenario: User can create single ticket from bonus card
#    Scenario: User can create multi ticket from bonus card
#    Scenario: User can not create double ticket from bonus card
#    Scenario: User can not create multi system from bonus card


#    Scenario: If user use bonus card to create ticket here have limit by quota
#    Scenario: If user have bonus card here can create ticket via deposit and via card
#    Scenario: Check that user can't use and do not see card if date expired
#    Scenario: To get money from card user should

    #quota limit
#    Scenario: Admin can set different quota limit for currency
#      Given I have account with "0" "EUR" in balance
#        And I have account with "0" "CHF" in balance
#        And Admin set quota limit to bonus card with "CHF" as '1.3'
#        And Admin set quota limit to bonus card with "EUR" as '1.5'

    # clean up card condition
#    Scenario: User should collect points to get money and should won 10 tickets after it all money from card move to user balance and card is close
#    Scenario: User can create single ticket from bonus card
#    Scenario: User can create multi ticket from bonus card
#    Scenario: User can not create double ticket from bonus card
#    Scenario: User can create multi system from bonus card


    # check backoffice features
#    Scenario: Admin can add bonus card to user from backoffice
#    Scenario: Admin can see and block cards on player page
#    Scenario: Admin can see BP transactions
#     Scenario: Admin can create dirty card
#     Scenario: Admin can create clean card
#------
# bugs
#- user can try create system and double ticket and when here click 'set' button here get error about quota
#- check old bonus system
#- double and system ticket tab shold be disabled
#- user can create several tickets to one odd of match from one card


#    (14:53:26) vysotsky@rssystems.ru/work: не совсем понял. юзер может с одной бонус карты создавать дубли тикетов?
#    (17:31:44) vysotsky@rssystems.ru/work: Саш, по начислению с бонус карты на счет момент
#    (17:32:32) vysotsky@rssystems.ru/work: оно втихаря начисляется, считаю это не правильным.
#    надо хотя бы на почту отправлять сообщение или там где статистика по карточке просто показать кнопку вроде "забрать бабло"