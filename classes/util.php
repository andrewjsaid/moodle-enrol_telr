<?php
/**
 * Telr enrolment plugin utility class.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Cameron Ball <cameron@cameron1729.xyz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_telr;

defined('MOODLE_INTERNAL') || die();

/**
 * PayPal enrolment plugin utility class.
 *
 * @package   enrol_paypal
 * @copyright 2016 Cameron Ball <cameron@cameron1729.xyz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class util {

    /**
     * Alerts site admin of potential problems.
     *
     * @param string   $subject email subject
     * @param stdClass $data    Telr IPN data
     */
    public static function message_telr_error_to_admin($subject, $message) {
        $admin = get_admin();
        $site = get_site();

        $eventdata = new \core\message\message();
        $eventdata->courseid          = SITEID;
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'enrol_telr';
        $eventdata->name              = 'telr_enrolment';
        $eventdata->userfrom          = $admin;
        $eventdata->userto            = $admin;
        $eventdata->subject           = "TELR ERROR: ".$subject;
        $eventdata->fullmessage       = $message;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }
}
