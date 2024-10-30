<?php
/**
 * Plugin Name: CSM Shipping
 * Description: Custom Shipping Method for WooCommerce
 * Version: 1.0.0
 * Author: CSM Logistics
 * Author URI: https://www.csmlogistics.co.uk/

 * WC requires at least: 3.7.1
 * WC tested up to: 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	die("You can't access this file directly"); // disable direct access
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	
	define('CSML_shipping_plugin_path', plugin_dir_path(__FILE__));
	if ( !class_exists( 'CSML_shipping_Pluign' ) ) {
	    class CSML_shipping_Pluign {
	    	public $aParams;
	    	public function __construct(){

				add_action( 'activated_plugin', 'CSML_shipping_activation_redirect' );

				function CSML_shipping_activation_redirect( $plugin ) {
				    if( $plugin == plugin_basename( __FILE__ ) ) { 
				      exit( wp_redirect( admin_url('admin.php?page=csm-shipping' ) ) );
				    }
				}

				function CSML_shipping_deactivation() {
				    remove_action( 'woocommerce_shipping_init', 'csmlogistics_shipping_method' );	
				    remove_filter( 'woocommerce_shipping_methods', 'add_csmlogistics_shipping_method' );
				    flush_rewrite_rules();
				}

				include CSML_shipping_plugin_path . 'includes/calculateShippingFront.php';
				include CSML_shipping_plugin_path . 'includes/addShipLabel.php';
				include CSML_shipping_plugin_path . 'includes/validateCartCheckout.php';
				//include CSML_shipping_plugin_path . 'includes/trackOrder.php';

				include CSML_shipping_plugin_path . 'includes/api/Rate.php';
				include CSML_shipping_plugin_path . 'includes/api/Ship.php';
				include CSML_shipping_plugin_path . 'includes/api/Track.php';
				include CSML_shipping_plugin_path . 'includes/api/Void.php';


				add_action( 'admin_enqueue_scripts', array(&$this,'CSML_admin_enqueue_scripts'));
				add_action( 'admin_menu', array($this, 'CSML_add_wps_csm_logistics_admin_menu'));
				add_action('wp_enqueue_scripts', 'CSML_callback_for_setting_up_scripts');
				function CSML_callback_for_setting_up_scripts(){
					wp_enqueue_style('wps-admin-csm-services-style', plugins_url( 'includes/assets/css/frontend-css.css', __FILE__ ) );
					wp_enqueue_script('jquery');
					wp_enqueue_script('wps-admin-csm-services-script123', plugins_url( 'includes/assets/js/frontend-js.js', __FILE__ ) );
					wp_localize_script( 
			            'wps-admin-csm-services-script123', 
			            'ajax_object', 
			            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
			          );
				}

				add_shortcode('track-order', array($this, 'CSML_track_order_shortcode'));
				add_action( 'wp_ajax_track_order_form_response', array(&$this,'track_order_form_response'));
				add_action( 'wp_ajax_nopriv_track_order_form_response',array(&$this,'track_order_form_response') );
 
			    add_action( 'woocommerce_shipping_init', 'csmlogistics_shipping_method' );
			    //add_action( 'woocommerce_order_status_completed', 'cmslogistics_woocommerce_order_status_completed', 10, 1 );
				add_action( 'woocommerce_thankyou', 'CSML_add_shipping_meta_to_order_at_woocommerce_thankyou', 10, 1 );
				//This action use for shiping method
				add_action( 'woocommerce_admin_order_data_after_order_details', 'CSML_cmslogistics_editable_order_meta_general' );
				add_action( 'woocommerce_process_shop_order_meta', 'CSML_cmslogistics_save_general_details' );

				add_action( 'woocommerce_checkout_update_order_meta', 'CSML_save_weight_order' );
			    
			    function add_csmlogistics_shipping_method( $methods ) {
			        $methods['csmlogistics'] = 'CsmLogistics_Shipping_Method';
			        return $methods;
			    }
			    add_filter( 'woocommerce_shipping_methods', 'add_csmlogistics_shipping_method' );


				add_filter( 'woocommerce_admin_order_preview_get_order_details', 'CSML_admin_order_preview_add_custom_meta_data', 10, 2 );

	    		add_action( 'woocommerce_admin_order_data_after_shipping_address', 'CSML_custom_display_order_data_in_admin' );
	    		//This action use for order preview button
	    		add_action( 'woocommerce_admin_order_preview_end', 'CSML_custom_display_order_data_in_admin' );
	    		//This action use for order preview button

				add_filter( 'woocommerce_admin_order_actions', 'CSML_add_custom_order_status_actions_button', 10, 3 );
				//This filter use for order list action preview button image 
			    if (get_option("woocommerce_csmlogistics_settings")) {
			      	$this->aParams['woocommerce_csmlogistics_settings']  = get_option("woocommerce_csmlogistics_settings");
			    } 

			    //Add a bulk action to Orders bulk actions dropdown
				add_filter( 'bulk_actions-edit-shop_order', 'CSML_custom_orders_list_bulk_actions' );
				//Process the bulk action from selected orders
				add_filter( 'handle_bulk_actions-edit-shop_order', 'CSML_custom_orders_list_action_print_labels', 10, 3 );

				//Display the results notice from bulk action on orders
				add_action( 'admin_notices', 'CSML_custom_orders_list_bulk_action_admin_notice' );							

	    	} // constructor end

			public function CSML_admin_enqueue_scripts(){
				wp_enqueue_script('wps-admin-csm-custom-script', plugins_url( 'includes/assets/js/custom.js', __FILE__) );	
				wp_enqueue_style('wps-admin-csm-services-backend', plugins_url( 'includes/assets/css/backend-css.css', __FILE__ ) );

				//wp_enqueue_style('wps-admin-pcs-services-backend', plugins_url( 'includes/assets/css/jquery-ui.css', __FILE__ ) );

        		wp_enqueue_script( 'jquery-ui-core' );
        		wp_enqueue_script( 'jquery-ui-progressbar' );

				wp_localize_script( 
			            'wps-admin-csm-custom-script', 
			            'ajax_object', 
			            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
			          );

			}

	    	public function CSML_add_wps_csm_logistics_admin_menu(){
	    		add_menu_page( 'CSM Shipping','CSM Shipping','manage_options','csm-shipping', array(&$this,'CSML_shipping_info_page'),'dashicons-admin-generic',22 );
	    	}

	    	public function CSML_shipping_info_page(){
	    		$this->set_template('overview');
	    	}

	    	public function CSML_track_order_shortcode(){
	    		$this->set_template('trackOrder', $this->aParams);
	    	}

	    	public function track_order_form_response($params = array()){
				$consignment_number = sanitize_text_field($_POST['consignment_number']);	
				ob_start(); 
				 $trackRecord = CSML_get_csm_shipping_track_record($this->aParams , $consignment_number);
				 $trackRecord['flag'] = 1;
				 session_start();
				 $_SESSION['track_response'] = $trackRecord;
				  $result['message'] = true;
					echo json_encode($result);
					wp_die();
				}

			public function set_template($aTemplate, $aData = null){				
				include CSML_shipping_plugin_path . 'includes/'.$aTemplate.'.php';
			}	
	    } // class end
	$wpsObj = new CSML_shipping_Pluign; 
	}
 }