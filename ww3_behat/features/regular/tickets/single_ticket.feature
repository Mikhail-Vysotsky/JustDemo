Feature: Single ticket type

#---------------------
# before each scenario steps
#---------------------
  Background:
    Given I am start new browser session
      And I have account with "100" "EUR" in balance
#      And I login under account

# Single тикет может содержать только игры, где league level = "*"
# Single тикет может содержать regular и outright игры
# Single тикет выиграл, если выиграла хотя бы одна ставка тикета
# Выигрыш по тикету равен сумме выигрышей по ставкам

#---------------------
# Check available single ticket cases
#---------------------
#  Scenario: "Single" тип тикета не может содержать игр, требующих больше "1" ставки
  Scenario: "Single" ticket can not contain games where need more one bets
    Given I have "3" "regular" games, where league level = "1st"
      And I login under account
     When I select bets to "each" games
     Then "single" not available

#  Scenario: "Single" тикет не может содержать 2 игры с типом, требующем "1" ставку и 1 игру с типом, требующем "3" ставки
  Scenario: "Single" ticket can not contain two games where league level is '*' and one game where league level is '1st'
    Given I have "1" "regular" games, where league level = "1st"
      And I have "2" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" not available

  Scenario: "Single" ticket can not contain outright game from '*' league and regular games from '1st' league
    Given I have "2" "regular" games, where league level = "1st"
      And I have "1" "outright" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" not available

#  Scenario: "Single" тикет  может содержать 1 игру с типом, требующем "1" ставку
  Scenario: "Single" ticket can contain one regular game where league = '*'
    Given I have "1" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" available
      And I can create "single" ticket

#  Scenario: "Single" тикет  может содержать 1 outright с типом, требующем "1" ставку
  Scenario: "Single" ticket can contain one outright game where league = '*'
    Given I have "1" "outright" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" available
      And I can create "single" ticket

#  Scenario: "Single" тикет  может содержать 1 outright и 1 regular игру
  Scenario: "Single" ticket can contain outright and regular games if league level = '*'
    Given I have "1" "regular" games, where league level = "*"
      And I have "1" "outright" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" available
      And I can create "single" ticket

#  Scenario: "Single" тикет  может содержать несколько outright игр
  Scenario: "Single" can contain several outright games from '*' league
    Given I have "3" "outright" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" available
      And I can create "single" ticket

#  Scenario: "Single" тикет  может содержать несколько regular игр, требующими "1" ставку
  Scenario: "Single" can contain several regular games from '*' league
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "single" available
      And I can create "single" ticket

#---------------------
# Check quota calculation
#---------------------
  Scenario: "Single" ticket quota is equal to arithmetical mean of bets quota
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then Single ticket quota is arithmetical mean of bets quota
      And I can create "single" ticket

  Scenario: "Single" ticket quota is equal to bet quota if user make only one bet
    Given I have "1" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then Ticket quota is equal to bet quota

#---------------------
# Check win conditions
#---------------------
#  Scenario: "Single" тикет считается выигрышным, если хотя бы один одна ставка выиграла
  Scenario: "Single" ticket win, if one of bet is win
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I create "single" ticket
     When One game win but other games is lose
     Then Check in public interface that "single" ticket is "won"

#  Scenario: "Single" тикет считается выигрышным, если все ставки выиграли
  Scenario: "Single" ticket win if all bets is win
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I create "single" ticket
     When All bets in ticket is win
     Then Check in public interface that "single" ticket is "won"

#  Scenario: "Single" тикет считается проигрышным, если все ставки проиграли
  Scenario: "Single" ticket lose if all bets lose
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I create "single" ticket
     When All bets in ticket is lose
     Then Check in public interface that "single" ticket is "lost"

#---------------------
# Check payoff calculation
#---------------------
#  Scenario: Выигрыш одной ставке тикета равен ставке на тикет деленной на количество ставок тикета и умноженому на квоту выигранной ставки.
  Scenario:  Ticket payoff on one bet of ticket is equal to ticket stake devided on count of bet and multiply on bet quota
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I set stake "15"
      And I create "single" ticket
     When One game win but other games is lose
     Then Won amount is equal to won bet multiply to 1/3 of ticket stake

#  Scenario: Выигрыш по тикету равен сумме выигрышей по ставкам
  Scenario: Ticket won amount is equal to sum of wons by each bets
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I set stake "15"
      And I create "single" ticket
     When One game win but other games is lose
     Then Win by ticket is equal to sum of each win bets
