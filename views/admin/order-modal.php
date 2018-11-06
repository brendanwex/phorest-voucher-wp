<?php
defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 06/11/2018
 * Time: 13:55
 */
?>

<div class="phorest-order-backdrop">
</div>
<div class="phorest-order-modal" id="phorest-modal-<?php echo $order_id; ?>">

    <div class="phorest-order-modal-inner">
        <div class="topbar">
            <h3>Order <?php echo $order_id; ?></h3>
            <a href="#" class="close-phorest-modal">&times;</a>
            <div class="clear"></div>
        </div>


        <div class="inner-content">

            <table class="form-table">

                <tr>
                    <th>Order Number</th>
                    <td>
                        GF-<?php echo $order_id; ?>
                    </td>
                </tr>

                <tr>
                    <th>Customer Name</th>
                    <td><?php echo $order->firstname . " " . $order->lastname; ?></td>
                </tr>
                <tr>
                    <th>Order Date</th>
                    <td><?php echo date($date_format, $order->order_date); ?></td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><?php echo "&euro; " . number_format($order->voucher_amount, 2, '.', ''); ?></td>
                </tr>
                <tr>
                    <th>Voucher Message</th>
                    <td><?php if(isset($order->message)) echo $order->message; ?></td>
                </tr>
                <tr>
                    <th>Voucher Code</th>
                    <td><?php if(isset($order->voucher_number)) echo $order->voucher_number; ?></td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td><?php if(isset($order->voucher_expiry))  echo date($date_format, $order->voucher_expiry); ?></td>
                </tr>
                <tr>
                    <th>Stripe ID</th>
                    <td><?php if(isset($order->stripe_response)) echo $order->stripe_response; ?></td>
                </tr>

                <?php if($order->status == 1){?>
                <tr>
                    <th>Actions</th>
                    <td><a href="#" class="button resend-customer-email" data-id="<?php echo $order_id;?>">Resend Customer Email</a> <span class="email-response"></span></td>
                </tr>

                <?php } ?>
            </table>


        </div>


    </div>


</div>
