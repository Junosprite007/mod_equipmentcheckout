<?php
// This file is part of FLIP Plugins for Moodle
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Add partnerships form.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

/**
 * Form for adding partnerships.
 */
class addpartnership_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE, $DB;

        // $PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'init');
        $mform = $this->_form;

        $numberofrepeats = 0;
        $repeatarray = [];
        $repeatoptions = [];
        $address = new stdClass();

        $users = local_equipment_auto_complete_users();
        $courses_formatted = local_equipment_get_master_courses('ALL_COURSES_CURRENT');

        $repeatarray['partnershipheader'] = $mform->createElement('header', 'partnershipheader', get_string('partnership', 'local_equipment'), ['class' => 'partnership-header']);

        $repeatno = optional_param('repeatno', 1, PARAM_INT);
        $mform->addElement('hidden', 'partnerships', $repeatno);
        // Add a delete button for each repeated element (except the first one).
        $repeatarray['delete'] = $mform->createElement('html', '<button type="button" class="remove-partnership btn btn-danger"><i class="fa fa-trash"></i></button>');
        // $mform->setDefault('delete', '<i class="fa fa-trash"></i>');

        $repeatarray['partnershipname'] = $mform->createElement('text', 'partnershipname', get_string('partnershipname', 'local_equipment'), ['class' => 'partnership-name-input']);
        $repeatarray['liaisons'] = $mform->createElement('autocomplete', 'liaisons', get_string('selectliaisons', 'local_equipment'), [], $users);
        $repeatarray['courses'] = $mform->createElement('select', 'courses', get_string('selectcourses', 'local_equipment'), $courses_formatted, ['multiple' => 'multiple', 'size' => 10]);
        $repeatarray['active'] = $mform->createElement('advcheckbox', 'active', get_string('active'));

        // Physical address section
        $address = local_equipment_add_address_block($mform, 'physical');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Mailing address section
        $address = local_equipment_add_address_block($mform, 'mailing');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Pickup address section
        $address = local_equipment_add_address_block($mform, 'pickup');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Billing address section
        $address = local_equipment_add_address_block($mform, 'billing');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);


        // Set options.
        $repeatoptions['partnerships']['type'] = PARAM_INT;
        $repeatoptions['partnershipheader']['header'] = true;
        $repeatoptions['partnershipname']['type'] = PARAM_TEXT;
        $repeatoptions['partnershipname']['rule'] = 'required';
        $repeatoptions['liaisons']['type'] = PARAM_TEXT;
        $repeatoptions['courses']['type'] = PARAM_TEXT;
        $repeatoptions['active']['type'] = PARAM_BOOL;
        $repeatoptions['active']['default'] = 1;
        // $repeatoptions['delete']['type'] = 'button';
        // $repeatoptions['delete']['default'] = '<i class="fa fa-trash"></i>';


        // $addfields = optional_param('add_partnership', '', PARAM_TEXT);
        // $deletefields = optional_param('delete_partnership', '', PARAM_TEXT);

        // if (!empty($deletefields)) {
        //     $repeatno--;
        // }

        // $this->repeat_elements($repeatarray, $repeatno, $repeatedoptions, 'option_repeats', 'option_add_fields', 3, get_string('addmorefields', 'form'), true);

        // Use this later if it helps.
        // $numberofrepeats = $this->repeat_elements(
        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'partnerships',
            'add_partnership',
            1,
            get_string('addmorepartnerships', 'local_equipment'),
            false,
            'delete_partnership'
        );

        // $PAGE->requires->js_call_amd('local_equipment/deletepartnership_button', 'init');
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Form validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // No custom validation yet.

        return $errors;
    }
}
