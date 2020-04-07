<?php
/**
 * Privacy Subsystem implementation for enrol_telr.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_telr\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
        // Transactions store user data.
        \core_privacy\local\metadata\provider,

        // The telr enrolment plugin contains user's transactions.
        \core_privacy\local\request\plugin\provider,

        // This plugin is capable of determining which users have data within it.
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_external_location_link(
            'telr.com',
            [
                'cart'       => 'privacy:metadata:enrol_telr:telr_com:cart',
                'first_name' => 'privacy:metadata:enrol_telr:telr_com:first_name',
                'last_name'  => 'privacy:metadata:enrol_telr:telr_com:last_name',
                'address'    => 'privacy:metadata:enrol_telr:telr_com:address',
                'city'       => 'privacy:metadata:enrol_telr:telr_com:city',
                'email'      => 'privacy:metadata:enrol_telr:telr_com:email',
                'country'    => 'privacy:metadata:enrol_telr:telr_com:country',
            ],
            'privacy:metadata:enrol_telr:telr_com'
        );

        // The enrol_telr has 2 DB tables that contains user data.
        $collection->add_database_table(
                'enrol_telr',
                [
                    'storeid'             => 'privacy:metadata:enrol_telr:enrol_telr:storeid',
                    'courseid'            => 'privacy:metadata:enrol_telr:enrol_telr:courseid',
                    'userid'              => 'privacy:metadata:enrol_telr:enrol_telr:userid',
                    'instanceid'          => 'privacy:metadata:enrol_telr:enrol_telr:instanceid',
                    'timeupdated'         => 'privacy:metadata:enrol_telr:enrol_telr:timeupdated',
                    
                    'orderref'            => 'privacy:metadata:enrol_telr:enrol_telr:orderref',
                    'amount'              => 'privacy:metadata:enrol_telr:enrol_telr:amount',
                    'currency'            => 'privacy:metadata:enrol_telr:enrol_telr:currency',
                    'description'         => 'privacy:metadata:enrol_telr:enrol_telr:description',
                    'statuscode'          => 'privacy:metadata:enrol_telr:enrol_telr:statuscode',
                    'statustext'          => 'privacy:metadata:enrol_telr:enrol_telr:statustext',
                    
                    'transactionref'      => 'privacy:metadata:enrol_telr:enrol_telr:transactionref',
                    'transactiontype'     => 'privacy:metadata:enrol_telr:enrol_telr:transactiontype',
                    'transactionstatus'   => 'privacy:metadata:enrol_telr:enrol_telr:transactionstatus',
                    'transactioncode'     => 'privacy:metadata:enrol_telr:enrol_telr:transactioncode',
                    'transactionmessage'  => 'privacy:metadata:enrol_telr:enrol_telr:transactionmessage',

                    'customeremail'       => 'privacy:metadata:enrol_telr:enrol_telr:customeremail',
                    'customertitle'       => 'privacy:metadata:enrol_telr:enrol_telr:customertitle',
                    'customerfirstname'   => 'privacy:metadata:enrol_telr:enrol_telr:customerfirstname',
                    'customersurname'     => 'privacy:metadata:enrol_telr:enrol_telr:customersurname',
                    'addressline1'        => 'privacy:metadata:enrol_telr:enrol_telr:addressline1',
                    'addressline2'        => 'privacy:metadata:enrol_telr:enrol_telr:addressline2',
                    'addressline3'        => 'privacy:metadata:enrol_telr:enrol_telr:addressline3',
                    'addresscity'         => 'privacy:metadata:enrol_telr:enrol_telr:addresscity',
                    'addressstate'        => 'privacy:metadata:enrol_telr:enrol_telr:addressstate',
                    'addresscountry'      => 'privacy:metadata:enrol_telr:enrol_telr:addresscountry',
                    'addressareacode'     => 'privacy:metadata:enrol_telr:enrol_telr:addressareacode',
                ],
                'privacy:metadata:enrol_telr:enrol_telr'
        );
        
        $collection->add_database_table(
            'enrol_telr_pending',
            [
                'storeid'             => 'privacy:metadata:enrol_telr:enrol_telr:storeid',
                'status'              => 'privacy:metadata:enrol_telr:enrol_telr:status',
                'timecreated'         => 'privacy:metadata:enrol_telr:enrol_telr:timecreated',
                'courseid'            => 'privacy:metadata:enrol_telr:enrol_telr:courseid',
                'userid'              => 'privacy:metadata:enrol_telr:enrol_telr:userid',
                'instanceid'          => 'privacy:metadata:enrol_telr:enrol_telr:instanceid',
                'orderref'            => 'privacy:metadata:enrol_telr:enrol_telr:orderref',
                'lasttimechecked'     => 'privacy:metadata:enrol_telr:enrol_telr:lasttimechecked',
                'lastorderstatuscode' => 'privacy:metadata:enrol_telr:enrol_telr:lastorderstatuscode',
                'lastorderstatus'     => 'privacy:metadata:enrol_telr:enrol_telr:lastorderstatus',
            ],
            'privacy:metadata:enrol_telr:enrol_telr_pending'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {enrol_telr} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = LOWER(ep.customeremail)
                 WHERE u.id = :userid

                UNION 

                SELECT ctx.id
                  FROM {enrol_telr_pending} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid
                 WHERE u.id = :userid
                ";
        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT u.id
                  FROM {enrol_telr} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {user} u ON ep.userid = u.id OR LOWER(u.email) = LOWER(ep.customeremail)
                 WHERE e.courseid = :courseid

                 UNION

                 SELECT u.id
                  FROM {enrol_telr_pending} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {user} u ON ep.userid = u.id
                 WHERE e.courseid = :courseid
                 ";
        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('id', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT ep.*
                  FROM {enrol_telr} ep
                  JOIN {enrol} e ON ep.instanceid = e.id
                  JOIN {context} ctx ON e.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                  JOIN {user} u ON u.id = ep.userid OR LOWER(u.email) = LOWER(ep.customeremail)
                 WHERE ctx.id {$contextsql} AND u.id = :userid
              ORDER BY e.courseid";

        $params = [
            'contextcourse' => CONTEXT_COURSE,
            'userid'        => $user->id,
            'emailuserid'   => $user->id,
        ];
        $params += $contextparams;

        // Reference to the course seen in the last iteration of the loop. By comparing this with the current record, and
        // because we know the results are ordered, we know when we've moved to the Telr transactions for a new course
        // and therefore when we can export the complete data for the last course.
        $lastcourseid = null;

        $strtransactions = get_string('transactions', 'enrol_telr');
        $transactions = [];
        $telrrecords = $DB->get_recordset_sql($sql, $params);
        foreach ($telrrecords as $telrrecord) {
            if ($lastcourseid != $telrrecord->courseid) {
                if (!empty($transactions)) {
                    $coursecontext = \context_course::instance($telrrecord->courseid);
                    writer::with_context($coursecontext)->export_data(
                            [$strtransactions],
                            (object) ['transactions' => $transactions]
                    );
                }
                $transactions = [];
            }

            $transaction = (object) [
                'storeid'              => $telrrecord->storeid,
                'courseid'             => $telrrecord->courseid,
                'userid'               => $telrrecord->userid,
                'instanceid'           => $telrrecord->instanceid,
                'orderref'             => $telrrecord->orderref,
                'amount'               => $telrrecord->amount,
                'currency'             => $telrrecord->currency,
                'description'          => $telrrecord->description,
                'statuscode'           => $telrrecord->statuscode,
                'statustext'           => $telrrecord->statustext,
                'transactionref'       => $telrrecord->transactionref,
                'transactiontype'      => $telrrecord->transactiontype,
                'transactionstatus'    => $telrrecord->transactionstatus,
                'transactioncode'      => $telrrecord->transactioncode,
                'transactionmessage'   => $telrrecord->transactionmessage,
                'customeremail'        => $telrrecord->customeremail,
                'customertitle'        => $telrrecord->customertitle,
                'customerfirstname'    => $telrrecord->customerfirstname,
                'customersurname'      => $telrrecord->customersurname,
                'addressline1'         => $telrrecord->addressline1,
                'addressline2'         => $telrrecord->addressline2,
                'addressline3'         => $telrrecord->addressline3,
                'addresscity'          => $telrrecord->addresscity,
                'addressstate'         => $telrrecord->addressstate,
                'addresscountry'       => $telrrecord->addresscountry,
                'addressareacode'      => $telrrecord->addressareacode,
                'timeupdated'          => \core_privacy\local\request\transform::datetime($telrrecord->timeupdated),
            ];
            if ($telrrecord->userid == $user->id) {
                $transaction->userid = $telrrecord->userid;
            }

            $transactions[] = $telrrecord;

            $lastcourseid = $telrrecord->courseid;
        }
        $telrrecords->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($transactions)) {
            $coursecontext = \context_course::instance($telrrecord->courseid);
            writer::with_context($coursecontext)->export_data(
                    [$strtransactions],
                    (object) ['transactions' => $transactions]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('enrol_telr', array('courseid' => $context->instanceid));
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        $contexts = $contextlist->get_contexts();
        $courseids = [];
        foreach ($contexts as $context) {
            if ($context instanceof \context_course) {
                $courseids[] = $context->instanceid;
            }
        }

        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $select = "userid = :userid AND courseid $insql";
        $params = $inparams + ['userid' => $user->id];
        $DB->delete_records_select('enrol_telr', $select, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $params = ['courseid' => $context->instanceid] + $userparams;

        $select = "courseid = :courseid AND userid $usersql";
        $DB->delete_records_select('enrol_telr', $select, $params);
    }
}
