<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 01/11/2018
 * Time: 10:50
 */
?>


<div class="container">


    <?php if(isset($voucher_settings['voucher_amounts'])){?>
    <form action="?action=checkout" method="POST" class="form">

        <h3>Your Details</h3>
        <div class="form-group">
            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First Name"
                   required>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last Name" required>
        </div>
        <div class="form-group">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
        </div>
        <div class="form-group">
            <input type="number" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number" minlength="10" maxlength="12" required>
        </div>


        <h3>Voucher Details</h3>
        <p class="help-text">This information <strong>does</strong> appear on the gift voucher</p>

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">&euro;</span>
            <select class="form-control" id="amount" name="amount">
                <option value="">Choose Amount</option>
                <?php foreach($voucher_settings['voucher_amounts'] as $amounts){?>
                <option><?php echo $amounts;?></option>
                <?php } ?>

            </select>
            </div>
        </div>

        <div class="form-group">
            <input type="text" class="form-control" id="voucher_name" name="voucher_name" placeholder="Name on Gift Voucher" required>
        </div>
        <div class="form-group">
            <textarea class="form-control" id="message" name="message" placeholder="Gift Message (Max 500 characters)"></textarea>
        </div>


        <div class="checkbox">
            <label for="smsmarketing">
                <input type="checkbox" id="smsmarketing" name="smsmarketing" value="1" />
                Send me occasional text messages about upcoming offers and events.
            </label>
        </div>

        <div class="checkbox">
            <label for="emailmarketing">
                <input type="checkbox" id="emailmarketing" name="emailmarketing" value="1" />
                Send me occasional emails about upcoming offers and events.
            </label>
        </div>

        <div class="checkbox">
            <label for="tos">
                <input type="checkbox" id="tos" name="tos" value="1" required />
                I have read and agree to the gift <a href="javascript:void(0);" data-toggle="modal" data-target="#tosmodal">voucher terms and conditions</a>.
            </label>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Checkout</button>
    </form>

        <!-- Modal -->
        <div id="tosmodal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Voucher Terms &amp; Conditions</h4>
                    </div>
                    <div class="modal-body">
                        <?php if(isset($voucher_settings['tos_text'])){
                            echo $voucher_settings['tos_text'];
                        }?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>



    <?php } else { ?>

    <div class="alert alert-danger">
        Sorry vouchers have not been set up yet.
    </div>

    <?php }  ?>


</div>
