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
 * Block's renderer.
 *
 * @package     block_shop_products
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 onwards Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_shop_products_renderer extends plugin_renderer_base {

    public function product_table_wide($theblock, $products) {
        global $COURSE;

        $pidstr = get_string('pid', 'block_shop_products');
        $startdatestr = get_string('startdate', 'block_shop_products');
        $enddatestr = get_string('enddate', 'block_shop_products');
        $productlinkstr = get_string('product', 'block_shop_products');
        $statusstr = get_string('status', 'block_shop_products');

        $availablecount = 0;
        $runningcount = 0;
        $expiredcount = 0;

        $producttable = new html_table();
        $producttable->head = array("<b>$pidstr</b>", "<b>$startdatestr</b>", "<b>$enddatestr</b>",
            "<b>$productlinkstr</b>", "<b>$statusstr</b>");
        $producttable->width = '100%';
        $producttable->size = array('10%', '10%', '10%', '40%', '30%');
        $producttable->align = array('left', 'left', 'left', 'left', 'right');

        foreach ($products as $p) {
            $pstart = ($p->startdate) ? date('Y/m/d H:i', $p->startdate) : 'N.C.';
            $pstr = '['.$p->code.'] '.$p->name;
            $params = array('id' => $COURSE->id,
                            'shopid' => $theblock->config->shopinstance,
                            'blockid' => $theblock->instance->id,
                            'pid' => $p->id);
            $purl = new moodle_url('/blocks/shop_products/product/view.php', $params);
            $status = '';
            $productext = $theblock->get_context_product_info($p);
            if ($p->renewable) {
                $pend = ($p->enddate) ? date('Y/m/d h:i', $p->enddate) : 'N.C.';
                if (time() > $p->enddate) {
                    // Expired.
                    $status = '<span class="cs-product-expired">'.get_string('expired', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expireddate">'.$pend.'</span>';
                    $expiredcount++;
                } else if (time() > $p->enddate - DAYSECS * 3) {
                    // Expiring.
                    $status = '<span class="cs-product-expiring">'.get_string('expiring', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expiringdate">'.$pend.'</span>';
                } else {
                    // Running.
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-runningdate">'.$pend.'</span>';
                    $runningcount++;
                }
                $productline = '<span class="cs-product-code">['.$p->reference.']</span>';
                $productline .= $productext, $pstart, $pend, '<a href="'.$purl.'">'.$pstr.'</a>'
                $producttable->data[] = array($productline, $status);
            } else {
                if ($p->instanceid) {
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $runningcount++;
                } else {
                    $status = '<span class="cs-product-unused">'.get_string('available', 'block_shop_products').'</span>';
                    $availablecount++;
                }
                $productline = '<span class="cs-product-code">['.$p->reference.']</span>';
                $productline .= $productext, $pstart, 'N.C.', '<a href="'.$purl.'">'.$pstr.'</a>'
                $producttable->data[] = array($productline, $status);
            }
        }

        $globalcounts = get_string('available', 'block_shop_products').': <b>'.$availablecount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('running', 'block_shop_products').': <b>'.$runningcount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('expired', 'block_shop_products').': <b>'.$expiredcount.'</b>';

        $str = $globalcounts;

        $str .= html_writer::table($producttable);

        return $str;
    }

    public function product_table_narrow($theblock, $products) {
        global $COURSE;

        $productlinkstr = get_string('product', 'block_shop_products');
        $statusstr = get_string('status', 'block_shop_products');

        $availablecount = 0;
        $runningcount = 0;
        $expiredcount = 0;

        $producttable = new html_table();
        $producttable->head = array("<b>$productlinkstr</b>", "<b>$statusstr</b>");
        $producttable->width = '100%';
        $producttable->size = array('70%', '30%');
        $producttable->align = array('left', 'right');

        foreach ($products as $p) {
            $pstart = ($p->startdate) ? date('Y/m/d h:i', $p->startdate) : 'N.C.';
            $pstr = '['.$p->code.'] '.$p->name;
            $params = array('id' => $COURSE->id,
                            'shopid' => $theblock->config->shopinstance,
                            'blockid' => 0 + @$theblock->instance->id,
                            'pid' => $p->id);
            $purl = new moodle_url('/blocks/shop_products/product/view.php', $params);
            $status = '';
            if ($p->renewable) {
                $pend = ($p->enddate) ? date('Y/m/d h:i', $p->enddate) : 'N.C.';
                if (time() > $p->enddate) {
                    // Expired.
                    $status = '<span class="cs-product-expired">'.get_string('expired', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expireddate">'.$pend.'</span>';
                    $expiredcount++;
                } else if (time() > $p->enddate - DAYSECS * 3) {
                    // Expiring.
                    $status = '<span class="cs-product-expiring">'.get_string('expiring', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expiringdate">'.$pend.'</span>';
                } else {
                    // Running.
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-runningdate">'.$pend.'</span>';
                    $runningcount++;
                }
                $productline = '<a href="'.$purl.'" title="'.$p->reference.'">'.$pstr.'</a><br/>';
                $productline .= '<span class="smalltext">'.$pstart.' - '.$pend.'</span>';
                $producttable->data[] = array($productline, $status);
            } else {
                if ($p->instanceid) {
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $runningcount++;
                } else {
                    $status = '<span class="cs-product-unused">'.get_string('available', 'block_shop_products').'</span>';
                    $availablecount++;
                }
                $producttable->data[] = array('<a href="'.$purl.'" title="'.$p->reference.'">'.$pstr.'</a><br/>'.$pstart, $status);
            }
        }

        $globalcounts = get_string('available', 'block_shop_products').': <b>'.$availablecount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('running', 'block_shop_products').': <b>'.$runningcount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('expired', 'block_shop_products').': <b>'.$expiredcount.'</b>';

        $str = $globalcounts;

        $str .= html_writer::table($producttable);

        return $str;
    }
}