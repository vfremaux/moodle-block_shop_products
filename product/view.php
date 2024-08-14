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
 * Product view.
 *
 * @package   block_shop_products
 * @category  blocks
 * @copyright 2013 Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @todo : templatize
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

use local_shop\CatalogItem;
use local_shop\Product;
use local_shop\BillItem;
use local_shop\Bill;
use local_shop\Shop;

$id = required_param('id', PARAM_INT); // Course id.
$productid = required_param('pid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT); // The shop_product block id.
$shopid = required_param('shopid', PARAM_INT); // The shop id.

try {
    $product = new Product($productid);
} catch (Exception $e) {
    throw new moodle_exception('objectexception', 'block_shop_products', $e->message);
}

try {
    $catalogitem = new CatalogItem($product->catalogitemid);
} catch (Exception $e) {
    throw new moodle_exception('objectexception', 'block_shop_products', $e->message);
}

if (!$instance = $DB->get_record('block_instances', ['id' => $blockid])) {
    throw new moodle_exception('badblockinstance', 'block_shop_products');
}

$theblock = block_instance('shop_products', $instance);
$theshop = new Shop($shopid);

// Get and check course from block context.
if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new moodle_exception('coursemisconf');
}

require_course_login($course);

$params = ['id' => $id, 'shopid' => $theshop->id, 'blockid' => $blockid, 'pid' => $productid];
$url = new moodle_url('/blocks/shop_products/product/view.php', $params);
$PAGE->set_url($url);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$PAGE->set_title(get_string('pluginname', 'block_shop_products'));
$PAGE->set_heading(get_string('pluginname', 'block_shop_products'));
$PAGE->navbar->add(get_string('pluginname', 'block_shop_products'));

$usercontext = context_user::instance($USER->id);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('productinstance', 'block_shop_products', format_string($catalogitem->name)));

$productinfo = $product->extract_production_data();
$handler = $catalogitem->get_handler();

echo format_text($catalogitem->description, $catalogitem->descriptionformat);

echo '<p>';
echo $catalogitem->notes;
echo '</p>';

echo $OUTPUT->heading(get_string('product', 'block_shop_products'), 3);
echo $OUTPUT->box_start('block-shop-product-ref-box block');

echo '<div>';
echo '<div class="cs-product-key">'.get_string('reference', 'block_shop_products').'</div>';
echo '<div class="cs-product-value monospace">'.$product->reference.'</div>';
echo '</div>';

echo '<div>';
echo '<div class="cs-product-key">'.get_string('startdate', 'block_shop_products').'</div>';
echo '<div class="cs-product-value">'.userdate($product->startdate).'</div>';
echo '</div>';

echo '<div>';
echo '<div class="cs-product-key">'.get_string('enddate', 'block_shop_products').'</div>';
echo '<div class="cs-product-value">'.userdate($product->enddate).'</div>';
echo '</div>';
echo $OUTPUT->box_end();

echo $OUTPUT->heading(get_string('production', 'block_shop_products'), 3);
echo $OUTPUT->box_start('cs-product-production-box block');
echo $handler->display_product_infos($productid, $productinfo);
echo $OUTPUT->box_end();

echo $OUTPUT->heading(get_string('purchase', 'block_shop_products'), 3);
$productevents = $DB->get_records('local_shop_productevent', array('productid' => $product->id));
echo $OUTPUT->box_start('cs-product-billinfo-box block');

if ($productevents) {
    foreach ($productevents as $pe) {
        $bi = new BillItem($pe->billitemid, $bill);
        $bill = new Bill($bi->billid, $theshop);
        echo '<p><div class="cs-product-bill">'.$bill->title.'</div>';
        echo '<div class="cs-product-date">'.userdate($bill->emissiondate).'</div></p>';
        echo '<p><div class="cs-product-billitem">['.$bi->catalogitem->code.']</div> ';
        echo '<div class="cs-product-billitem">'.$bi->catalogitem->name.'</div></p>';
        echo '<div><div class="cs-product-key">'.get_string('quantity', 'block_shop_products').'</div>';
        echo '<div class="cs-product-value">'.$bi->quantity.'</div></div>';
        echo '<div><div class="cs-product-key">'.get_string('unitprice', 'block_shop_products').'</div>';
        echo '<div class="cs-product-value">'.sprintf('%0.2f', $bi->unitcost).' '.$bill->currency.'</div></div>';
    }
}
echo $OUTPUT->box_end();

if (method_exists($handler, 'display_product_actions')) {
    echo $OUTPUT->heading(get_string('manage', 'block_shop_products'), 3);
    echo $OUTPUT->box_start('cs-action-box block');
    echo $handler->display_product_actions($productid, $productinfo);
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->heading(get_string('manage', 'block_shop_products'), 3);
    echo $OUTPUT->box_start('cs-action-box block');
    echo get_string('noactions', 'block_shop_products');
    echo $OUTPUT->box_end();
}

echo '<p></p>';

echo $OUTPUT->footer();
