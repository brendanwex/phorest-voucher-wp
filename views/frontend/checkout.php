<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

if(isset($voucher_settings['stripe_test_mode'])){

    $stripe_pub = $voucher_settings['stripe_pub_test'];
}else{

    $stripe_pub = $voucher_settings['stripe_pub'];

}
if($voucher_settings['stripe_icon']){
    $stripe_icon = $voucher_settings['stripe_icon'];
}else{
    $stripe_icon = "https://stripe.com/img/documentation/checkout/marketplace.png";
}
?>
<style>
    .stripe-button-el{
        display:none!important;
    }
</style>

<div class="container">



    <form action="<?php echo admin_url('admin-ajax.php?action=phorest_callback');?>" method="POST" class="form">

        <h2>Review Purchase</h2>



        <table class="table table-striped table-bordered">
            <tr>
                <th>Your Name</th>
                <td><?php echo $_SESSION['phorest']['firstname']." ".$_SESSION['phorest']['lastname'];?></td>
            </tr>
            <tr>
                <th>Email Address</th>
                <td><?php echo $_SESSION['phorest']['email'];?></td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td><?php echo $_SESSION['phorest']['mobile'];?></td>
            </tr>
            <tr>
                <th>Voucher Amount</th>
                <td>&euro; <?php echo number_format($_SESSION['phorest']['amount'], 2);?></td>
            </tr>
            <tr>
                <th>Voucher Message</th>
                <td><?php echo $_SESSION['phorest']['message'];?></td>
            </tr>
            <tr>
                <th>Name on Voucher</th>
                <td><?php echo $_SESSION['phorest']['voucher_name'];?></td>
            </tr>
        </table>
        <script
            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
            data-key="<?php echo $stripe_pub;?>"
            data-amount="<?php echo ($_SESSION['phorest']['amount'] * 100);?>"
            data-name="<?php echo get_bloginfo("name");?>"
            data-description="Online Voucher"
            data-currency="EUR"
            data-image="<?php echo $stripe_icon;?>"
            data-locale="auto"
            data-zip-code="false"
            data-label="Pay Now"
            data-email="<?php echo $_SESSION['phorest']['email'];?>">

        </script>


        <button type="submit" class="btn btn-primary btn-lg btn-block">Pay Now</button>

    </form>


</div>

