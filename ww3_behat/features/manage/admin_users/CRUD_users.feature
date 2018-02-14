Feature: Admin users page
  Background:
    Given I am start new browser session

  Scenario: Admin can create new admin user
    Given I login as admin and open admin users page
     When I click to add user button
      And I fill add user form
      And I submit add user form
     Then New user created
      And New user can login into backoffice

  Scenario: Admin can edit exist admin user
    Given I login as admin and open admin users page
      And I have any admin user
     When I open admin user
      And I edit and save user details
     Then User details changed
      And User can login after detail changed

  Scenario: Admin can deactivate any admin user
    Given I login as admin and open admin users page
      And I have any admin user
     When I find and deactivate this admin user
     Then User deactivated
      And User can not login to backoffice
