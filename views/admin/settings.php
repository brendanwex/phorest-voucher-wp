<?php
defined('ABSPATH') or die("Cannot access pages directly.");
$voucher_settings = get_option('voucher_settings');

isset($voucher_settings['stripe_test_mode']) ? $stripe_test_mode = $voucher_settings['stripe_test_mode'] : $stripe_test_mode = "";
isset($voucher_settings['tos_text']) ? $tos_text = $voucher_settings['tos_text'] : $tos_text = "";
isset($voucher_settings['customer_email']) ? $customer_email = $voucher_settings['customer_email'] : $customer_email = "";
isset($voucher_settings['customer_email_footer']) ? $customer_email_footer = $voucher_settings['customer_email_footer'] : $customer_email_footer = "";
isset($voucher_settings['voucher_footer']) ? $voucher_footer = $voucher_settings['voucher_footer'] : $voucher_footer = "";
isset($voucher_settings['voucher_text']) ? $voucher_text = $voucher_settings['voucher_text'] : $voucher_text = "";
isset($voucher_settings['voucher_bg_colour']) ? $voucher_bg_colour = $voucher_settings['voucher_bg_colour'] : $voucher_bg_colour = "#ffffff";
isset($voucher_settings['voucher_font_colour']) ? $voucher_font_colour = $voucher_settings['voucher_font_colour'] : $voucher_font_colour = "#000000";
isset($voucher_settings['phorest_demo_mode']) ? $phorest_demo_mode = $voucher_settings['phorest_demo_mode'] : $phorest_demo_mode = "";


?>
<div class="wrap">


    <?php
    if (isset($_GET['settings-updated'])) {
        ?>

        <div class="notice notice-success">
            <p>Settings saved successfully</p>
        </div>

        <?php
    }
    ?>


    <h2>Phorest Voucher Settings</h2>


    <h2 class="nav-tab-wrapper gr-tabs-nav hidden-pdf">
        <a href="#" data-id="general" class="nav-tab nav-tab-active">General</a>
        <a href="#" data-id="phorest" class="nav-tab ">Phorest API</a>
        <a href="#" data-id="email" class="nav-tab">Email Template</a>
        <a href="#" data-id="stripe" class="nav-tab">Stripe API</a>
        <a href="#" data-id="design" class="nav-tab">Voucher Design</a>


    </h2>


    <div class="wrap gr-tabs">

        <form action="options.php" class="booking-plugin-form" method="post">

            <?php settings_fields('voucher-settings'); ?>
            <?php do_settings_sections('voucher-settings'); ?>



            <section id="general" class="tab-content active">

                <h3>General Settings</h3>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Voucher Page</th>
                        <td>

                            <select name="voucher_settings[voucher_page]">

                                <option value="">Select a page</option>

                                <?php foreach ($pages as $page) { ?>

                                    <option value="<?php echo $page['post_id']; ?>" <?php if (isset($voucher_settings['voucher_page']) && $voucher_settings['voucher_page'] == $page['post_id']) echo "selected"; ?>><?php echo $page['post_title']; ?></option>

                                <?php } ?>

                            </select>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Voucher Amounts</th>
                        <td>


                            <div id="more-amounts">

                                <?php
                                if(isset($voucher_settings['voucher_amounts'])){
                                    foreach($voucher_settings['voucher_amounts'] as $amounts){?>
                                        <p><input type="number" name="voucher_settings[voucher_amounts][]" value="<?php echo $amounts;?>" required><button class="button remove-amount" type="button">Remove</button></p>
                                    <?php } } ?>

                            </div>

                            <button type="button" class="button add-amount">Add Amount</button>
                        <p class="description">You can drag to reorder.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Voucher Valid For (Years)</th>
                        <td>


                            <input type="number" name="voucher_settings[voucher_valid]" min="1" value="<?php if (isset($voucher_settings['voucher_valid'])) echo $voucher_settings['voucher_valid'];?>" />
                            <p class="description">If left blank 1 year will be used.</p>

                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row">Terms &amp; Conditions </th>
                        <td>
                            <?php wp_editor($tos_text, 'tos_text', $settings = array('textarea_name' => 'voucher_settings[tos_text]', 'wpautop' => false, 'editor_height' => 500)); ?>

                            <br/>
                            <p class="description">These can be read by the customer on checkout page.</p>
                        </td>
                    </tr>




                </table>

                <button class="button button-primary" type="submit">Save Settings</button>


            </section>




            <section id="phorest" class="tab-content">


                <h3>Phorest Settings</h3>

                <table class="form-table">


                    <tr valign="top">
                        <th scope="row">Business ID</th>
                        <td><input type="text" name="voucher_settings[business_id]"
                                   value="<?php if(isset($voucher_settings['business_id']))  echo esc_attr($voucher_settings['business_id']); ?>" class="regular-text"/>

                            <br/>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Branch ID</th>
                        <td><input type="text" name="voucher_settings[branch_id]"
                                   value="<?php if(isset($voucher_settings['branch_id'])) echo esc_attr($voucher_settings['branch_id']); ?>" class="regular-text"/>

                            <br/>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Username</th>
                        <td><input type="text" name="voucher_settings[phorest_username]"
                                   value="<?php if(isset($voucher_settings['phorest_username'])) echo esc_attr($voucher_settings['phorest_username']); ?>" class="regular-text"/>

                            <br/>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Password</th>
                        <td><input type="text" name="voucher_settings[phorest_password]"
                                   value="<?php if(isset($voucher_settings['phorest_password'])) echo esc_attr($voucher_settings['phorest_password']); ?>" class="regular-text"/>

                            <br/>

                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row">Phorest Demo Mode</th>
                        <td><input type="checkbox" name="voucher_settings[phorest_demo_mode]"
                                   value="1" <?php checked($phorest_demo_mode, 1 , true);?> />


                        </td>
                    </tr>

                </table>
                <button class="button button-primary" type="submit">Save Settings</button>


            </section>


            <section id="email" class="tab-content">


                <h3>Email Settings</h3>


                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Notification Emails</th>
                        <td><input type="email" name="voucher_settings[notification_email]"
                                   value="<?php if(isset($voucher_settings['notification_email'])) echo esc_attr($voucher_settings['notification_email']); ?>" multiple
                                   class="regular-text" required/>

                            <br/>
                            <p class="description">New booking notifications will be sent to this address. Enter
                                multiple addresses separated by a comma.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Customer Email Body</th>
                        <td>

                            <?php wp_editor($customer_email, 'customer_email', $settings = array('textarea_name' => 'voucher_settings[customer_email]', 'wpautop' => false, 'editor_height' => 500)); ?>
                            <br/>
                            <p class="description">The is the body of the text sent to the customer via email. You can use the shortcodes below to personalise this message</p>
                            <pre>[customer_firstname] [customer_lastname] [voucher_amount] [order_date] [voucher_message] [voucher_code] [voucher_terms]</pre>

                        </td>
                    </tr>



                    <tr valign="top">
                        <th scope="row">Customer Email Footer</th>
                        <td>

                            <?php wp_editor($customer_email_footer, 'customer_email_footer', $settings = array('textarea_name' => 'voucher_settings[customer_email_footer]', 'wpautop' => false, 'editor_height' => 500)); ?>
                            <br/>
                            <p class="description">Privacy, terms, directions etc</p>
                        </td>
                    </tr>



                </table>
                <button class="button button-primary" type="submit">Save Settings</button>


            </section>




            <section id="stripe" class="tab-content">

                <h3>Stripe Settings</h3>


                <table class="form-table">


                    <tr valign="top">
                        <th scope="row">Stripe Test Mode</th>
                        <td><input type="checkbox" name="voucher_settings[stripe_test_mode]"
                                   value="1" <?php checked($stripe_test_mode, 1 , true);?> />


                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Test Publishable Key</th>
                        <td><input type="text" name="voucher_settings[stripe_pub_test]"
                                   value="<?php if(isset($voucher_settings['stripe_pub_test'])) echo esc_attr($voucher_settings['stripe_pub_test']); ?>" class="regular-text"/>


                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Test Secret Key</th>
                        <td><input type="text" name="voucher_settings[stripe_secret_test]"
                                   value="<?php if(isset($voucher_settings['stripe_secret_test'])) echo esc_attr($voucher_settings['stripe_secret_test']); ?>" class="regular-text"/>


                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Live Publishable Key</th>
                        <td><input type="text" name="voucher_settings[stripe_pub]"
                                   value="<?php if(isset($voucher_settings['stripe_pub'])) echo esc_attr($voucher_settings['stripe_pub']); ?>" class="regular-text"/>


                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Stripe Live Secret Key</th>

                        <td><input type="text" name="voucher_settings[stripe_secret]"
                                   value="<?php if(isset($voucher_settings['stripe_secret'])) echo esc_attr($voucher_settings['stripe_secret']); ?>" class="regular-text"/>


                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row">Stripe Checkout Icon</th>

                        <td><input type="text" name="voucher_settings[stripe_icon]"
                                   value="<?php if(isset($voucher_settings['stripe_icon'])) echo esc_attr($voucher_settings['stripe_icon']); ?>" class="regular-text"/><button type="button" class="button phorest-uploader">Upload</button>
                        <p class="description">128px x 128px square logo that is displayed on the Stripe credit card form - gif, jpeg or png.</p>

                        </td>
                    </tr>


                </table>

                <button class="button button-primary" type="submit">Save Settings</button>

            </section>


            <section id="design" class="tab-content">

                <h3>Voucher Design Settings</h3>


                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Voucher Logo</th>
                        <td>
                            <input type="text" name="voucher_settings[voucher_logo]" value="<?php if(isset($voucher_settings['voucher_logo'])) echo esc_attr($voucher_settings['voucher_logo']); ?>"  class="regular-text" /><button type="button" class="button phorest-uploader">Upload</button>
                        <p class="description">Max of 600px wide, png only.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Voucher Banner</th>
                        <td>
                            <input type="text" name="voucher_settings[voucher_banner]" value="<?php if(isset($voucher_settings['voucher_banner'])) echo esc_attr($voucher_settings['voucher_banner']); ?>"  class="regular-text" /><button type="button" class="button phorest-uploader">Upload</button>
                            <p class="description">Max of 1160px wide, png only.</p>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Voucher Background Colour</th>
                        <td>
                            <input type="text" name="voucher_settings[voucher_bg_colour]" value="<?php if(isset($voucher_settings['voucher_bg_colour'])) echo esc_attr($voucher_settings['voucher_bg_colour']); ?>"  class="regular-text phorest-colour" />
                            <p class="description">Background colour of the PDF voucher.</p>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Voucher Font Colour</th>
                        <td>
                            <input type="text" name="voucher_settings[voucher_font_colour]" value="<?php if(isset($voucher_settings['voucher_font_colour'])) echo esc_attr($voucher_settings['voucher_font_colour']); ?>"  class="regular-text phorest-colour" />
                            <p class="description">Font colour, border colours on the PHP voucher.</p>

                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row">Voucher Footer</th>
                        <td>
                            <?php wp_editor($voucher_footer, 'voucher_footer', $settings = array('textarea_name' => 'voucher_settings[voucher_footer]', 'wpautop' => false, 'editor_height' => 100)); ?>
                            <p class="description">Footer displayed on the voucher - max 2 lines.</p>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Preview Voucher</th>
                        <td>
                        <a href="<?php echo admin_url("admin-ajax.php?action=phorest_voucher_preview");?>" target="_blank" class="button">Preview Voucher</a>
                        </td>
                    </tr>


                </table>

                <button class="button button-primary" type="submit">Save Settings</button>

            </section>


        </form>
    </div>

</div>