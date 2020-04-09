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
$pd->id = $DB->insert_record("enrol_telr_pending", $pd);



if ( (float) $instance->cost <= 0 ) {
    $cost = (float) $plugin->get_config('cost');
} else {
    $cost = (float) $instance->cost;
}
$cost = format_float($cost, 2, false);

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
    'ivp_amount'    => $cost,
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