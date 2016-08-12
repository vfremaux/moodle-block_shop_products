<?php

class block_shop_products_renderer extends plugin_renderer_base {

    function product_table_wide($theblock, $products) {
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
        $producttable->head = array("<b>$pidstr</b>", "<b>$startdatestr</b>", "<b>$enddatestr</b>", "<b>$productlinkstr</b>", "<b>$statusstr</b>");
        $producttable->width = '100%';
        $producttable->size = array('10%', '10%', '10%', '40%', '30%');
        $producttable->align = array('left', 'left', 'left', 'left', 'right');

        foreach($products as $p) {
            $pstart = ($p->startdate) ? date('Y/m/d h:i', $p->startdate) : 'N.C.' ;
            $pstr = '['.$p->code.'] '.$p->name;
            $purl = new moodle_url('/blocks/shop_products/product/view.php', array('id' => $COURSE->id, 'shopid' => $theblock->config->shopinstance, 'blockid' => $theblock->instance->id, 'pid' => $p->id));
            $status = '';
            $productext = $theblock->get_context_product_info($p);
            if ($p->renewable) {
                $pend = ($p->enddate) ? date('Y/m/d h:i', $p->enddate) : 'N.C.' ;
                if (time() > $p->enddate) {
                    // expired
                    $status = '<span class="cs-product-expired">'.get_string('expired', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expireddate">'.$pend.'</span>';
                    $expiredcount++;
                } elseif (time() > $p->enddate - DAYSECS * 3) {
                    // expiring
                    $status = '<span class="cs-product-expiring">'.get_string('expiring', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expiringdate">'.$pend.'</span>';
                } else {
                    // running
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-runningdate">'.$pend.'</span>';
                    $runningcount++;
                }
                $producttable->data[] = array('<span class="cs-product-code">['.$p->reference.']</span>'.$productext, $pstart, $pend, '<a href="'.$purl.'">'.$pstr.'</a>', $status);
            } else {
                if ($p->instanceid) {
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $runningcount++;
                } else {
                    $status = '<span class="cs-product-unused">'.get_string('available', 'block_shop_products').'</span>';
                    $availablecount++;
                }
                $producttable->data[] = array('<span class="cs-product-code">['.$p->reference.']</span>'.$productext, $pstart, 'N.C.', '<a href="'.$purl.'">'.$pstr.'</a>', $status);
            }
        }

        $globalcounts = get_string('available', 'block_shop_products').': <b>'.$availablecount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('running', 'block_shop_products').': <b>'.$runningcount.'</b>&nbsp;&nbsp;&nbsp;';
        $globalcounts .= get_string('expired', 'block_shop_products').': <b>'.$expiredcount.'</b>';

        $str = $globalcounts;

        $str .= html_writer::table($producttable);

        return $str;
    }

    function product_table_narrow($theblock, $products) {
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
        $producttable->head = array("<b>$productlinkstr</b>", "<b>$statusstr</b>");
        $producttable->width = '100%';
        $producttable->size = array('70%', '30%');
        $producttable->align = array('left', 'right');

        foreach($products as $p) {
            $pstart = ($p->startdate) ? date('Y/m/d h:i', $p->startdate) : 'N.C.' ;
            $pstr = '['.$p->code.'] '.$p->name;
            $purl = new moodle_url('/blocks/shop_products/product/view.php', array('id' => $COURSE->id, 'shopid' => $theblock->config->shopinstance, 'blockid' => $theblock->instance->id, 'pid' => $p->id));
            $status = '';
            $productext = $theblock->get_context_product_info($p);
            if ($p->renewable) {
                $pend = ($p->enddate) ? date('Y/m/d h:i', $p->enddate) : 'N.C.';
                if (time() > $p->enddate) {
                    // expired
                    $status = '<span class="cs-product-expired">'.get_string('expired', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expireddate">'.$pend.'</span>';
                    $expiredcount++;
                } elseif (time() > $p->enddate - DAYSECS * 3) {
                    // expiring
                    $status = '<span class="cs-product-expiring">'.get_string('expiring', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-expiringdate">'.$pend.'</span>';
                } else {
                    // running
                    $status = '<span class="cs-product-running">'.get_string('running', 'block_shop_products').'</span>';
                    $pend = '<span class="cs-product-runningdate">'.$pend.'</span>';
                    $runningcount++;
                }
                $producttable->data[] = array('<a href="'.$purl.'" title="'.$p->reference.'">'.$pstr.'</a><br/><span class="smalltext">'.$pstart.' - '.$pend.'</span>', $status);
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