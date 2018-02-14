Feature: top7

#---------------------
# Выполняется перед каждым сценарием
#---------------------
  Background:
    Given I am start new browser session
      And I have account with "100" "CHF" in balance

#---------------------
# Check top7 available
#---------------------
  Scenario: User can place bet to top7 event
    Given I login under account
     When I open top7 list
      And I select 7 games
      And I click to "10 set and print coupon" button
      And I open 'bets' tab
     Then Ticket type is "top 7"

  Scenario: User can't create top7 ticket if here change 6 games
    Given I login under account
     When I open top7 list
      And I select 6 games
      And I click to "10 set and print coupon" button
     Then Top7 ticket is not created

  Scenario: User can't select more 7 outcomes in top7 ticket
    Given I login under account
     When I open top7 list
      And I try to select 8 games
     Then Only 7 games selected
     When I click to "10 set and print coupon" button
      And I open 'bets' tab
     Then Ticket type is top7 and ticket contain only 7 games

#---------------------
# Check win condition
#---------------------
    Scenario: Top7 ticket is win if each user bet win
      Given I login under account
        And I open top7 list
        And I select 7 games
        And I click to "10 set and print coupon" button
       When Each of user bet win
       Then Top7 ticket is win

    Scenario: Top7 ticket lose if only six user bet win
      Given I login under account
        And I open top7 list
        And I select 7 games
        And I click to "10 set and print coupon" button
       When Six games win but other lose
       Then Top7 ticket is lost

#---------------------
# Check payout
#---------------------
#
#  Тop7 тикет выиграл, если выиграли все ставки тикета
#На top7 тикет не распростроняются бонусы
#Топ7 список содержит 10, необходимо выбрать 7 ставок
#На топ7 тикет можно поставить только 10 CHF (значение по умолчанию)
#Тикет выиграл, если все ставки выиграли.
#Выигрыш по тикету равен 10.000 chf
#
#
#
#Какие ограничения применяются к TOP7 тикитам?
#в разделе top7 tickets не вижу только что созданных top7 тикетов.
