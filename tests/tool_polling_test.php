<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * File containing tests for tool_polling.
 *
 * @package     tool_polling
 * @category    test
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The tool_polling test class.
 *
 * @package    tool_polling
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_polling_tool_polling_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();
        set_config('enabled', 1, 'tool_polling');
    }

    public function test_is_enabled() {
        $this->assertTrue(tool_polling_notification::is_enabled());
    }

    public function test_add_for_user() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        tool_polling_notification::add_for_user($user1->id, 'refreshMessages');
        $this->assertTrue($DB->record_exists('tool_polling', ['userid' => $user1->id]));
        $this->assertFalse($DB->record_exists('tool_polling', ['userid' => $user2->id]));
    }

    public function test_add_for_users() {
        global $DB;
        $this->assertTrue(tool_polling_notification::is_enabled());

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        tool_polling_notification::add_for_users([$user1->id, $user2->id], 'refreshMessages');
        $this->assertTrue($DB->record_exists('tool_polling', ['userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('tool_polling', ['userid' => $user2->id]));
        $this->assertFalse($DB->record_exists('tool_polling', ['userid' => $user3->id]));
    }

    public function test_get_all() {

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        tool_polling_notification::add_for_user($user1->id, 'refreshMessages');

        $this->assertCount(1, tool_polling_notification::get_all($user1->id));

        $this->assertCount(0, tool_polling_notification::get_all($user2->id));
    }

    public function test_cleanup() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        tool_polling_notification::add_for_user($user1->id, 'refreshMessages');
        tool_polling_notification::add_for_user($user2->id, 'refreshMessages');
        $record = $DB->get_record('tool_polling', ['userid' => $user1->id]);
        $DB->update_record('tool_polling', ['id' => $record->id, 'timecreated' => time() - DAYSECS]);

        ob_start();
        /** @var \tool_polling\task\cleanup_task $task */
        $task = \core\task\manager::get_scheduled_task(tool_polling\task\cleanup_task::class);
        $task->execute();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('Done', trim($output));
        $this->assertFalse($DB->record_exists('tool_polling', ['userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('tool_polling', ['userid' => $user2->id]));
    }
}
