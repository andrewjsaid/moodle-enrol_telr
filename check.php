<?php
/**
 * This script is run on user returning from Telr.
 * It checks the state of the transaction and enrols the user or emails the admin.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Eugene Venter and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine This script does not require login.
require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('telr')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_telr');
}


$id = required_param('id', PARAM_INT);
$pd = $DB->get_record("enrol_telr_pending", array('id'=>$id), "*", MUST_EXIST);
if($pd->status != 1) {
    redirect(new moodle_url('/course/view.php', array('id'=>$pd->courseid)));
}

$user = $DB->get_record("user", array("id" => $pd->userid), "*", MUST_EXIST);
$course = $DB->get_record("course", array("id" => $pd->courseid), "*", MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_context($context);

$plugin_instance = $DB->get_record("enrol", array("id" => $pd->instanceid, "enrol" => "telr", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('telr');



// Now we check the transaction from Telr's side
$c = new curl();
$telrdomain = 'secure.telr.com';
$options = array(
    'httpheader' => array('application/x-www-form-urlencoded', "Host: $telrdomain"),
    'timeout' => 30,
    'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
);
$location = "https://$telrdomain/gateway/order.json";
$telrreq = array(
    'ivp_method'    => 'check',
    'ivp_store'     => $plugin->get_config('storeid'),
    'ivp_authkey'   => $plugin->get_config('authkey'),
    'order_ref'     => $pd->orderref
    
);
$result = $c->post($location, $telrreq, $options);

if ($c->get_errno()) {
    throw new moodle_exception('errtelr', 'enrol_telr', '', null, $result);
}

$jsonResult = json_decode($result);
if(isset($jsonResult->error)) {
    throw new moodle_exception('errtelr', 'enrol_telr', '', null, $result);
}

$pd->lasttimechecked = time();
$pd->lastorderstatuscode = $result->order->status->code;
$pd->lastorderstatus = $result->order->status->text;
$DB->update_record('enrol_telr_pending', $pd);



if ($pd->lastorderstatuscode < 0) { // Expired, Cancelled or Declined    
    // Ununrol user
    $plugin->unenrol_user($plugin_instance, $pd->userid);
    \enrol_telr\util::message_telr_error_to_admin(
        "Status not completed or pending. User unenrolled from course", $pd);
    die;
}

// If status is pending and reason is other than echeck then we are on hold until further notice
// Email user to let them know. Email admin.

if ($pd->lastorderstatuscode == 1 || $pd->lastorderstatuscode == 2) { // Pending or Authorised 
    $eventdata = new \core\message\message();
    $eventdata->courseid          = empty($pd->courseid) ? SITEID : $pd->courseid;
    $eventdata->modulename        = 'moodle';
    $eventdata->component         = 'enrol_telr';
    $eventdata->name              = 'telr_enrolment';
    $eventdata->userfrom          = get_admin();
    $eventdata->userto            = $user;
    $eventdata->subject           = "Moodle: Telr payment";
    $eventdata->fullmessage       = "Your Telr payment is pending.";
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';
    $eventdata->smallmessage      = '';
    message_send($eventdata);

    \enrol_telr\util::message_telr_error_to_admin("Payment pending - manual check required", $pd);
    die;
}

// At this point we only proceed with a status of completed or pending with a reason of echeck

// Make sure this transaction doesn't exist already.
if ($existing = $DB->get_record("enrol_telr", array("orderref" => $pd->orderref), "*", IGNORE_MULTIPLE)) {
    \enrol_telr\util::message_telr_error_to_admin("Transaction $pd->orderref is being repeated!", $pd);
    die;
}

if($pd->lastorderstatuscode != 3) {
    \enrol_telr\util::message_telr_error_to_admin("Unexpected transaction code $pd->lastorderstatuscode", $pd);
    die;

}
// User has paid, transaction is good

$d = new stdClass();
$d->storeid = $pd->storeid;
$d->courseid = $pd->courseid;
$d->userid = $pd->userid;
$d->instanceid = $pd->instanceid;
$d->timeupdated = time();

// Record all details of the order
$d->orderref = $result->order->ref;
$d->test = $result->order->test;
$d->amount = $result->order->amount;
$d->currency = $result->order->currency;
$d->description = $result->order->description;
$d->statuscode = $result->order->status->code;
$d->statustext = $result->order->status->text;

if(!empty($result->transaction)) {
    $d->transactionref = $result->transaction->ref;
    $d->transactiontype = $result->transaction->type;
    $d->transactionstatus = $result->transaction->status;
    $d->transactioncode = $result->transaction->code;
    $d->transactionmessage = $result->transaction->message;
}

$d->customeremail = $result->customer->email;
$d->customertitle = $result->customer->name->title;
$d->customerfirstname = $result->customer->name->forenames;
$d->customersurname = $result->customer->name->surname;

if(!empty($result->address)) {
    $d->addressline1 = $result->address->line1;
    $d->addressline2 = $result->address->line2;
    $d->addressline3 = $result->address->line3;
    $d->addresscity = $result->address->city;
    $d->addressstate = $result->address->state;
    $d->addresscountry = $result->address->country;
    $d->addressareacode = $result->address->areacode;
}

// If currency is incorrectly set then someone maybe trying to cheat the system
if ($d->currency != $plugin_instance->currency) {
    \enrol_telr\util::message_telr_error_to_admin(
        "Currency does not match course settings, received: ".$d->currency, $d);
    die;
}

// Check that amount paid is the correct amount
if ( (float) $plugin_instance->cost <= 0 ) {
    $cost = (float) $plugin->get_config('cost');
} else {
    $cost = (float) $plugin_instance->cost;
}

// Use the same rounding of floats as on the enrol form.
$cost = format_float($cost, 2, false);

if ($d->amount < $cost) {
    \enrol_telr\util::message_telr_error_to_admin("Amount paid is not enough ($d->amount < $cost))", $d);
    die;
}

// ALL CLEAR !

$DB->insert_record("enrol_telr", $d);

if ($plugin_instance->enrolperiod) {
    $timestart = time();
    $timeend   = $timestart + $plugin_instance->enrolperiod;
} else {
    $timestart = 0;
    $timeend   = 0;
}

// Enrol user
$plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);

// Pass $view=true to filter hidden caps if the user cannot see them
if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                        '', '', '', '', false, true)) {
    $users = sort_by_roleassignment_authority($users, $context);
    $teacher = array_shift($users);
} else {
    $teacher = false;
}

$mailstudents = $plugin->get_config('mailstudents');
$mailteachers = $plugin->get_config('mailteachers');
$mailadmins   = $plugin->get_config('mailadmins');
$shortname = format_string($course->shortname, true, array('context' => $context));


if (!empty($mailstudents)) {
    $a = new stdClass();
    $a->coursename = format_string($course->fullname, true, array('context' => $context));
    $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

    $eventdata = new \core\message\message();
    $eventdata->courseid          = $course->id;
    $eventdata->modulename        = 'moodle';
    $eventdata->component         = 'enrol_telr';
    $eventdata->name              = 'telr_enrolment';
    $eventdata->userfrom          = empty($teacher) ? core_user::get_noreply_user() : $teacher;
    $eventdata->userto            = $user;
    $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
    $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';
    $eventdata->smallmessage      = '';
    message_send($eventdata);

}

if (!empty($mailteachers) && !empty($teacher)) {
    $a->course = format_string($course->fullname, true, array('context' => $context));
    $a->user = fullname($user);

    $eventdata = new \core\message\message();
    $eventdata->courseid          = $course->id;
    $eventdata->modulename        = 'moodle';
    $eventdata->component         = 'enrol_telr';
    $eventdata->name              = 'telr_enrolment';
    $eventdata->userfrom          = $user;
    $eventdata->userto            = $teacher;
    $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
    $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';
    $eventdata->smallmessage      = '';
    message_send($eventdata);
}

if (!empty($mailadmins)) {
    $a->course = format_string($course->fullname, true, array('context' => $context));
    $a->user = fullname($user);
    $admins = get_admins();
    foreach ($admins as $admin) {
        $eventdata = new \core\message\message();
        $eventdata->courseid          = $course->id;
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'enrol_telr';
        $eventdata->name              = 'telr_enrolment';
        $eventdata->userfrom          = $user;
        $eventdata->userto            = $admin;
        $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
        $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }
}

redirect(new moodle_url('/enrol/telr/return.php', array('id'=>$course->id)));
