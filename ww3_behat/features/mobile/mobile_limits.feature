Feature: Mobile: limits feature

  Background:
    Given I am start new browser session
      And I have account with "100" "EUR" in balance

#---------------------
# Проверка Min games
#---------------------

  Scenario: Ticket can't contain less games then Min games amount
    Given I have "3" "regular" games, where league level = "1st"
      And I open main page of mobile site
      And I login under account on mobile site
     When I place bet to "two" games
      And I click to "betslip" button in header
      And I click to bet button
     Then I see "please select more 1 bet(s)" message
      And Ticket not created and balance "100"

#  Scenario: Тикет может содержать больше ставок, чем Min games amount
  Scenario: Ticket can contain greater bets then minimum games amount for ticket type
    Given I have "4" "regular" games, where league level = "1st"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
     Then I see that "multi" available
      And I can create "Multi" type ticket
#     Then Ticket is created and balance "99 EUR"
     Then Ticket is created and balance "99"

#  Scenario: При выборе игр двух разных лиг срабатывает наиболее строгое ограничение
  Scenario: If user choose two games with different min games limit, then system choose the most strict rule
    Given I have "1" "regular" games, where league level = "*"
      And I have "2" "regular" games, where league level = "1st"
      And I open main page of mobile site
      And I login under account on mobile site
      And I place bet to one game with league level = "*"
      And I place bet to one game with league level = "1st"
      And I click to "betslip" button in header
     When I click to bet button
     Then I see "please select more 1 bet(s)" message
      And Ticket not created and balance "100"
      But if i select one more game
      And I click to "betslip" button in header
      And I click to bet button
     Then Ticket is created and balance "99"

  Scenario: Check min games amount options in league level configuration
    Given I have "3" "regular" games, where league level = "*" and first quota "1.5"
      And League level "*" have limit "Min games amount" is "3"
      And I open main page of mobile site
      And I login under account on mobile site
     When I place bet to "two" games
      And I click to "betslip" button in header
      And I click to bet button
     Then I see "please select more 1 bet(s)" message
      And Ticket not created and balance "100"
      But if i select 3 games
      And I click to "betslip" button in header
      And I click to bet button
     Then Ticket is created and balance "99"

#---------------------
# Проверка Min quota и Max quota
#---------------------

  Scenario: Ticket quota should be greater or equal minimal league quota from league limits
    Given I have "2" "regular" games, where league level = "*" and first quota "1.1"
      And I have "1" "regular" games, where league level = "*" and first quota "3"
      And League level "*" have limit "Min quota" is "2"
      And I open main page of mobile site
      And I login under account on mobile site
     When I place bet to "two" games
      And I click to "betslip" button in header
      And I click to bet button
     Then I see "Your bet slip includes match that requires total quota value greater than 2" message
      And Ticket not created and balance "100"
      But If i place bet for ticket quota more 2
      And I click to "betslip" button in header
      And I click to bet button
     Then Ticket is created and balance "99"


#Scenario: Квота тикета не может привышать значение Max quota
  Scenario: Ticket quota can't be greater that Max quota limit and all bonuses calculated after max quota limit
    Given I have "10" "regular" games, where league level = "*" and first quota "2"
      And League level "*" have limit "Max quota" is "500"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "5"
      And I click to bet button
     Then Ticket is created and balance "95"
      And All bets in ticket is win
#      And I login under account
      And I open main page of mobile site
      And Ticket detail is: payoff "3000", bonus "500", stake "5 EUR"

  Scenario: Max quota limit does not do limit if ticket quota is less
    Given I have "8" "regular" games, where league level = "*" and first quota "2"
      And League level "*" have limit "Max quota" is "50000"
      And I open main page of mobile site
      And I login under account on mobile site
     When I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "5"
      And I click to bet button
     Then Ticket is created and balance "95"
      And All bets in ticket is win
      And I open main page of mobile site
      And Ticket detail is: payoff "1408", bonus "128", stake "5 EUR"

#---------------------
#Проверка Min stake и Max stake
#---------------------
  Scenario: User stake should be greater or equal league min stake
    Given I have "2" "regular" games, where league level = "*"
      And League level "*" have limit "Min stake EUR" is "3"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
     When I set stake "2"
      And I click to bet button
     Then I see "stake has to be greater than 3" message
      And Ticket not created and balance "100"
      And I click to "betslip" button in header
      But If i set stake "3"
      And I click to bet button
     Then Ticket is created and balance "97"

  Scenario: Stake can be less then Max stake
    Given I have "2" "regular" games, where league level = "*"
      And League level "*" have limit "Max stake EUR" is "500"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
      And I set stake "55"
     When I click to bet button
     Then Ticket is created and balance "45"

  Scenario: Stake can not be greater then Max Stake
    Given I have "2" "regular" games, where league level = "*"
      And League level "*" have limit "Max stake EUR" is "50"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each" game
      And I click to "betslip" button in header
      And I set stake "55"
     When I click to bet button
     Then I see "Maximum stake is 50" message
      And Ticket not created and balance "100"
      And I click to "betslip" button in header
      But If i set stake "45"
      And I click to bet button
     Then Ticket is created and balance "55"

#---------------------
#Проверка Max payoff
#---------------------
  Scenario: User can not create ticket if maximum winning sum greater then Maximum payoff limit
    Given I have "3" "regular" games, where league level = "*" and first quota "5"
      And League level "*" have limit "Max payoff EUR" is "1000"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "50"
      And Possible winning is "6'250.00"
     When I click to bet button
     Then I see "maximum stake is 8" message
      And Possible winning is "1'000.00"
      And Ticket not created and balance "100"
      And I click to "betslip" button in header
      But If i set stake "7"
      And I set stake "8"
      And I click to bet button
     Then Ticket is created and balance "92"

#  Scenario: Бонусы начисляются после расчета Max payoff, т.е. макс выигрыш может быть больше макс payoff за счет бонусов
  Scenario: Check that bonuses calculated after max pay off limit
    Given I have "6" "regular" games, where league level = "*" and first quota "2"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "15"
     When I click to bet button
     Then Ticket is created and balance "85"
      And All bets in ticket is win
      And I open main page of mobile site
     Then Ticket detail is: payoff "1008", bonus "48", stake "15 EUR"

#---------------------
#Проверка Maximum risk per odds tip
#---------------------
#  Scenario: Ставка на исход матча не принимаются, если сумма выиграшей ставой на один и тот же исход больше либо равна Maximum risk per odds tip
  Scenario: User can't place bet to odd if total sum of bets to odd is greater then 'Maximum risk per odds tip' limit
    Given I have "1" "regular" games, where league level = "*" and first quota "5"
      And League level "*" have limit "Maximum risk per odds tip EUR" is "100"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "50"
      And I click to bet button
      And Ticket is created and balance "50"
     When I have one more account
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "5"
      And I click to bet button
     Then I see "bet is locked" message
      And Ticket not created and balance "100"

#---------------------
#Проверка Maximum risk per match
#---------------------
#  Scenario: Ставки на матч не принимаются, если сумма выиграшей всех тикетов на матч больше либо равна Maximum risk per odds tip
  Scenario: User can't place bet to odd if total sum of bets to odd is greater then 'Maximum risk per match' limit
    Given I have "1" "regular" games, where league level = "*" and first quota "5"
      And League level "*" have limit "Maximum risk per match EUR" is "100"
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "50"
      And I click to bet button
      And Ticket is created and balance "50"
     When I have one more account
      And I open main page of mobile site
      And I login under account on mobile site
      And I select odd in "each first" game
      And I click to "betslip" button in header
      And I set stake "5"
      And I click to bet button
     Then I see "bet is locked" message
      And Ticket not created and balance "100"