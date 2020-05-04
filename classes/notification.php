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
 * Class tool_polling_notification
 *
 * @package     tool_polling
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class tool_polling_notification
 *
 * @package     tool_polling
 * @copyright   2020 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class tool_polling_notification {

    /**
     * Is polling enabled
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        return (bool)get_config('tool_polling', 'enabled');
    }

    /**
     * Add notification for user
     *
     * @param int $userid
     * @param string $event name of the Javascript PubSub event
     * @param bool $groupevents when triggering JS event, trigger only once if there are several events with this name
     * @param array $addinfo additional information to include in the PubSub event (not compatible with $groupevents)
     */
    public static function add_for_user(int $userid, string $event, bool $groupevents = false, array $addinfo = []) {
        global $DB;
        if (!self::is_enabled()) {
            return;
        }
        if ($groupevents && $addinfo) {
            throw new coding_exception('Groupped events can not have additional information');
        }
        $params = [
            'userid' => $userid,
            'event' => $event,
            'groupevents' => (int)$groupevents,
            'addinfo' => json_encode($addinfo)
        ];
        $params['timecreated'] = $params['timemodified'] = time();
        $DB->insert_record('tool_polling', $params);
    }

    /**
     * Add notification for several users
     *
     * @param array|callable $userids
     * @param string $event name of the Javascript PubSub event
     * @param bool $groupevents when triggering JS event, trigger only once if there are several events with this name
     * @param array $addinfo additional information to include in the PubSub event (not compatible with $groupevents)
     */
    public static function add_for_users($userids, string $event, bool $groupevents = false, array $addinfo = []) {
        if (!self::is_enabled()) {
            return;
        }
        if (is_callable($userids)) {
            $userids = $userids();
        }
        foreach ($userids as $userid) {
            self::add_for_user($userid, $event, $groupevents, $addinfo);
        }
    }

    /**
     * Get all notifications for a given user
     *
     * @param int $userid
     * @param string $pageurl
     * @param int $fromid
     * @return array
     */
    public static function get_all(int $userid, int $fromid = 0): array {
        global $DB;
        $notifications = $DB->get_records_select('tool_polling',
            'userid = :userid AND id > :fromid',
            ['userid' => $userid, 'fromid' => $fromid],
            'id', 'id, event, groupevents, addinfo');
        array_walk($notifications, function(&$item) {
            $item->addinfo = @json_decode($item->addinfo, true);
        });
        return $notifications;
    }

    /**
     * Get token for current user and current session
     *
     * @return string
     */
    public static function get_token() {
        global $USER;
        $sid = session_id();
        return self::get_token_for_user($USER->id, $sid);
    }

    /**
     * Get token for a given user and given session
     *
     * @param int $userid
     * @param string $sid
     * @return false|string
     */
    protected static function get_token_for_user(int $userid, string $sid) {
        return substr(md5($sid . '/' . $userid . '/' . get_site_identifier()), 0, 10);
    }

    /**
     * Validate that a token corresponds to one of the users open sessions
     *
     * @param int $userid
     * @param string $token
     * @return bool
     */
    public static function validate_token(int $userid, string $token) {
        global $DB;
        $sessions = $DB->get_records('sessions', ['userid' => $userid]);
        foreach ($sessions as $session) {
            if (self::get_token_for_user($userid, $session->sid) === $token) {
                return true;
            }
        }
        return false;
    }

    /** @var bool */
    static protected $initialised = false;

    /**
     * Initialise polling on the current page.
     */
    public static function init() {
        global $PAGE, $USER, $DB;
        if (!self::is_enabled() || !isloggedin() || isguestuser() || self::$initialised) {
            return;
        }
        $fromid = (int)$DB->get_field_sql("SELECT max(id) FROM {tool_polling} WHERE userid = ?", [$USER->id]);
        $url = get_config('tool_polling', 'pollurl') ?:
            (new moodle_url('/admin/tool/polling/poll.php'))->out(false);
        $PAGE->requires->js_call_amd('tool_polling/poll', 'init',
            [$USER->id, self::get_token(), $fromid, $PAGE->url->out_as_local_url(false), $url]);
        self::$initialised = true;
    }
}
