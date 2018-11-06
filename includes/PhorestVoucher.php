<?php
defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 01/11/2018
 * Time: 10:55
 */
class PhorestVoucher
{


    private $phorest_api, $phorest_settings;
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'phorest_admin_assets'));
        //add_action('wp_enqueue_scripts', array($this, 'phorest_frontend_assets'));
        add_shortcode('phorest-vouchers', array($this, 'phorest_form_sc'));
        add_action('init', array($this, 'phorest_init'));
        add_action('admin_menu', array($this, 'phorest_plugin_pages'));
        add_action('wp_ajax_phorest_callback', array($this, 'phorest_callback'));
        add_action('wp_ajax_nopriv_phorest_callback', array($this, 'phorest_callback'));
        add_action("wp_ajax_phorest_voucher_preview", array($this, "phorest_generate_voucher_pdf"));
        add_action("wp_ajax_resend_customer_email", array($this, "resend_customer_email"));

        $this->phorest_settings = get_option("voucher_settings");


    }

    public function phorest_init()
    {

        if (!session_id()) {
            session_start();
        }


        register_setting('voucher-settings', 'voucher_settings');

    }


    public function phorest_plugin_pages()
    {


        add_menu_page('Vouchers', 'Vouchers', 'publish_posts', 'vouchers-overview', array($this, 'phorest_vouchers_overview'), 'dashicons-money', 6);
        add_submenu_page('vouchers-overview', 'Settings', 'Settings', 'manage_options', 'vouchers-settings', array($this, 'phorest_vouchers_settings'));


    }

    public function phorest_admin_assets()
    {

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('phorest', PHOREST_ASSETS_PATH . '/js/phorest-admin.js', array('jquery'), false, '1.0.0');
        wp_enqueue_style('fontawesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

        wp_enqueue_style('phorest', PHOREST_ASSETS_PATH . '/css/phorest-admin.css');


    }


    public function phorest_frontend_assets()
    {

        wp_enqueue_script('phorest', PHOREST_ASSETS_PATH . '/js/phorest-frontend.js', array('jquery'), false, '1.0.0');
        wp_enqueue_style('phorest', PHOREST_ASSETS_PATH . '/css/phorest-frontend.css');

    }


    public function phorest_vouchers_overview()
    {

        include(PHOREST_VIEWS_DIR . "admin/orders.php");


    }

    public function phorest_vouchers_settings()
    {

        $pages = $this->phorest_list_posts($post_type = "page");

        include(PHOREST_VIEWS_DIR . "admin/settings.php");


    }

    public function phorest_form_sc()
    {

        isset($_GET['action']) ? $action = $_GET['action'] : $action = "";

        $voucher_settings = get_option("voucher_settings");


        switch ($action) {

            case "checkout":

                if (isset($_POST['amount'])) {

                    $_SESSION['phorest']['firstname'] = $_POST['firstname'];
                    $_SESSION['phorest']['lastname'] = $_POST['lastname'];
                    $_SESSION['phorest']['mobile'] = $_POST['mobile'];
                    $_SESSION['phorest']['email'] = $_POST['email'];
                    $_SESSION['phorest']['amount'] = $_POST['amount'];
                    $_SESSION['phorest']['voucher_name'] = $_POST['voucher_name'];
                    $_SESSION['phorest']['message'] = $_POST['message'];

                    isset($_POST['emailmarketing']) ? $emailmarketing = $_POST['emailmarketing']: $emailmarketing = 0;
                    isset($_POST['smsmarketing']) ? $smsmarketing = $_POST['smsmarketing'] : $smsmarketing = 0;

                    $_SESSION['phorest']['emailmarketing'] = $emailmarketing;
                    $_SESSION['phorest']['smsmarketing'] = $smsmarketing;


                    include(PHOREST_VIEWS_DIR . "frontend/checkout.php");


                } else {

                    include(PHOREST_VIEWS_DIR . "frontend/error.php");


                }


                break;


            case "result":


                if (isset($_SESSION['phorest']['order'])) {

                    include(PHOREST_VIEWS_DIR . "frontend/result.php");

                } else {

                    include(PHOREST_VIEWS_DIR . "frontend/error.php");


                }

                break;


            default:


                include(PHOREST_VIEWS_DIR . "frontend/form.php");


        }


    }


    public function create_order()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . "voucher_orders";


        isset($_SESSION['phorest']['message']) ? $message = strip_tags($_SESSION['phorest']['message']) : $message = "";
        isset($_SESSION['phorest']['emailmarketing']) ? $emailmarketing = $_SESSION['phorest']['emailmarketing'] : $emailmarketing = 0;
        isset($_SESSION['phorest']['smsmarketing']) ? $smsmarketing = $_SESSION['phorest']['smsmarketing'] : $smsmarketing = 0;

        $wpdb->insert($table_name, array('firstname' => $_SESSION['phorest']['firstname'], 'lastname' => $_SESSION['phorest']['lastname'], 'email' => $_SESSION['phorest']['email'], 'mobile' => $_SESSION['phorest']['mobile'], 'voucher_name' => $_SESSION['phorest']['voucher_name'],  'voucher_amount' => $_SESSION['phorest']['amount'], 'stripe_token' => $_POST['stripeToken'], 'order_date' => current_time("timestamp"), 'status' => 0, 'message' => $message, 'smsmarketing' => $smsmarketing, 'emailmarketing' => $emailmarketing));


        return $wpdb->insert_id;

    }

    public function get_order($order_id)
    {

        global $wpdb;


        $table_name = $wpdb->prefix . "voucher_orders";

        if ($order_id) {

            $data = $wpdb->get_row("SELECT * FROM $table_name WHERE order_id = '$order_id'");

            $result = stripslashes_deep($data);

        } else {

            $result = false;

        }

        return $result;

    }

    public function get_orders($s="")
    {

        global $wpdb;


            $table_name = $wpdb->prefix . "voucher_orders";


            if(empty($s)){
                $data = $wpdb->get_results("SELECT * FROM $table_name", "ARRAY_A");

            }else{

                $data = $wpdb->get_results("SELECT * FROM $table_name WHERE firstname LIKE '%$s%' OR lastname LIKE '%$s%' OR email LIKE '%$s%'", "ARRAY_A");

            }

            $result = stripslashes_deep($data);

            return $result;

    }


    public function phorest_callback()
    {


        $voucher_settings = get_option("voucher_settings");


        if (isset($voucher_settings['stripe_test_mode'])) {

            $stripe_secret = $voucher_settings['stripe_secret_test'];
        } else {

            $stripe_secret = $voucher_settings['stripe_secret'];

        }


        $token = $_POST['stripeToken'];


        $order = $this->create_order();


        $amount = ($_SESSION['phorest']['amount'] * 100);


        $voucher_amount = number_format($_SESSION['phorest']['amount'], 2, '.', '');

        $email = $_SESSION['phorest']['email'];

        $errors = array();


        if (empty($token)) {

            $errors[] = 'The order cannot be processed. Please make sure you have JavaScript enabled and try again (No Token).';

        }

        if (!$order) {

            $errors[] = 'The order cannot be processed. Please make sure you have JavaScript enabled and try again (DB Error).';

        }


        // If no errors, process the order:
        if (empty($errors)) {

            try {


                \Stripe\Stripe::setApiKey($stripe_secret);


                $customer = \Stripe\Customer::create(array(
                    'email' => $email,
                    'source' => $token
                ));


                $charge = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $amount,
                    'currency' => 'eur',
                    "description" => $order,

                ));


                $stripe_id = $charge->id;


                if ($charge->paid == true) {

                    //Success

                    isset($voucher_settings['voucher_valid']) ? $voucher_years_valid = $voucher_settings['voucher_valid'] : $voucher_years_valid = 1;

                    if(isset($voucher_settings['phorest_demo_mode'])){
                        //Staging
                        $phorest_endpoint = "platform-staging.phorest.com";
                    }else{
                        //Live
                        $phorest_endpoint = "platform.phorest.com";
                    }

                    $this->phorest_api = new PhorestApi($this->phorest_settings['phorest_username'], $this->phorest_settings['phorest_password'], $this->phorest_settings['branch_id'], $this->phorest_settings['business_id'], $phorest_endpoint);


                    $order_info = $this->get_order($order);

                    $sms_marketing = (bool) $order_info->smsmarketing;

                    $email_marketing = (bool) $order_info->emailmarketing;


                    $create_client = $this->phorest_api->createVoucherClient($this->phorest_api->formatMobileNumber($order_info->mobile), $order_info->email, $order_info->firstname, $order_info->lastname,$sms_marketing, $email_marketing);


                    $voucher_meta = $this->phorest_api->createVoucher($create_client, $voucher_amount, $voucher_years_valid);


                    $this->phorest_update_order($order, 1, $stripe_id, false, array('voucher_number' => $voucher_meta['serialNumber'], 'voucher_expiry' => strtotime($voucher_meta['expiryDate']), 'voucher_meta' => json_encode($voucher_meta)));


                    $this->phorest_send_customer_email($order);

                    $this->phorest_send_admin_email($order);


                    $_SESSION['phorest']['order'] = $order;

                    $result_page = get_the_permalink($voucher_settings['voucher_page']) . "?action=result";


                    header("location: $result_page");


                    exit;





                } else {

                    //Declined
                    $this->phorest_update_order($order, 2, "Card Declined", true);


                }

            } catch (Stripe\Error\Card $e) {

                $msg = $e->getJsonBody();
                $err = $msg['body'];


                $this->phorest_update_order($order, 2, $err['message'], true);

                //Failure and log it

            } catch (Stripe\Error\ApiConnection $e) {

                $msg = $e->getJsonBody();
                $err = $msg['body'];


                $this->phorest_update_order($order, 2, $err['message'], true);


            } catch (Stripe\Error\InvalidRequest $e) {

                $msg = $e->getJsonBody();
                $err = $msg['body'];

                $this->phorest_update_order($order, 2, $err['message'], true);


            } catch (Stripe\Error\Base $e) {
                $msg = $e->getJsonBody();
                $err = $msg['body'];

                $this->phorest_update_order($order, 2, $err['message'], true);


            }

        } else {

            //Errors

            $this->phorest_update_order($order, 2, implode(', ', $errors), true);


        }


        wp_die();


    }

    public function phorest_update_order($order, $status, $msg, $redirect = false, $update_data = array())
    {

        global $wpdb;

        $voucher_settings = get_option("voucher_settings");


        $table_name = $wpdb->prefix . "voucher_orders";


        $data = $update_data;

        $data['status'] = $status;
        $data['stripe_response'] = $msg;
        $data['status'] = $status;



        $update = $wpdb->update($table_name, $data, array('order_id' => $order));


        if ($update && $redirect) {

            $_SESSION['phorest']['order'] = $order;

            $result_page = get_the_permalink($voucher_settings['voucher_page']) . "?action=result";
            header("location: $result_page");
            exit;

        } else {

            return $update;
        }


    }

    public function phorest_send_customer_email($order_id)
    {



        $voucher_settings = get_option("voucher_settings");

        $customer_email_text = $voucher_settings['customer_email'];
        $customer_email_footer = $voucher_settings['customer_email_footer'];


        $this->phorest_generate_voucher_pdf($order_id, 'F');


        $order = $this->get_order($order_id);

        $subject = "Your Gift Voucher";

        $to = $order->email;

        $body_data = str_replace(array('[customer_firstname]', '[customer_lastname]', '[order_date]', '[voucher_amount]', '[voucher_message]', '[voucher_code]', '[voucher_terms]', '[voucher_expiry]'), array($order->firstname, $order->lastname, $order->order_date, $order->voucher_amount, $order->message, $order->voucher_number, $voucher_settings['tos_text'], $order->voucher_expiry ), $customer_email_text);

        ob_start();

        include(PHOREST_VIEWS_DIR . "frontend/email-template.php");


        $template = ob_get_contents();

        ob_clean();

        $body = str_replace(array('[email_content]', '[email_title]', '[email_footer]'), array($body_data, 'Thank you for your purchase!', $customer_email_footer), $template);

        $site_name = get_bloginfo('name');

        $from_email = get_bloginfo('admin_email');

        $attachments = array(PHOREST_BASE_DIR . "assets/tmppdfs/GF-$order_id.pdf");

        $header = 'MIME-Version: 1.0' . PHP_EOL;
        $header .= 'From: =?UTF-8?B?' . base64_encode($site_name) . '?= <' . $from_email . '>' . PHP_EOL;
        $header .= 'Content-Type: text/html; charset="utf-8"' . PHP_EOL;
        $header .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

        if (wp_mail($to, $subject, $body, $header, $attachments)) {
            //delete temp files
            unlink(PHOREST_BASE_DIR . "assets/tmppdfs/GF-$order_id.pdf");
            return true;

        } else {
            return false;
        }




    }

    public function phorest_send_admin_email($order_id)
    {

        $voucher_settings = get_option("voucher_settings");

        isset($voucher_settings['notification_email']) ? $admin_email = $voucher_settings['notification_email'] : $admin_email = get_bloginfo("admin_email");

        $customer_email_text = $voucher_settings['customer_email'];
        $customer_email_footer = $voucher_settings['customer_email_footer'];


        $order = $this->get_order($order_id);

        $subject = "New Gift Voucher Purchase";

        $body_data = str_replace(array('[customer_firstname]', '[customer_lastname]', '[order_date]', '[voucher_amount]', '[voucher_message]', '[voucher_code]', '[voucher_terms]', '[voucher_expiry]'), array($order->firstname, $order->lastname, $order->order_date, $order->voucher_amount, $order->message, $order->voucher_number, $voucher_settings['tos_text'], $order->voucher_expiry ), $customer_email_text);

        ob_start();

        include(PHOREST_VIEWS_DIR . "frontend/email-template.php");


        $template = ob_get_contents();

        ob_clean();

        $body = str_replace(array('[email_content]', '[email_title]', '[email_footer]'), array($body_data, $subject, $customer_email_footer), $template);

        $site_name = get_bloginfo('name');

        $from_email = get_bloginfo('admin_email');


        $header = 'MIME-Version: 1.0' . PHP_EOL;
        $header .= 'From: =?UTF-8?B?' . base64_encode($site_name) . '?= <' . $from_email . '>' . PHP_EOL;
        $header .= 'Content-Type: text/html; charset="utf-8"' . PHP_EOL;
        $header .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

        if (wp_mail($admin_email, $subject, $body, $header)) {
            return true;

        } else {
            return false;
        }



    }


    function install_db_tables()
    {

        global $wpdb;


        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . "voucher_orders";


        $sql = "CREATE TABLE {$table_name} (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_date` varchar(50) NOT NULL,
            `firstname` text NOT NULL,
            `lastname` varchar(150) NOT NULL,
            `email` varchar(150) NOT NULL,
            `mobile` varchar(100) NOT NULL,
            `message` text NOT NULL,
            `voucher_name` text NOT NULL,
            `voucher_amount` varchar(50) NOT NULL,
            `voucher_meta` text NOT NULL,
            `client_meta` text NOT NULL,
            `voucher_number` text NOT NULL,
            `voucher_expiry` text NOT NULL,
            `stripe_token` varchar(50) NOT NULL,
            `stripe_response` varchar(50) NOT NULL,
            `status` int(11) NOT NULL,
            `smsmarketing` int(11) NOT NULL,
            `emailmarketing` int(11) NOT NULL,
            PRIMARY KEY  (order_id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);


    }

    /*
    * Returns array of posts by post_type
    * Used in our settings page
    */
    public function phorest_list_posts($post_type = "page")
    {

        // The Query
        $the_query = new WP_Query(array('post_type' => $post_type, 'posts_per_page' => -1));


        $pages = array();

        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();

                $pages[] = array('post_id' => get_the_ID(), 'post_title' => get_the_title());
            }

            /* Restore original Post Data */
            wp_reset_postdata();

        }


        return $pages;


    }


    function phorest_generate_voucher_pdf($order_id="", $format="I")
    {


        if(empty($order_id)){
            //We need this to generate a voucher via admin.
            isset($_GET['order_id']) ? $order_id = $_GET['order_id'] : $order_id = "";

        }

        $voucher_settings = get_option("voucher_settings");

        isset($voucher_settings['voucher_footer']) ? $voucher_footer = $voucher_settings['voucher_footer'] : $voucher_footer = "";
        isset($voucher_settings['voucher_text']) ? $voucher_text = $voucher_settings['voucher_text'] : $voucher_text = "";

        isset($voucher_settings['voucher_bg_colour']) ? $voucher_bg_colour = $voucher_settings['voucher_bg_colour'] : $voucher_bg_colour = "#ffffff";
        isset($voucher_settings['voucher_font_colour']) ? $voucher_font_colour = $voucher_settings['voucher_font_colour'] : $voucher_font_colour = "#000000";


        //$pdf = new PhorestPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = new PhorestPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Graphedia Voucher Plugin');
        $pdf->SetTitle('Gift Voucher');


        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


        $pdf->SetAutoPageBreak(TRUE, 0);


        $pdf->SetFont('helvetica', '', 10);

        $pdf->AddPage();


        // column titles
        $header = array('From', 'To', 'Voucher Value', 'Issued Date', 'Message', 'Expiry Date');

        $date_format = get_option("date_format");

        if(!empty($order_id)){

            $order = $this->get_order($order_id);

            //Double check if paid
            /*
            if((int) $order->status !== 1){
                wp_die("Invalid Order Status.");
            }
            */
            $data = array($order->firstname." ".$order->lastname, $order->voucher_name,  "&euro; ".number_format($order->voucher_amount,2), date($date_format, $order->order_date), $order->message, date($date_format, $order->voucher_expiry));
            $voucher_code = $order->voucher_number;


        }else{
            //TEST Preview
            $data = array("John Doe", "Jane Doe", "&euro; 50.00", "25th May 2016", "Happy Birthday Dear -  Have a great day Happy Birthday Dear -  Have a great day Happy Birthday Dear -  Have a great day !");
            $voucher_code = "SAMPLE";
        }

        $pdf->voucherTable($header, $data, $voucher_code);


        $pdf->lastPage();


        if($format == "F"){
            $path = PHOREST_BASE_DIR . "assets/tmppdfs/GF-$order_id.pdf";


            $pdf->Output($path, $format);


        }else{

            $path = "voucher.pdf";


            $pdf->Output($path, $format);

            wp_die();
        }

    }


    function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
    {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; //Invalid hex color code
        }
        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }


    function order_status($status){

        switch($status){

            case "0":

                return "<span class='order-pending'>Pending</span>";

                break;

            case "1":

                return "<span class='order-paid'>Paid</span>";

                break;

            case "2":

                return "<span class='order-failed'>Failed</span>";

                break;

            default;

                return "<span class='order-pending'>Pending</span>";


        }

    }


    function order_modal($order_id){


        $order = $this->get_order($order_id);

        $date_format = get_option("date_format");


        ob_start();

        include(PHOREST_VIEWS_DIR . "admin/order-modal.php");


        $output = ob_get_contents();

        ob_clean();


        return $output;

    }


    function resend_customer_email(){


        isset($_GET['order_id']) ? $order_id = $_GET['order_id'] : $order_id = "";

        if(empty($order_id)){

            echo json_encode(array('status' => 'error', 'msg' => 'Order ID is required!'));

        }else {

            $order = $this->get_order($order_id);

            if ((int) $order->status !== 1) {

                echo json_encode(array('status' => 'error', 'msg' => 'Sorry this order has not been paid for yet.'));

            } else {

                if ($this->phorest_send_customer_email($order_id)) {
                    echo json_encode(array('status' => 'success', 'msg' => 'Email sent!'));

                } else {

                    echo json_encode(array('status' => 'error', 'msg' => 'Sorry the email could not be sent.'));

                }
            }

        }

        wp_die();

    }

}

$phorest = new PhorestVoucher();