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
 * Plugin strings are defined here.
 *
 * @package     tool_polling
 * @category    string
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['enabled'] = 'Enable polling for updates';
$string['enableddesc'] = 'When user opens a web page, the page will poll the server asking if there are any updates for this user.
Some pages may use this to update the content without requiring user to refresh the page. This can significantly improve user
experience, but at the same time increase the server load';
$string['pluginname'] = 'Polling page updates';
$string['pollurl'] = 'Alternative polling URL';
$string['pollurldesc'] = 'The URL used for polling for updates may be separated from the main web site to balance the server
load or configure different timeouts. This page may even be on a different domain, it does not need to have access to Moodle
cookies, it uses the token to validate that the user has active session. If not specified the requests will be sent to {$a}';
$string['privacy:metadata'] = 'The Polling page updates plugin only stores user notifications for 5 minutes and they do not have personal information';
$string['taskcleanup'] = 'Clean-up notifications for polling';
