Feature: V2 changes monitoring
  In manage v2 admin make changes
  Admin in manage v3 see changes

  Rules:
    - implemented for category feature


  Background:
    Given I am start new browser session
      And I have account with "100" "EUR" in balance


  Scenario: Admin can see in log from v3 if user disable category in v2
    Given I login as admin to v2 manage
      And I open category page
      And I found "pc-name" category
     When I disable category
     Then User can see that record about change "disable category" in v3 backoffice

#  Scenario: Admin can see in log from v3 if user delete category in v2
#    Given I login as admin to v2 manage
#      And I open category page
#      And I found "pc-name" category
#     When I delete category
#     Then User can see that record about change "delete category" in v3 backoffice

  Scenario: Admin can see in log from v3 if user rename category in v2
    Given I login as admin to v2 manage
      And I open category page
      And I found "pc-name" category
     When I rename category
     Then User can see that record about change "rename category" in v3 backoffice

  Scenario: Admin can see in log from v3 if user disable subcategory in v2
    Given I login as admin to v2 manage
      And I open category page
      And I found "pc-name" category
     When I disable subcategory
     Then User can see that record about change "disable subcategory" in v3 backoffice