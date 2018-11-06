<?php
/**
 * Copyright (c) 2017.
 */
defined('ABSPATH') or die("Cannot access pages directly.");

isset($_GET['s']) ? $s = $_GET['s'] : $s = "";
$orders = new OrdersTable();

$orders->prepare_items($s);


?>


<div class="wrap">

    <h2>Voucher Purchases</h2>


    <form id="bookings-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php  $orders->search_box('Search', 'search_id'); ?>
        <!-- Now we can render the completed list table -->
        <?php $orders->display() ?>
    </form>

