<?php
defined('ABSPATH') or die("Cannot access pages directly.");
/*
Plugin Name: Phorest Voucher WP
Plugin URI: http://www.graphedia.ie
Description: Create a voucher using Phorest Salon API
Version: 1.0
Author: Brendan Doyle @ Graphedia
Author URI: http://www.graphedia.ie
*/

define("PHOREST_ASSETS_PATH", plugin_dir_url(__FILE__) . "assets/");
define('PHOREST_VIEWS_DIR', plugin_dir_path(__FILE__) . "views/");
define('PHOREST_BASE_DIR', plugin_dir_path(__FILE__));


include("vendor/autoload.php");
include("includes/PhorestPdf.php");
include("includes/PhorestApi.php");
include("includes/PhorestVoucher.php");
include("includes/OrdersTable.php");


register_activation_hook( __FILE__, array($phorest, 'install_db_tables'));
