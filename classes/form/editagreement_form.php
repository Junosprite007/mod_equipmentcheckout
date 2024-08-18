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
 * Form for editing an existing agreement by creating a new version.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class editagreement_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $agreement = $this->_customdata['agreement'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'title', get_string('agreementtitle', 'local_equipment'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('editor', 'content', get_string('agreementcontent', 'local_equipment'));
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        $types = [
            'informational' => get_string('agreementtype_informational', 'local_equipment'),
            'optinout' => get_string('agreementtype_optinout', 'local_equipment'),
        ];
        $mform->addElement('select', 'agreementtype', get_string('agreementtype', 'local_equipment'), $types);

        $mform->addElement('advcheckbox', 'active', get_string('active', 'local_equipment'));

        $mform->addElement('advcheckbox', 'requiresignature', get_string('requiresignature', 'local_equipment'));

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_equipment'));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_equipment'));

        $mform->addElement('static', 'currentversion', get_string('currentversion', 'local_equipment'), $agreement->version);

        $this->add_action_buttons();
    }

    /**
     * Perform validation on the form data.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['startdate'] >= $data['enddate']) {
            $errors['enddate'] = get_string('enddateafterstart', 'local_equipment');
        }

        return $errors;
    }
}