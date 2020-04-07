<?php
/**
 * Process expirations task.
 *
 * @package    enrol_telr
 * @copyright  2020 Andrew J Said
 * @author     Andrew J Said - based on code by Farhan Karmali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_telr\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Process expirations task.
 *
 * @package   enrol_paypal
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_expirations extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('processexpirationstask', 'enrol_telr');
    }

    /**
     * Run task for processing expirations.
     */
    public function execute() {
        $enrol = enrol_get_plugin('telp');
        $trace = new \text_progress_trace();
        $enrol->process_expirations($trace);
    }

}
