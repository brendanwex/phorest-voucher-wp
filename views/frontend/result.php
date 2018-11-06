<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 01/11/2018
 * Time: 10:51
 */

    $order_result = $this->get_order($_SESSION['phorest']['order']);
    $date_format = get_option("date_format");

    if((int) $order_result->status == 1) {
        ?>


        <h2>Thank you for your purchase</h2>

        <p>Your gift voucher has been sent to you by email. Details of your order are below.</p>

        <table class="table table-bordered table-striped">
            <tbody>
            <tr>
                <th>Customer Name</th>
                <td><?php echo $order_result->firstname . " " . $order_result->lastname; ?></td>
            </tr>
            <tr>
                <th>Order Date</th>
                <td><?php echo date($date_format, $order_result->order_date); ?></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td><?php echo "&euro; " . number_format($order_result->voucher_amount, 2, '.', ''); ?></td>
            </tr>
            <tr>
                <th>Voucher Message</th>
                <td><?php echo $order_result->message; ?></td>
            </tr>
            <tr>
                <th>Voucher Code</th>
                <td><?php echo $order_result->voucher_number; ?></td>
            </tr>
            <tr>
                <th>Expiry Date</th>
                <td><?php echo date($date_format, $order_result->voucher_expiry); ?></td>
            </tr>
            </tbody>
        </table>


        <?php


    }else{

        ?>

        <h2>order Failed</h2>

        <p>Sorry we could not complete your transaction, please try again later or try another payment method.</p>
    <?php }
    //Unset SESSION
    unset($_SESSION['phorest']);


