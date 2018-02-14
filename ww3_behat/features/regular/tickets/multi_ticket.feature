Feature: Multi ticket type

#---------------------
# Выполняется перед каждым сценарием
#---------------------
Background:
  Given I have account with "100" "EUR" in balance
#  Given I am start new browser session
#    And I have account with "100" "EUR" in balance
#    And I login under account

# Multi тикет может содержать только regular игры
# Multi тикет доступен только если в тикете присутствует минимум 2 ставки
# Multi тикет выиграл, если выиграли все ставки тикета
# Квота тикета равна произведению квот всех ставок
# Выигрыш равен произведению квоты тикета на ставку

#---------------------
# Check "multi" ticket type available
#---------------------
  Scenario: User can't create "multi" ticket with two outright games
    Given I have "2" "outright" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "multi" not available

  Scenario: User can't create "multi" ticket with outright and regular game
    Given I have "1" "outright" games, where league level = "*"
      And I have "1" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "multi" not available

#  Scenario: Тикет может иметь несколько игр, для которых нужно больше одной ставки
  Scenario: "Multi" ticket can contain several games where need more one bets
    Given I have "6" "regular" games, where league level = "1st"
      And I login under account
     When I select bets to "each" games
     Then "multi" available
      And bonuses "5" available
      And I can create "multi" ticket

#  Scenario: Тикет может иметь две игры, для каждой из которых нужна одна ставка
  Scenario: "Multi" ticket can contain two games where need one game
    Given I have "2" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "multi" available
      And I can create "multi" ticket

  Scenario: "Multi" ticket is not available if user place bet to only one game
    Given I have "1" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
     Then "multi" not available


#  Scenario: У multi тикета возможна только по одной ставке на каждую из игр
  Scenario: If user create double bet, but switch to multi ticket, then each event have only one bet
    Given I have "4" "regular" games, where league level = "*"
      And I login under account
     When I select bets to "each" games
      And I switch ticket to "double" type
      And I place double bet to "each" game
      And I switch ticket to "multi" type
     Then "multi" available
      And I can create "multi" ticket

#---------------------
# Check win condition
#---------------------
#  Scenario: Тикет не выиграл, если хотя бы одна ставка проиграла
  Scenario: "Multi" ticket lose if one of bets lose
    Given I have "4" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I create "multi" ticket
     When One game lose but other games is win
     Then Check in public interface that "multi" ticket is "lost"

  Scenario: "Multi" ticket win if each game is win
    Given I have "4" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I create "multi" ticket
     When All bets in ticket is win
     Then Check in public interface that "multi" ticket is "WAITING APPROVING"

#---------------------
# Check quota calculation
#---------------------
#  Scenario: Квота по тикету равна произведению квот каждой ставки
  Scenario: "multi" ticket quota is equals to multiply of each bets quota
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "each" games
     When I create "multi" ticket
     Then Multi ticket quota is equals to multiply of each bets quota

#---------------------
# Check win payout calculation
#---------------------
  Scenario: 'multi' ticket payoff sum is equal to multiply ticket quota and ticket stake
    Given I have "3" "regular" games, where league level = "*"
      And I login under account
      And I select bets to "first outcome in each" games
      And I set stake "5"
      And I create "multi" ticket
     When All bets in ticket is win
     Then Win by multi ticket is equal to multiply ticket quota and ticket stake