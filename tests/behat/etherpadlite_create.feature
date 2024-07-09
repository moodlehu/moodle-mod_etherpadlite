@mod @mod_etherpadlite
Feature: Show the etherpadlite activity link on course page
  In order to edit the etherpadlite content
#   As a teacher
#   I need to see the button "Edit content"

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | student1 | Student   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: See the etherpadlite activity link as teacher
    # Set up a etherpadlite.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a etherpadlite activity to course "Course 1" section "1" and I fill the form with:
      | Name               | testpad1          |
      | Etherpadlite Intro | Intro to Testpad2 |

    # Should See the etherpadlite and the button
    Then I should see "testpad1"
