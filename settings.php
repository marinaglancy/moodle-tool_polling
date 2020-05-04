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
 * Admin settings and defaults.
 *
 * @package    tool_polling
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $ajaxsettings = $ADMIN->locate('ajax');

    $ajaxsettings->add(new admin_setting_configcheckbox('tool_polling/enabled',
        new lang_string('enabled', 'tool_polling'),
        new lang_string('enableddesc', 'tool_polling'), '0'));

    $ajaxsettings->add(new admin_setting_configtext('tool_polling/pollurl',
        new lang_string('pollurl', 'tool_polling'),
        new lang_string('pollurldesc', 'tool_polling',
            (new moodle_url('/admin/tool/polling/poll.php'))->out(false)),
        '', PARAM_URL));

}
