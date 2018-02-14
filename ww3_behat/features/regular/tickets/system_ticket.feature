Feature: System ticket type

#---------------------
# Выполняется перед каждым сценарием
#---------------------
  Background:
    Given I am start new browser session
    And I have account with "100" "EUR" in balance

# System тикет для POS доступен, если включена настройка 'Allow system bet'
# Bank для system тикета доступен для POS, если включена настройка 'Allow extended system bet'
# System тикет доступен, если сделано не менее 3 и не более 10 ставок
# System тикет доступен если в тикете только regular игры
# Бонусы для system тикета не доступны
# Ставка с пометкой Bank обязательно должна выиграть
# Для System тикета раcчитываются все возможные выигрышные для тикета варианты исхода матчей. Тикет считается выигрышным, если выиграло как минимум заявленное число вариантов.
# Выигрыш по варианту равен ставке на тикет поделенной на количество вариантов и умноженному на произведению квот ставок варианта
# Выигрыш по тикету равен сумме выигрышей по всем выигранным вариантам

#---------------------
# Проверка доступности "System" тикета
#---------------------
#  Scenario: Для POS "system" тикет доступен, если включена настройка "Allow system bet"
#    Given У меня есть POS, у которого включена настройка "Allow system bet"
#      And У меня есть "3" "regular" игры с типом, где нужна "1" ставка
#     When Я делаю ставку на "каждую" игру
#     Then "System" тикет доступен

#  Scenario: Для POS "system" тикет НЕ доступен, если выключена настройка "Allow system bet"
#    Given У меня есть POS, у которого выключена настройка "Allow system bet"
#      And У меня есть "3" "regular" игры с типом, где нужна "1" ставка
#     When Я делаю ставку на "каждую" игру
#     Then "System" тикет не доступен

  Scenario: System ticket can not contain greater than 10 games
    Given I have "11" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "10" games
      And "system" available
      And I select "system" ticket type
     When I select bet to "eleventh" games
     Then "system" not available

  Scenario: System ticket available if user select minimum 3 games
    Given I have "3" "regular" games, where league level = "*"
     When I select bet to "each" games
     Then "system" available

  Scenario: "System" available if user choose greater games then minimal games need by league level
    Given I have "6" "regular" games, where league level = "3rd"
      And I login under account
     When I select bet to "5" games
      Then "system" not available
       But If i place bet to "last" game
      Then "system" available

  Scenario: System type not available for tickets with outright games
    Given I have "3" "regular" games, where league level = "*"
      And I have "1" "outright" games, where league level = "*"
     When I select bet to "each" games
     Then "system" not available
      But If i remove "outright" game from ticket
     Then "system" available

  Scenario: User can not create system ticket from 3 outright games
   Given I have "3" "outright" games, where league level = "*"
     And I login under account
    When I select bet to "each" games
    Then "system" not available

#---------------------
# Проверка доступности "bank" у ставок
#---------------------

#  Scenario: [banking] Для POS "banking" у "system" тикета доступен, если включена настройка "Allow extended system bet"
#    Given У меня есть POS, у которого включена настройка "Allow system bet"
#      And У POS включена настройка "Allow extended system bet"
#      And У меня есть "3" "regular" игры с типом, где нужна "1" ставка
#     When Я делаю ставку на "каждую" игру
#     Then "System" тикет доступен
#      And Я могу выбрать "bank" у сделаных ставок
#
#  Scenario: [banking] Для POS "banking" у "system" тикета НЕ доступен, если выключена настройка "Allow system bet"
#    Given У меня есть POS, у которого выключена настройка "Allow system bet"
#      And У POS выключена настройка "Allow extended system bet"
#      And У меня есть "3" "regular" игры с типом, где нужна "1" ставка
#     When Я делаю ставку на "каждую" игру
#     Then "System" тикет доступен
#      And Я не могу выбрать "bank" у сделаных ставок

#---------------------
# Проверка что бонусы не доступны
#---------------------
  Scenario: Bonuses not available for system type tickets
    Given I have "6" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each" games
     When I select "system" ticket type
     Then bonuses "0" available
      But I select "multi" ticket type
     Then bonuses "5" available

#---------------------
# Проверка условия выигрыша тикета
#---------------------

  Scenario: Ticket win if all banks win and stated win games true
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I select "2" banks
      And I create "system" ticket
     When All outcomes with banks win and stated win is true
     Then Check in public interface that "system" ticket is "won"


  Scenario: Ticket lost if all banks win but not all stated games win
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "4/5"
      And I select "3" banks
      And I create "system" ticket
     When All outcomes with banks win but other lose
     Then Check in public interface that "system" ticket is "lost"



#  Scenario: Тикет выиграл, если выиграло больше ставок, чем заявлено
  Scenario: Ticket win if win all banks and one more outcome
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "4/5"
      And I select "3" banks
      And I create "system" ticket
     When All banks and one more outcome win but other lose
     Then Check in public interface that "system" ticket is "won"

#  Scenario: Тикет проиграл, если выиграло меньше ставок, чем заявлено
  Scenario: Ticket lose if any one outcome with bank lose
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "4/5"
      And I select "3" banks
      And I create "system" ticket
     When One bank lose but other outcomes win
     Then Check in public interface that "system" ticket is "lost"

  Scenario: Ticket win if stated win games true
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I not select bankers
      And I create "system" ticket
     When First three games win but other lose
     Then Check in public interface that "system" ticket is "won"

  Scenario: Ticket win if all banks win and stated win games true
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I not select bankers
      And I create "system" ticket
     When First two game win but other lose
     Then Check in public interface that "system" ticket is "lost"

#  Scenario: [Banking] Тикет выиграл, если выиграли все banking ставки и соответствующее количество заявленных ставок
#    Given У меня есть "5" "regular" игры с типом, где нужна "1" ставка
#      And Я делаю ставку на "каждую" игру
#      And Я создаю "system" тикет, где указываю что "3/5" ставок победят, отмечаю "2" banking ставки
#     When Выиграли "3/5" ставок
#      And Выиграли все Banking ставки
#     Then Тикет выиграл
#
#  Scenario: [Banking] Тикет проиграл, если хотя бы одна banking ставка проиграла
#    Given У меня есть "5" "regular" игры с типом, где нужна "1" ставка
#      And Я делаю ставку на "каждую" игру
#      And Я создаю "system" тикет, где указываю что "3/5" ставок победят, отмечаю "2" banking ставки
#     When Выиграли "3/5" ставок
#      And Выиграла только одна Banking ставка
#     Then Тикет проиграл
#
#  Scenario: [Banking] Тикет проиграл, если выиграли все banking ставки, но в итоге выиграло меньше ставок, чем заявлено
#    Given У меня есть "5" "regular" игры с типом, где нужна "1" ставка
#      And Я делаю ставку на "каждую" игру
#      And Я создаю "system" тикет, где указываю что "3/5" ставок победят, отмечаю "2" banking ставки
#     When Выиграли 2 banking ставки
#      But Остальные ставки проиграли
#     Then Тикет проиграл

#---------------------
# Проверка расчета квот и выгрышей
#---------------------
  Scenario: Check quota calculation
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I store quota of each ticket outcome
      And I create "system" ticket
     When I open ticket
#     Then Я вижу 10 вариантов выигрыша тикета
#          |   | A | B | C | D | E |
#          | 1 | * | * | * |   |   |
#          | 2 | * | * |   | * |   |
#          | 3 | * | * |   |   | * |
#          | 4 | * |   | * | * |   |
#          | 5 | * |   | * |   | * |
#          | 6 | * |   |   | * | * |
#          | 7 |   | * | * | * |   |
#          | 8 |   | * | * |   | * |
#          | 9 |   | * |   | * | * |
#          | 10|   |   | * | * | * |
     Then I see table where exist "10" variant of ticket outcomes
      And For each variant quota is product of each win outcome quota
#      And Для каждого варианта квота равна произведению квот, выигрывающих в нем ставок
#      And Сумма выигрыша для каждого варианта равна ставке на тикет деленной на количество вариантов и умноженной на квоту варианта
#      And Максимальная сумма выгрыша по тикету равна сумме выигрышей всех вариантов
      And Maximum ticket win is a sum of all variants

  Scenario: [Banking] Check quota calculation for tickets with banks outcome
#    Given У меня есть "5" "regular" игры с типом, где нужна "1" ставка
#      And Я делаю ставку на "каждую" игру
#      And Я помечаю два тикета как "bank"
#      And Я создаю "system" тикет, где указываю что "3/5" ставок победят
#     When Я открываю тикет
#      And Вижу таблицу с выигрышными вариантами
#      And В таблице присутствуют все варианты, где выигрывают только 3 из 5 ставок
#      And Каждый из вариантов содержит все "banking" ставки
#     Then Я вижу 3 варианта выигрыша тикета
#          |   | A | B | C | D | E |
#          | 1 | b | b | * |   |   |
#          | 2 | b | b |   | * |   |
#          | 3 | b | b |   |   | * |
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I select "2" banks
      And I store quota of each ticket outcome
      And I create "system" ticket
     When I open ticket
     Then I see table where exist "3" variant of ticket outcomes
      And Each variant contain banking events and quota of each variant is product of win outcome quota
#      And For each variant win sum is sum of outcomes quota delimited to count of variants and product to variant quota
#    And Для каждого варианта квота равна произведению квот, выигрывающих в нем ставок
#      And Сумма выигрыша для каждого варианта равна ставке на тикет деленной на количество вариантов и умноженной на квоту варианта
#      And Максимальная сумма выгрыша по тикету равна сумме выигрышей всех вариантов
      And Maximum ticket win is a sum of all variants

#---------------------
# Проверка расчета выплат
#---------------------
#  Scenario: Расчет выплат при выигрыше по 3 ставкам
  Scenario: Win calculation when win 3 outcomes
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I store quota of each ticket outcome
      And I create "system" ticket
#      And Ticket have a 10 outcome variants
#        |   | A | B | C | D | E |
#        | 1 | * | * | * |   |   |
#        | 2 | * | * |   | * |   |
#        | 3 | * | * |   |   | * |
#        | 4 | * |   | * | * |   |
#        | 5 | * |   | * |   | * |
#        | 6 | * |   |   | * | * |
#        | 7 |   | * | * | * |   |
#        | 8 |   | * | * |   | * |
#        | 9 |   | * |   | * | * |
#        | 10|   |   | * | * | * |
     When First three games win and other games lose
     Then Check that ticket is "won"
      And Highlight first row where first three games win in ticket
#        |   | A | B | C | D | E |
#        | 1 | * | * | * |   |   |

  Scenario: Won calculation when to variants won
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I store quota of each ticket outcome
      And I create "system" ticket
#      |   | A | B | C | D | E |
#      | 1 | * | * | * |   |   |
#      | 2 | * | * |   | * |   |
#      | 3 | * | * |   |   | * |
#      | 4 | * |   | * | * |   |
#      | 5 | * |   | * |   | * |
#      | 6 | * |   |   | * | * |
#      | 7 |   | * | * | * |   |
#      | 8 |   | * | * |   | * |
#      | 9 |   | * |   | * | * |
#      | 10|   |   | * | * | * |
     When First four games win and other games lose
     Then Check that ticket is "won"
      And Highlight "4" won row and ticket won amount is equal to sum of payoff won rows
#        |   | A | B | C | D | E |
#        | 1 | * | * | * |   |   |
#        | 2 | * | * |   | * |   |
#        | 4 | * |   | * | * |   |
#        | 7 |   | * | * | * |   |
#      And Won sum equal to sum of each won outcome

#  Scenario: [banking] Расчет выплат при выигрыше по 3 ставкам в тикете с двума banking ставками
  Scenario: [banking] Won calculation in ticket with 2 banks when 3 outcomes won
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I select "2" banks
      And I store quota of each ticket outcome
      And I create "system" ticket
#      And Ticket have a 3 outcome variants
#      |   | A | B | C | D | E |
#      | 1 | b | b | * |   |   |
#      | 2 | b | b |   | * |   |
#      | 3 | b | b |   |   | * |
    When First three games win and other games lose
    Then Check that ticket is "won"
#     And Highlit first row where first three games win in ticket
     And Highlight first row where first three games win in ticket
#      |   | A | B | C | D | E |
#      | 1 | b | b | * |   |   |
#      And Won sum is equal to won outcome sum

#  Scenario: [banking] Расчет выплат при выигрыше по 4 ставкам в тикете с двумя banking ставками
  Scenario: [banking] Won calculation in ticket with 2 banks when 4 outcomes won
    Given I have "5" "regular" games, where league level = "*"
      And I login under account
      And I select bet to "each first" games
      And I switch ticket to "system" type
      And I set ticket special value "3/5"
      And I select "2" banks
      And I store quota of each ticket outcome
      And I create "system" ticket
#      And Ticket have a 3 outcome variants
#      |   | A | B | C | D | E |
#      | 1 | b | b | * |   |   |
#      | 2 | b | b |   | * |   |
#      | 3 | b | b |   |   | * |
     When First four games win and other games lose
     Then Check that ticket is "won"
      And Highlight "2" won row and ticket won amount is equal to sum of payoff won rows
#      |   | A | B | C | D | E |
#      | 1 | b | b | * |   |   |
#      | 2 | b | b |   | * |   |
#      And Won amount is equal to sum of each won variants
