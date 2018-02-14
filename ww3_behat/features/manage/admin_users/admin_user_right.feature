Feature: admin user rights
  Background:
    Given I am start new browser session
#-------------------------------------------------------------
# Admin
#-------------------------------------------------------------
  Scenario: Admin can open each menu item
    Given I have "admin" user
      And I have full backoffice menu items list
     When I login under "admin" user
     Then Available "all" menu items

#-------------------------------------------------------------
# Game manager
#-------------------------------------------------------------
  Scenario: Game manager can view only available url for him
    Given I have "Game manager" user
      And I have full backoffice menu items list
     When I login under "Game manager" user
     Then Available only "Game manager" menu items
      And Excluded menu items does not available for "Game manager"
#      And "Game manager" can not open not available for him menu items by prof-link

#-------------------------------------------------------------
# Ticket manager
#-------------------------------------------------------------
  Scenario: Ticket manager can view only available url for him
    Given I have "Ticket manager" user
      And I have full backoffice menu items list
     When I login under "Ticket manager" user
     Then Available only "Ticket manager" menu items
      And Excluded menu items does not available for "Ticket manager"
#      And "Ticket manager" can not open not available for him menu items by prof-link

#-------------------------------------------------------------
# Risk officer
#-------------------------------------------------------------
  Scenario: Risk officer can view only available url for him
    Given I have "Risk officer" user
      And I have full backoffice menu items list
     When I login under "Risk officer" user
     Then Available only "Risk officer" menu items
      And Excluded menu items does not available for "Risk officer"
#      And "Risk officer" can not open not available for him menu items by prof-link

#-------------------------------------------------------------
# Financial manager
#-------------------------------------------------------------
  Scenario: Financial manager can view only available url for him
    Given I have "Financial manager" user
      And I have full backoffice menu items list
     When I login under "Financial manager" user
     Then Available only "Financial manager" menu items
      And Excluded menu items does not available for "Financial manager"
#      And "Financial manager" can not open not available for him menu items by prof-link

