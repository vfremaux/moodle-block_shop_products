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
 * Products view. All myproducts
 *
 * @package   block_shop_products
 * @category  blocks
 * @copyright 2013 Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

use local_shop\CatalogItem;
use local_shop\Product;
use local_shop\BillItem;
use local_shop\Bill;
use local_shop\Shop;
use local_shop\Customer;

$id = required_param('id', PARAM_INT); // Course id.
$blockid = required_param('blockid', PARAM_INT); // The shop_product block id.
$shopid = optional_param('shopid', '', PARAM_INT); // The shop id.

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('badblockinstance', 'block_shop_products');
}

$theblock = block_instance('shop_products', $instance);
$theshop = new Shop($shopid);

// Get and check course from block context.
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('coursemisconf');
}

require_course_login($course);

$params = array('id' => $id, 'shopid' => $theshop->id, 'blockid' => $blockid);
$url = new moodle_url('/blocks/shop_products/product/viewall.php', $params);
$PAGE->set_url($url);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$PAGE->set_title(get_string('pluginname', 'block_shop_products'));
$PAGE->set_heading(get_string('viewallproducts', 'block_shop_products'));
$PAGE->navbar->add(get_string('pluginname', 'block_shop_products'));
$PAGE->requires->js_call_amd('block_shop_products/products', 'init');

$usercontext = context_user::instance($USER->id);
$renderer = $PAGE->get_renderer('block_shop_products');

echo $OUTPUT->header();

$meascustomer = Customer::instance_by_user($USER->id);

$allproducts = Product::get_instances(array('customerid' => $meascustomer->id));

echo $OUTPUT->box_start('block-shop-products');
echo $renderer->products($allproducts, $theblock);
echo $OUTPUT->box_end();

echo $OUTPUT->box_start('cs-product-view-returns');
$courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
echo $OUTPUT->single_button($courseurl, get_string('backtocourse', 'block_shop_products'));
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
