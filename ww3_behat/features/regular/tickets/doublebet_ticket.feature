Feature: Doublebet ticket type

# Double тикеты доступны только для 3way игр, где доступны 2 или более ставки
# Для Double тикетов расчитываются все выигрышные варианты исхода матча.
# Ставка на каждый из вариантов равна сумме ставки на тикет поделенной на количество вариантов.
# Квота для каждого из вариантов равна произведению квот исходов матчей.
# Выигрыш по тикету равен сумме выплат по каждому выигравшему варианту.


#---------------------
# Выполняется перед каждым сценарием
#---------------------
  Background:
    Given I am start new browser session
      And I have account with "100" "EUR" in balance
#      And I login under account

#---------------------
# Проверка доступности "double" тикета
#---------------------
  Scenario: Doublebet not available if user select less 3 games
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
     Then "double" not available

  #  Scenario: Нужно минимум 4 "regular" игры для создания тикета "Double"
  Scenario: Double bet is available if user select 4 bets
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I set stake "5"
     Then "double" available
      And I can switch ticket to "double" type
      And I can create "double" ticket

  Scenario: "Double" ticket can't contain outright games
    Given I have "1" "outright" games, where league level = "*"
#      And I have "1" "outright" games, where league level = "*"
      And I have "4" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
     When I select bets to "each" games
     Then "double" not available

#  Scenario: "Double" ставки можно делать только на 3way игры
  Scenario: User can't select double bet on 2way game
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I have "1" "2way" games, where league level = "*"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I switch ticket to "double" type
#      And I select "double" ticket type
      And I place double bet to "each" game
      And I set stake "5"
     Then I can't place double bet to "2way" game
      And I can create "double" ticket

#  Scenario: "Double" ставку нельзя сделать на залоченный исход
  Scenario: User can't place double bet to locket game outcome
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I have account with "500" "EUR" in balance
      And I login under account
      And "first" game have a locked outcome on "1" game
      And I open game page
     When I select bets to "each last" games
      And I switch ticket to "double" type
      And I set stake "5"
     Then I can't place double bet to "first" game
      But I place double bet to "draw outcome of first" game
      And I can create "double" ticket


#---------------------
# Проверка что бонусы не доступны
#---------------------
  Scenario: Bonuses is not available for double bets
    Given I have "6" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
     When I select bets to "each" games
      And I switch ticket to "double" type
      And I place double bet to "each" game
     Then bonuses "0" available

#---------------------
# Проверка условия выигрыша тикета
#---------------------

#  Scenario: "Double" тикет выиграл, если случился один из вариантов его исхода
  Scenario: "Double" ticket is win if one of variants win
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
      And I select bets to "first outcome in each" games
      And I switch ticket to "double" type
      And I place double bet to "last outcome in each" game
      And I set stake "10"
      And I click to create "double" ticket
     When All bets in ticket is win
     Then Check in public interface that "double" ticket is "won"
#
#  Scenario: "Double" тикет проиграл, если ни один из вариантов исхода, на который поставили не случился
  Scenario: "Double" ticket is lose if not win variants
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
      And I select bets to "first outcome in each" games
      And I switch ticket to "double" type
      And I place double bet to "last outcome in each" game
      And I set stake "10"
      And I click to create "double" ticket
     When First game win but other games is draw
     Then Check in public interface that "double" ticket is "lost"

#    Given У меня есть "4" "regular" игры с типом, где league level =  "*"
#      And Я сделал ставку на "каждую игру"
#      And Для первой и второй игр я сделал "double" ставку
#      And У меня есть 4 выигрышных варианта исхода
#    """
#    Варианты исхода:
#          |   | A | B | C | D | E |
#          | 1 | 1 | 1 | 1 | 1 | 1 |
#          | 2 | 1 | X | 1 | 1 | 1 |
#          | 3 | X | 1 | 1 | 1 | 1 |
#          | 4 | X | X | 1 | 1 | 1 |
#    """
#    When Результат по играм не совпал ни с одним из выигрышных вариантов исхода
#    """
#    Допустим результат по играм такой:
#           | A | B | C | D | E |
#           | 2 | 1 | X | 1 | 3 |
#    """
#    Then Тикет проиграл
#
#---------------------
# Проверка расчета квот
#---------------------
#  Scenario: Расчет квот
  Scenario: Check double ticket quota calculation
    Given I have "4" "regular" games, where league level = "*" and first quota "5"
      And I login under account
      And I open game page
      And I select bets to "first outcome in each" games
      And I switch ticket to "double" type
      And I place double bet to "last outcome in each" game
      And I set stake "100"
      And I store outcomes of ticket
     When I click to create "double" ticket
     Then Quota of each variant is a product of outcomes quota
      And Maximum payoff is a maximum payoff by variant

#    Given У меня есть "4" "regular" игры с типом, где league level =  "*"
#      And У меня есть "double" тикет на эти игры
#     When Я смотрю варианты исхода тикета
#     Then Квота каждого варианта равна произведению квот выигрывающих в нем ставок
#      And Квота тикета равна квоте максимального варианта деленного на количество вариантов
#

