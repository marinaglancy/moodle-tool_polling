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
 * Poll for updates.
 *
 * @package     tool_polling
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);
// @codingStandardsIgnoreLine This script does not require login.
require_once(__DIR__ . '/../../../config.php');

// We do not want to call require_login() here because we don't want to update 'lastaccess' and keep session alive.
$fromid = optional_param('fromid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$token = optional_param('token', '', PARAM_RAW);

if (!tool_polling_notification::is_enabled()) {
    echo json_encode(['error' => 'Polling is not enabled']);
    exit;
}

if ($pollurl = get_config('tool_polling', 'pollurl')) {
    $requesturl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $requesturl = preg_replace('|\\?.*$|', '', $requesturl);
    if ($requesturl !== $pollurl) {
        echo json_encode(['error' => 'Polling is not availabe on this URL']);
        exit;
    }
}

core_php_time_limit::raise();

while (true) {
    if (!tool_polling_notification::validate_token($userid, $token)) {
        // User is no longer logged in or token is wrong. Do not poll any more.
        // We check this in a loop becauser user session may end while we are still waiting.
        echo json_encode(['error' => 'Can not find an active user session']);
        exit;
    }
    if ($notifications = tool_polling_notification::get_all((int)$userid, (int)$fromid)) {
        // We have some notifications for this user - return them. The JS will then create a new request.
        echo json_encode(['success' => 1, 'results' => array_values($notifications)]);
        exit;
    }
    // Nothing new for this user. Sleep and check again.
    sleep(1);
}
