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
 * Virtual course consent (vcc) submission management page.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');

admin_externalpage_setup('local_equipment_vccsubmissions');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/vccsubmissions.php'));
$PAGE->set_title(get_string('managevccsubmissions', 'local_equipment'));
$PAGE->set_heading(get_string('managevccsubmissions', 'local_equipment'));

require_capability('local/equipment:managevccsubmissions', $context);

// Handle delete action.
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $DB->delete_records('local_equipment_vccsubmission', ['id' => $delete]);
    \core\notification::success(get_string('vccsubmissiondeleted', 'local_equipment'));
    redirect($PAGE->url);
}

echo $OUTPUT->header();

// Set up the table.
$table = new flexible_table('local-equipment-vccsubmissions');

$columns = [
    'timecreated',
    'parent_firstname',
    'parent_lastname',
    'parent_email',
    'parent_phone2',
    'partnership_name',
    'students',
    'parent_mailing_address',
    'parent_mailing_extrainstructions',
    'pickup',
    'pickupmethod',
    'pickuppersonname',
    'pickuppersonphone',
    'pickuppersondetails',
    'usernotes',
    'adminnotes',
    'actions'
];
$columns_nosort = [
    'parent_mailing_address',
    'parent_mailing_extrainstructions',
    'pickup',
    'students',
    'actions'
];

$headers = array_map(function ($column) {
    return get_string($column, 'local_equipment');
}, $columns);

$table->define_columns($columns);
$table->define_headers($headers);

$nowrap_header = 'local-equipment-nowrap-header';
$nowrap_cell = 'local-equipment-nowrap-cell';

foreach ($columns as $column) {
    $table->column_class($column, $nowrap_header);
}

$table->column_class('timecreated', $nowrap_cell);
$table->column_class('partnership_name', $nowrap_cell);
$table->column_class('parent_mailing_address', $nowrap_cell);
$table->column_class('students', $nowrap_cell);
$table->column_class('pickup', $nowrap_cell);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'timecreated', SORT_DESC);
foreach ($columns_nosort as $column) {
    $table->no_sorting($column);
}
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'vccsubmissions');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$select = "vccsubmission.id, vccsubmission.userid, vccsubmission.partnershipid, vccsubmission.studentids, vccsubmission.pickupid, vccsubmission.pickupmethod, vccsubmission.pickuppersonname, vccsubmission.pickuppersonphone, vccsubmission.pickuppersondetails, vccsubmission.usernotes, vccsubmission.adminnotes, vccsubmission.timecreated,
        user.firstname AS parent_firstname, user.lastname AS parent_lastname, user.email AS parent_email, user.phone2 AS parent_phone2,
        partnership.name AS partnership_name, partnership.pickup_extrainstructions, partnership.pickup_apartment, partnership.pickup_streetaddress, partnership.pickup_city, partnership.pickup_state, partnership.pickup_zipcode,
        pickup.starttime, pickup.endtime";

$from =
    "{local_equipment_vccsubmission} vccsubmission
        LEFT JOIN {user} user ON vccsubmission.userid = user.id
        LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id
        LEFT JOIN {local_equipment_pickup} pickup ON vccsubmission.pickupid = pickup.id";
$where = "1=1";
$params = [];

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = 'vccsubmission.timecreated DESC';
}
$submissions = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $sort", $params);

$select = "parent.id, parent.userid, parent.mailing_extrainput AS parent_mailing_extrainput,
        parent.mailing_streetaddress AS parent_mailing_streetaddress,
        parent.mailing_apartment AS parent_mailing_apartment,
        parent.mailing_city AS parent_mailing_city,
        parent.mailing_state AS parent_mailing_state,
        parent.mailing_country AS parent_mailing_country,
        parent.mailing_zipcode AS parent_mailing_zipcode,
        parent.mailing_extrainstructions AS parent_mailing_extrainstructions";

$from = "{local_equipment_user} parent";
$submissions_parentaddress = $DB->get_records_sql("SELECT $select FROM $from WHERE $where");

// This is the first pass where we merge records of parents who have multiple children and did not put that all on one form.
$formattedpickuplocation = get_string('contactusforpickup', 'local_equipment');

foreach ($submissions as $submission) {
    $submission->parent_mailing_address = '';
    $submission->parent_mailing_extrainstructions = '';

    $break = false;
    foreach ($submissions_parentaddress as $parentuser) {
        if ($parentuser->userid == $submission->userid) {
            if ($parentuser->parent_mailing_apartment) {
                $submission->parent_mailing_address = $parentuser->parent_mailing_streetaddress . ', ' . get_string('apt', 'local_equipment') . ' ' . $parentuser->parent_mailing_apartment . ', ' . $parentuser->parent_mailing_city . ', ' . $parentuser->parent_mailing_state . ' ' . $parentuser->parent_mailing_zipcode;
            } else {
                $submission->parent_mailing_address = "$parentuser->parent_mailing_streetaddress, $parentuser->parent_mailing_city, $parentuser->parent_mailing_state $parentuser->parent_mailing_zipcode";
            }

            $submission->parent_mailing_extrainstructions = $parentuser->parent_mailing_extrainstructions;
            $break = true;
        }
        if ($break) {
            break;
        }
    }

    // $pickup_extrainstructions = $submission->pickup_extrainstructions ?? '';

    $datetime = userdate($submission->starttime, get_string('strftimedate', 'langconfig')) . ' ' .
        userdate($submission->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
        userdate($submission->endtime, get_string('strftimetime', 'langconfig'));

    $pickup_pattern = '/#(.*?)#/' ?? '';
    $pickup_name = $submission->pickup_city;

    if (!empty($submission->pickup_extrainstructions) && preg_match($pickup_pattern, $submission->pickup_extrainstructions, $matches)) {
        $pickup_name = $submission->locationname = $matches[1];
        $submission->pickup_extrainstructions = trim(preg_replace($pickup_pattern, '', $submission->pickup_extrainstructions, 1));
    }

    // if (
    //     preg_match($pickup_pattern, $submission->pickup_extrainstructions, $matches)
    // ) {
    //     $pickup_name = $submission->locationname = $matches[1];
    //     $submission->pickup_extrainstructions = trim(preg_replace($pickup_pattern, '', $submission->pickup_extrainstructions, 1));
    // }
    if ($submission->pickup_streetaddress) {
        $formattedpickuplocation = "$pickup_name — $datetime — $submission->pickup_streetaddress, $submission->pickup_city, $submission->pickup_state $submission->pickup_zipcode";
    }

    $submission->starttime = $submission->starttime ? userdate($submission->starttime) : get_string('contactusforpickup', 'local_equipment');
    $minwidth_cell = 'local-equipment-minwidth-cell';
    $actions = '';
    $viewurl = new moodle_url('/local/equipment/vccsubmissionview.php', ['id' => $submission->id]);
    $editurl = new moodle_url('/local/equipment/vccsubmissionform.php', ['id' => $submission->id]);
    $deleteurl = new moodle_url($PAGE->url, ['delete' => $submission->id, 'sesskey' => sesskey()]);

    $row = [];
    $row[] = userdate($submission->timecreated, get_string('strftime24date_mdy', 'local_equipment'));
    $row[] = $submission->parent_firstname;
    $row[] = $submission->parent_lastname;
    $row[] = $submission->parent_email;
    $row[] = $submission->parent_phone2;
    $row[] = $submission->partnership_name;
    $row[] = local_equipment_get_vcc_students($submission);
    $row[] = $submission->parent_mailing_address;
    $row[] = $submission->parent_mailing_extrainstructions;
    $row[] = $formattedpickuplocation;
    $row[] = $submission->pickupmethod;
    $row[] = $submission->pickuppersonname;
    $row[] = $submission->pickuppersonphone;
    $row[] = $submission->pickuppersondetails;
    $row[] = $submission->usernotes;
    $row[] = $submission->adminnotes;
    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
