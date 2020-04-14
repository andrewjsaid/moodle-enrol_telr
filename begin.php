<?php
/**
 * This script is called from enrol.html to start the telr transaction.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said
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

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$repeat = required_param('repeat', PARAM_INT);

$user = $DB->get_record("user", array("id" => $userid), "*", MUST_EXIST);
$course = $DB->get_record("course", array("id" => $courseid), "*", MUST_EXIST);
$instance = $DB->get_record("enrol", array("id" => $instanceid, "enrol" => "telr", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('telr');

$pd = new stdClass();
$pd->storeid = $plugin->get_config('storeid');
$pd->status = 0;
$pd->timecreated = time();
$pd->courseid = $course->id;
$pd->userid = $user->id;
$pd->instanceid = $instance->id;
$pd->isrepeat = $repeat;
$pd->id = $DB->insert_record("enrol_telr_pending", $pd);

if ( (float) $instance->cost <= 0 ) {
    $cost = (float) $plugin->get_config('cost');
} else {
    $cost = (float) $instance->cost;
}
$original_cost = $cost;
$initial_amount = $original_cost;
$charge = 0;

if(1 == $repeat) {
    if(0 == $instance->customint1) {
        \enrol_telr\util::message_telr_error_to_admin("User attempted to make repeat billing when not enabled", $pd);
        redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
    }

    $repeat_charge        = $instance->customdec1;
    $repeat_charge_perc   = $instance->customint4;
    $repeat_initial_perc  = $instance->customdec2;
    $repeat_period        = $instance->customtext1;
    $repeat_interval      = $instance->customint2;
    $repeat_term          = $instance->customint3;

    if(is_numeric($repeat_initial_perc) && (float)$repeat_initial_perc > 0) {
        $initial_amount = ((float)$repeat_initial_perc / 100.0) * $original_cost;
    } else {
        $initial_amount = $original_cost / ($repeat_term + 1);
    }

    if(is_numeric($repeat_charge) && (float)$repeat_charge > 0) {
        $charge = (float)$repeat_charge;
    }
    if(is_numeric($repeat_charge_perc) && (float)$repeat_charge_perc > 0) {
        $charge = $charge + ((float)$repeat_charge_perc / 100.0) * $original_cost;
    }
}

/// Open a connection to Telr to get the URL
$c = new curl();
$telrdomain = 'secure.telr.com';
$options = array(
    'httpheader' => array('application/x-www-form-urlencoded', "Host: $telrdomain"),
    'timeout' => 30,
    'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
);
$location = "https://$telrdomain/gateway/order.json";
$telrreq = array(
    'ivp_method'    => 'create',
    'ivp_store'     => $plugin->get_config('storeid'),
    'ivp_authkey'   => $plugin->get_config('authkey'),
    'ivp_test'      => $plugin->get_config('testmode'),
    'ivp_amount'    => format_float($initial_amount + $charge, 2, false),
    'ivp_currency'  => $instance->currency,
    'ivp_cart'      => $pd->id,
    'ivp_desc'      => $course->shortname,
    'return_auth'   => "$CFG->wwwroot/enrol/telr/check.php?id=$pd->id",
    'return_decl'   => "$CFG->wwwroot/enrol/telr/check.php?id=$pd->id",
    'return_can'    => "$CFG->wwwroot/enrol/telr/check.php?id=$pd->id",
    'ivp_framed'    => 0,

    'bill_fname'    => $user->firstname,
    'bill_sname'    => $user->lastname,
    'bill_addr1'    => $user->address,
    'bill_city'     => $user->city,
    'bill_country'  => $user->country,
    'bill_email'    => $user->email,
);

if(1 == $repeat) {
    if(0 == $instance->customint1) {
        \enrol_telr\util::message_telr_error_to_admin("User attempted to make repeat billing when not enabled", $pd);
        redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
    }

    $repeat_charge        = $instance->customdec1;
    $repeat_charge_perc   = $instance->customint4;
    $repeat_initial_perc  = $instance->customdec2;
    $repeat_period        = $instance->customtext1;
    $repeat_interval      = $instance->customint2;
    $repeat_term          = $instance->customint3;

    $total_repeat_amount = $original_cost - $initial_amount;
    $repeat_amount = $total_repeat_amount / (float)$repeat_term;
    
    $repeat_start = '';
    if(strtolower($repeat_period) == 'w') {
        $repeat_start = date("dmY", strtotime("+" . $repeat_interval . " week", time()));
    } else if(strtolower($repeat_period) == 'm') {
        $repeat_start = date("dmY", strtotime("+" . $repeat_interval . " month", time()));

    } else{
        \enrol_telr\util::message_telr_error_to_admin("Repeat period must be m or w", $instance);
        die();
    }

    $telrreq['ivp_extra'] = 'repeat';
    $telrreq['repeat_amount'] = format_float($repeat_amount, 2, false);
    $telrreq['repeat_start'] = $repeat_start;
    $telrreq['repeat_period'] = $repeat_period;
    $telrreq['repeat_interval'] = $repeat_interval;
    $telrreq['repeat_term'] = $repeat_term;
    $telrreq['repeat_final'] = '0';
}

$result = $c->post($location, $telrreq, $options);

if ($c->get_errno()) {
    \enrol_telr\util::message_telr_error_to_admin("Could not connect to telr", $result);
    redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
}

$jsonResult = json_decode($result);
if(isset($jsonResult->error)) {
    \enrol_telr\util::message_telr_error_to_admin("Telr error message", $result);
    redirect(new moodle_url('/course/view.php', array('id'=>$courseid)));
}

$ref = $jsonResult->order->ref;

$pd->orderref = $ref;
$pd->status = 1;
$DB->update_record('enrol_telr_pending', $pd);

redirect($jsonResult->order->url);
