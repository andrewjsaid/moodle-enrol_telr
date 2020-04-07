<?php
/**
 * Strings for component 'enrol_telr', language 'en'.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Eugene Venter and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Assign role';
$string['authkey'] = 'Telr auth key';
$string['authkey_desc'] = 'The auth key of your Telr account';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during Telr enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['errdisabled'] = 'The Telr enrolment plugin is disabled and does not handle payment notifications.';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:telr_enrolment'] = 'Telr enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['telr:config'] = 'Configure Telr enrol instances';
$string['telr:manage'] = 'Manage enrolled users';
$string['telr:unenrol'] = 'Unenrol users from course';
$string['telr:unenrolself'] = 'Unenrol self from the course';
$string['telraccepted'] = 'Telr payments accepted';
$string['pluginname'] = 'Telr';
$string['pluginname_desc'] = 'The Telr module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['processexpirationstask'] = 'Telr enrolment send expiry notifications task';
$string['sendpaymentbutton'] = 'Send payment via Telr';
$string['status'] = 'Allow Telr enrolments';
$string['status_desc'] = 'Allow users to use Telr to enrol into a course by default.';
$string['storeid'] = 'Telr store id';
$string['storeid_desc'] = 'The store id of your Telr account';
$string['testmode'] = 'Telr test mode';
$string['transactions'] = 'Telr transactions';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';

$string['privacy:metadata:enrol_telr:enrol_telr'] = 'Information about the Telr transactions for Telr enrolments.';
$string['privacy:metadata:enrol_telr:enrol_telr:storeid'] = 'Internal identifier for the merchant.';
$string['privacy:metadata:enrol_telr:enrol_telr:courseid'] = 'The ID of the course that is sold.';
$string['privacy:metadata:enrol_telr:enrol_telr:instanceid'] = 'The ID of the enrolment instance in the course.';
$string['privacy:metadata:enrol_telr:enrol_telr:userid'] = 'The ID of the user who bought the course enrolment.';
$string['privacy:metadata:enrol_telr:enrol_telr:timeupdated'] = 'The time of Moodle being notified by Telr about the payment.';
$string['privacy:metadata:enrol_telr:enrol_telr:orderref'] = 'Telr generated reference for the transaction.';
$string['privacy:metadata:enrol_telr:enrol_telr:amount'] = 'The amount paid in this transaction.';
$string['privacy:metadata:enrol_telr:enrol_telr:currency'] = 'The currency of this transaction.';
$string['privacy:metadata:enrol_telr:enrol_telr:description'] = 'A name of the course.';
$string['privacy:metadata:enrol_telr:enrol_telr:statuscode'] = 'Telr order status code.';
$string['privacy:metadata:enrol_telr:enrol_telr:statustext'] = 'Telr order status text.';
$string['privacy:metadata:enrol_telr:enrol_telr:transactionref'] = 'Telr transaction reference.';
$string['privacy:metadata:enrol_telr:enrol_telr:transactiontype'] = 'Telr transaction type.';
$string['privacy:metadata:enrol_telr:enrol_telr:transactionstatus'] = 'Telr transaction status.';
$string['privacy:metadata:enrol_telr:enrol_telr:transactioncode'] = 'Telr authorization/error code.';
$string['privacy:metadata:enrol_telr:enrol_telr:transactionmessage'] = 'Telr authorization/error message.';
$string['privacy:metadata:enrol_telr:enrol_telr:customeremail'] = 'Email of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:customertitle'] = 'Title of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:customerfirstname'] = 'First Name of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:customersurname'] = 'Surname of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addressline1'] = 'Address line 1 of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addressline2'] = 'Address line 2 of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addressline3'] = 'Address line 3 of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addresscity'] = 'City of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addressstate'] = 'State of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addresscountry'] = 'Country of the user as entered into Telr.';
$string['privacy:metadata:enrol_telr:enrol_telr:addressareacode'] = 'Area Code of the user as entered into Telr.';

$string['privacy:metadata:enrol_telr:enrol_telr_pending'] = 'Information about the Telr transactions for Telr enrolments, to keep track of transactions which may not be completed.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:storeid'] = 'Internal identifier for the merchant.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:status'] = 'The status of the process (including started, pending, completed).';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:timecreated'] = 'The time when the payment process began.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:courseid'] = 'The ID of the course that is sold.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:userid'] = 'The ID of the user who bought the course enrolment.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:instanceid'] = 'The ID of the enrolment instance in the course.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:orderref'] = 'Telr generated reference for the transaction.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:lasttimechecked'] = 'The last time when the transaction was queried by moodle.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:lastorderstatuscode'] = 'The payment status code reported by Telr at the point the payment was last checked.';
$string['privacy:metadata:enrol_telr:enrol_telr_pending:lastorderstatus'] = 'The payment status text reported by Telr at the point the payment was last checked.';

$string['privacy:metadata:enrol_telr:telr_com'] = 'The Telr enrolment plugin transmits user data from Moodle to the Telr website.';
$string['privacy:metadata:enrol_telr:telr_com:address'] = 'Address of the user who is buying the course.';
$string['privacy:metadata:enrol_telr:telr_com:cart'] = 'A hyphen-separated string that contains ID of the user (the buyer), ID of the course, ID of the enrolment instance.';
$string['privacy:metadata:enrol_telr:telr_com:city'] = 'City of the user who is buying the course.';
$string['privacy:metadata:enrol_telr:telr_com:country'] = 'Country of the user who is buying the course.';
$string['privacy:metadata:enrol_telr:telr_com:email'] = 'Email address of the user who is buying the course.';
$string['privacy:metadata:enrol_telr:telr_com:first_name'] = 'First name of the user who is buying the course.';
$string['privacy:metadata:enrol_telr:telr_com:last_name'] = 'Last name of the user who is buying the course.';
