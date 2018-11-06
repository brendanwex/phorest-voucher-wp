<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class OrdersTable extends WP_List_Table {


	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'Order',     //singular name of the listed records
			'plural'   => 'Orders',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		) );

	}


	function column_default( $item, $column_name ) {


	    global $phorest;

		switch ( $column_name ) {


			case 'order_id':


			    $output = "";

				$output .= $this->column_title( $item );


				$output .= $phorest->order_modal($item['order_id']);


				return $output;

				break;


			case 'customer_name':


				return $item['firstname'] . " " . $item['lastname'];

				break;


            case 'voucher_number':


                if(isset($item['voucher_number'])){
                    return $item['voucher_number'];
                }else{
                    return "-";
                }

                break;


			case 'voucher_amount':


				return "&euro; ".number_format($item['voucher_amount'],2);

				break;



			case 'order_date':


				return date( "d-m-Y", $item['order_date'] );



				break;


            case 'expiry_date':


                if(isset($item['voucher_expiry'])){
                    return date( "d-m-Y", $item['voucher_expiry'] );
                }else{
                    return "-";
                }



                break;

			case 'client':

				return $item['email']."<br />".$item['mobile'];


				break;



            case 'status':

                return $phorest->order_status($item['status']);


            break;




			default:

		}
	}


	function get_columns() {
		$columns = array(
			'order_id'      => 'Order ID',
			'customer_name' => 'Customer',
            'voucher_number' => 'Voucher No.',
            'voucher_amount' => 'Amount',
			'order_date'     => 'Date',
            'expiry_date'     => 'Expiry',

            'client'     => 'Client',
			'status'      => 'Status',
		);

		return $columns;
	}


	function get_sortable_columns() {
		$sortable_columns = array(
			'order_id' => array( 'order_id', true ),     //true means it's already sorted
			'customer_name'        => array( 'customer_last', false ),
			'order_date'         => array( 'order_date', false ),
			'status'         => array( 'status', false ),



		);

		return $sortable_columns;
	}


	function column_title( $item ) {



		$title = "GF-".$item['order_id'];

		$actions = array();

		//Build row actions
        $actions['view'] =  "<a href='#' class='open-phorest-modal' data-id='{$item['order_id']}'>View Order</a>";
        if($item['status'] == 1) {
            $actions['print'] = "<a href='".admin_url('admin-ajax.php?action=phorest_voucher_preview&order_id='.$item['order_id'])."' target='_blank'>Print Voucher</a>";
        }


		//Return the title contents
		return sprintf( '%1$s %2$s',
			/*$1%s*/
			$title,
			/*$3%s*/
			$this->row_actions( $actions )
		);
	}


	function prepare_items( $s = "" ) {
		global $phorest; //This is used only if making any database queries


		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->get_items_per_page( 'files_per_page', 24 );

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );


		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		//$data = $wpdb->get_results("SELECT * FROM $table_name", "ARRAY_A");


		$data = $phorest->get_orders($s);


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/


		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );


		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
		) );
	}


}
