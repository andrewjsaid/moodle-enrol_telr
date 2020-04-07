<?php
/**
 * Telr script ran after payment process is complete.
 * 
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record("course", array("id"=>$id))) {
    redirect($CFG->wwwroot);
}

$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_context($context);

require_login();

if (!empty($SESSION->wantsurl)) {
    $destination = $SESSION->wantsurl;
    unset($SESSION->wantsurl);
} else {
    $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
}

$fullname = format_string($course->fullname, true, array('context' => $context));

if (is_enrolled($context, NULL, '', true)) { // TODO: use real paypal check (AJS: what does this todo mean?)
    redirect($destination, get_string('paymentthanks', '', $fullname));

} else {   /// Somehow they aren't enrolled yet!  :-(
    $PAGE->set_url($destination);
    echo $OUTPUT->header();
    $a = new stdClass();
    $a->teacher = get_string('defaultcourseteacher');
    $a->fullname = $fullname;
    notice(get_string('paymentsorry', '', $a), $destination);
}


