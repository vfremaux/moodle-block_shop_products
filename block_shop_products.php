<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block main class
 *
 * @package    block_shop_products
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block main class
 */
class block_shop_products extends block_base {

    /**
     * Standard init
     */
    public function init() {
        $this->title = get_string('blockname', 'block_shop_products');
    }

    /**
     * Applicable format
     */
    public function applicable_formats() {
        return ['all' => false, 'my' => true, 'course' => true];
    }

    /**
     * Instance specialisation
     */
    public function specialization() {
        return false;
    }

    /**
     * Can we have multiple instances in context ? 
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Main content
     */
    public function get_content() {
        global $USER, $DB, $COURSE, $PAGE;

        $renderer = $PAGE->get_renderer('block_shop_products');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if (empty($this->config->shopinstance)) {
            $this->content->icons[] = '';
            $this->content->items[] = get_string('notconfigured', 'block_shop_products');
            $this->content->footer = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $sql = "
            SELECT
                cp.id,
                ci.renewable,
                ci.name,
                ci.code,
                cp.reference,
                cp.contexttype,
                cp.instanceid,
                cp.startdate,
                cp.enddate
            FROM
                {local_shop_product} cp,
                {local_shop_catalogitem} ci,
                {local_shop_productevent} pe,
                {local_shop_billitem} bi,
                {local_shop_customer} c
            WHERE
                cp.catalogitemid = ci.id AND
                cp.id = pe.productid AND
                bi.id = pe.billitemid AND
                cp.customerid = c.id AND
                c.hasaccount = ?
            ORDER BY
                cp.startdate DESC
        ";

        if ($products = $DB->get_records_sql($sql, [$USER->id])) {
            $wide = false;

            // Check we are not in central position of a page format.
            if ($COURSE->format == 'page') {
                $blockposition = $DB->get_record('block_positions', ['blockinstanceid' => $this->instance->id]);
                if (!$blockposition) {
                    if ($this->defaultregion == 'main') {
                        $wide = true;
                    }
                } else {
                    if ($blockposition->region == 'main') {
                        $wide = true;
                    }
                }
            }

            if ($wide) {
                $this->content->text = $renderer->product_table_wide($this, $products);
            } else {
                $this->content->text = $renderer->product_table_narrow($this, $products);
            }
        } else {
            $this->content->text = get_string('noproducts', 'block_shop_products');
        }

        return $this->content;
    }

    /**
     * Hide the title bar when none set..
     */
    public function hide_header() {
        return empty($this->config->title);
    }

    /**
     * Get info on product
     * @param object $product
     */
    public function get_context_product_info($product) {
        global $DB, $OUTPUT;

        if (empty($product->instanceid)) {
            return '';
        }

        $str = '';
        switch ($product->contexttype) {
            case 'user_enrolment':
                $ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid]);
                $user = $DB->get_record('user', ['id' => $ue->userid]);
                $courseid = $DB->get_field('enrol', 'courseid', ['id' => $ue->enrolid]);
                $course = $DB->get_record('course', ['id' => $courseid], 'id,shortname,fullname');
                $str .= $OUTPUT->box_start();
                $str .= get_string('assignedto', 'block_shop_products', fullname($user));
                $str .= '<br/>';
                $str .= get_string('incourse', 'block_shop_products', $course);
                $str .= $OUTPUT->box_end();
        }
        return $str;
    }
}
