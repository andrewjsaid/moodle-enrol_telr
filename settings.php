<?php
/**
 * Telr enrolments plugin settings and presets.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Eugene Venter, Petr Skoda and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_telr_settings', '', get_string('pluginname_desc', 'enrol_telr')));

    $settings->add(new admin_setting_configtext('enrol_telr/storeid', get_string('storeid', 'enrol_telr'), get_string('storeid_desc', 'enrol_telr'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_telr/authkey', get_string('authkey', 'enrol_telr'), get_string('authkey_desc', 'enrol_telr'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('enrol_telr/testmode', get_string('testmode', 'enrol_telr'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_telr/mailstudents', get_string('mailstudents', 'enrol_telr'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_telr/mailteachers', get_string('mailteachers', 'enrol_telr'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_telr/mailadmins', get_string('mailadmins', 'enrol_telr'), '', 0));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_telr/expiredaction', get_string('expiredaction', 'enrol_telr'), get_string('expiredaction_help', 'enrol_telr'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_telr_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_telr/status',
        get_string('status', 'enrol_telr'), get_string('status_desc', 'enrol_telr'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_telr/cost', get_string('cost', 'enrol_telr'), '', 0, PARAM_FLOAT, 4));

    $telrcurrencies = enrol_get_plugin('telr')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_telr/currency', get_string('currency', 'enrol_telr'), '', 'AED', $telrcurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_telr/roleid',
            get_string('defaultrole', 'enrol_telr'), get_string('defaultrole_desc', 'enrol_telr'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_telr/enrolperiod',
        get_string('enrolperiod', 'enrol_telr'), get_string('enrolperiod_desc', 'enrol_telr'), 0));
}
