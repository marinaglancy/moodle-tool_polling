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
 * Testing polling in behat
 *
 * This is not an example of how to use polling! Polling is designed to send notifications to OTHER
 * sessions and other users. This is just a test that can be executed in single-threaded behat.
 *
 * @package    tool_polling
 * @copyright  2020 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../../../config.php');

// Only continue for behat site.
defined('BEHAT_SITE_RUNNING') ||  die();

require_login();
$PAGE->set_url('/admin/tool/polling/tests/behat/fixtures/polling.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

if (optional_param('test', '', PARAM_INT)) {
    tool_polling_notification::add_for_user($USER->id, 'testevent');
    exit;
}

tool_polling_notification::init();
echo $OUTPUT->header();
$PAGE->requires->js_amd_inline(<<<EOL
    M.util.js_pending('testfile');
    require(['jquery', 'core/pubsub'], function($, PubSub) {
        $('body').on('submit', '#testform', function(e) {
            e.preventDefault();
            var ajax = new XMLHttpRequest();
            ajax.open('GET', "{$PAGE->url}?test=1", true);
            ajax.send();
        })

        PubSub.subscribe('testevent', function(e) {
            $('#pollingresults').append('Polling works!<br>');
        });
        M.util.js_complete('testfile');
    });
EOL
);

?>
<form id="testform">
    <input type="submit" name="test" value="Test polling">
</form>
<div id="pollingresults">
    <?php
    if (tool_polling_notification::is_enabled()) {
        echo "Polling is enabled!<br>";
    } else {
        echo "Polling is disabled!<br>";
    }
    ?>
</div>
<?php
echo $OUTPUT->footer();
