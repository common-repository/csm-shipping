<?php

/*************** function to create and save consignment data when an order is completed ***************/

function CSML_add_shipping_meta_to_order_at_woocommerce_thankyou($order_id){

  $order = new WC_Order( $order_id );
  csmlogistics_shipping_method();
  $cObj = new CsmLogistics_Shipping_Method();

  $shipping_method = $order->get_shipping_methods();
  $shipping_method = array_slice($shipping_method, 0, 1);
  if(isset($shipping_method[0]['method_id']) && $shipping_method[0]['method_id'] == 'csmlogistics'){
    $shipping_data = get_option('woocommerce_csmlogistics_'.$shipping_method[0]['instance_id'].'_settings');
    $csm_shipping_service_code = $shipping_data['csm_mapped_service'];
    update_post_meta($order_id, '_order_csm_service_id', $csm_shipping_service_code);

    $shipConsignmentResponse = CSML_get_ship_consignment($order, $cObj->settings, $csm_shipping_service_code,'Ship');

    $eu_countries = array(
        'AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE',
        'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR',
        'IE', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PL',
        'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA',
    );

	 $shipperduttypaid    = $cObj->settings['shipperduttypaid'];  // value yes or no
	 if (in_array($order->get_shipping_country(), $eu_countries)){
	  	// for EU Country
	  	$DutiesAndTaxesPaidByShipper = 0;
		$DutiableContents = 0;
	} elseif($shipperduttypaid=='yes') {
			$DutiesAndTaxesPaidByShipper = 1;
			$DutiableContents = 1;
		} else {
			$DutiesAndTaxesPaidByShipper = 0;
			$DutiableContents = 1;
		} 
	
	if($shipConsignmentResponse['ResponseResult']['ResponseCode'] == '01'){
		$consignmentData = serialize($shipConsignmentResponse['Consignment']);
		update_post_meta($order_id, '_order_consignment_data', $consignmentData);
		if(($DutiesAndTaxesPaidByShipper == 0 && $DutiableContents == 1) || ($DutiesAndTaxesPaidByShipper == 1 && $DutiableContents == 1) ){
		$CommercialInvoiceData = serialize($shipConsignmentResponse['Consignment']['CommercialInvoice']);
		update_post_meta($order_id, '_order_commercial_invoice_data', $CommercialInvoiceData);
		}
	} else {
		$consignmentData = serialize($shipConsignmentResponse['ResponseResult']);
		update_post_meta($order_id, '_order_consignment_error_data', $consignmentData);
		} 
  	}
}

/*************** end function to create and save consignment data when an order is completed ***************/

/*************** function to show delivery service in admin section edit order ***************/

function CSML_cmslogistics_editable_order_meta_general($order){ 
	$order_id = $order->get_id();
	csmlogistics_shipping_method();
	?>
		<?php if( $order->has_shipping_method('csmlogistics') ) { ?> 
		<br class="clear" />
		<h4>Delivery Service <a href="#" class="edit_delivery">Edit</a></h4> 
		<?php
			echo '<p>Shipping method: '. $order->get_shipping_method() .'</p>';
			$cObj = new CsmLogistics_Shipping_Method();
			$is_csm_id = get_post_meta( $order_id, '_order_csm_service_id', true );
			$order_consignment_data = get_post_meta( $order_id, '_order_consignment_data', true );
			$order_commercial_invoice_data = get_post_meta( $order->get_id(), '_order_commercial_invoice_data', true );
			$base_info = unserialize($order_consignment_data);
	    	$invoice_data = unserialize($order_commercial_invoice_data);
	    	$baseinfo_label = $base_info['Parcel']['LabelData'];
	    	if($invoice_data==''){
	    		$hide_invoice_btn = "hide_invoice_btn";
	    	}else{
	    		$hide_invoice_btn ="";
	    	}

			if( $is_csm_id ){ ?>			
			<?php 
			woocommerce_wp_select( array(
					'id' => 'delivery_service',
					'label' => 'Delivery Service:',
					'value' => $is_csm_id,
					'options' => $cObj->csm_service_codes,				
					'wrapper_class' => 'form-field-wide',
					)
				 );		
			?>		
			<br class="clear" />
			<p class="custom_download_label print_custom_width"><a id="dwnldLnk" data-attr="<?php echo $baseinfo_label; ?>" href="javascript:void(0);">Print Label</a>
			<a id="dwnldLnk" class="hideifempty <?php echo $hide_invoice_btn; ?>" data-attr="<?php echo $invoice_data; ?>" href="javascript:void(0)" download="commercial-invoice.pdf">Print Commercial Invoice</a></p>
			
		<?php } else { ?>
				<div class="error message"> 
				<?php 
					$error_data = get_post_meta( $order_id, '_order_consignment_error_data', true );
					if(!empty($error_data)){
						echo "<p>".$error_data['ResponseDescription']."</p>";
					}
				?>
				</div>
				<div class="edit_delivery"> 
				<?php 
				woocommerce_wp_select( array(
						'id' => 'delivery_service',
						'label' => 'Delivery Service:',
						'value' => '',
						'options' => $cObj->csm_service_codes,				
						'wrapper_class' => 'form-field-wide',
						)
					 );		
				?>
				</div>
			<?php }
		}
	}

/*************** end function to show delivery service in admin section edit order ***************/

/*************** function to save new data when admin changes delivery service ***************/

function CSML_cmslogistics_save_general_details( $ord_id ){
	$settings = get_option("woocommerce_csmlogistics_settings");
	$order = new WC_Order( $ord_id );
	$items = $order->get_items();
	$shipping_method = $order->get_shipping_methods();
    $shipping_method = array_slice($shipping_method, 0, 1);

	if(isset($_POST[ 'delivery_service' ]) && !empty($_POST[ 'delivery_service' ])){
		$csm_service_id = get_post_meta( $ord_id, '_order_csm_service_id', true );
		if($csm_service_id != sanitize_text_field($_POST[ 'delivery_service' ])){
			$csm_shipping_service_code = sanitize_text_field($_POST[ 'delivery_service' ]);
			if( metadata_exists( 'post', $ord_id, '_order_consignment_data')){
				$order_consignment_data = get_post_meta($ord_id, '_order_consignment_data', true);	
				$consignment_data = unserialize($order_consignment_data);
				$consignmentNumber = $consignment_data['ConsignmentNumber'];

				if($shipping_method[0]['method_id'] == 'csmlogistics'){	
					$voidShipConsignmentResponse = CSML_void_ship_consignment($settings, $consignmentNumber,'Void');
					if($voidShipConsignmentResponse['ResponseResult']['ResponseCode'] == '01'){
						$shipConsignmentResponse = CSML_get_ship_consignment($order, $settings, $csm_shipping_service_code,'Ship');
						if($shipConsignmentResponse['ResponseResult']['ResponseCode'] == '01'){
							$consignmentData = serialize($shipConsignmentResponse['Consignment']);
							update_post_meta($ord_id, '_order_consignment_data', $consignmentData);
							update_post_meta( $ord_id, '_order_csm_service_id', wc_clean( $_POST[ 'delivery_service' ] ) );
						}  						
					}
				}
			} else {
				if($shipping_method[0]['method_id'] == 'csmlogistics'){
					$shipConsignmentResponse = CSML_get_ship_consignment($order, $settings, $csm_shipping_service_code,'Ship');
					if($shipConsignmentResponse['ResponseResult']['ResponseCode'] == '01'){
						$consignmentData = serialize($shipConsignmentResponse['Consignment']);
						update_post_meta($ord_id, '_order_consignment_data', $consignmentData);
						update_post_meta( $ord_id, '_order_csm_service_id', wc_clean( $_POST[ 'delivery_service' ] ) );
					}
				}
			}			
		}
	}
}

/*************** end function to save new data when admin changes delivery service ***************/

function CSML_add_custom_order_status_actions_button($actions,$order) { 
	 if( $order->has_shipping_method('csmlogistics') ) {  
	echo '<a href="#" class="order-preview custom-preview-img" data-order-id="'.$order->get_id().'" title="Preview">  </a>';
	}
	return $actions;
}
				
function CSML_admin_order_preview_add_custom_meta_data( $data, $order ) {
	$csm_id = get_post_meta( $order->get_id(), '_order_csm_service_id', true );
	csmlogistics_shipping_method();
	$cObj = new CsmLogistics_Shipping_Method();
	$custom_value = $cObj->csm_service_codes[$csm_id];
	if( $custom_value ){
	    $data['custom_key'] = $custom_value; 
	    $data['order_id'] = $order->get_id();

	    $order_consignment_data = get_post_meta( $order->get_id(), '_order_consignment_data', true );
	    $order_commercial_invoice_data = get_post_meta( $order->get_id(), '_order_commercial_invoice_data', true );
	    //$order_commercial_invoice_data = get_post_meta( $order->get_id(), '_order_commercial_invoice_data', true );
		$base_info = unserialize($order_consignment_data);
		$invoice_data = unserialize($order_commercial_invoice_data);
	    $data['order_consignment_data'] = $base_info['Parcel']['LabelData'];
	    if($invoice_data){
	    $data['commercial_invoice_data'] = $invoice_data;
	}else{
		$data['commercial_invoice_data'] = $invoice_data;
		$data['hideclass'] = 'hide_invoice_btn';
	}
	}
	return $data;
}

function CSML_custom_display_order_data_in_admin(){ 
    echo '<div class="wc-order-csm-shipping wc-order-preview-addresses">
    <div class="wc-order-preview-address">
    <strong>Delivery Service :</strong> 
    {{data.custom_key}}';
    //echo '<p><a id="dwnldLnk" href="data:application/octet-stream;base64,{{data.order_consignment_data}}" download="label.pdf">Print Label</a></p></div></div>';
    echo '<p class="print_custom_width"><a id="dwnldLnk" data-attr="{{data.order_consignment_data}}" href="javascript:void(0)" download="label.pdf">Print Label</a>
    <a id="dwnldLnk" class="hideifempty {{data.hideclass}}" data-attr="{{data.commercial_invoice_data}}" href="javascript:void(0)" download="commercial-invoice.pdf">Print Commercial Invoice</a></p></div></div>';  
}


function CSML_custom_orders_list_bulk_actions( $bulk_actions ) {
    $bulk_actions['print_labels'] = 'Print CSM Labels';
    return $bulk_actions;
}

function CSML_custom_orders_list_action_print_labels( $redirect_to, $action, $post_ids ) {
    if ( $action === 'print_labels' ){
        $processed_ids = array(); // Initializing

        foreach ( $post_ids as $post_id ) {
           $processed_ids[] = $post_id; // Adding processed order IDs to an array
        }

        // Adding the right query vars to the returned URL
        $redirect_to = add_query_arg( array(
            'print_labels' => '1',
            'processed_count' => count( $processed_ids ),
            'processed_ids' => implode( ',', $processed_ids ),
        ), $redirect_to );
    }
    return $redirect_to;
} 

function CSML_custom_orders_list_bulk_action_admin_notice() {
    global $pagenow;
    if ( 'edit.php' === $pagenow && isset($_GET['post_type'])
    && 'shop_order' === $_GET['post_type'] && isset($_GET['print_labels'])){

		$err_count = 0;
		$processed_ids =  explode( ',', sanitize_text_field($_REQUEST['processed_ids']) );
		foreach ($processed_ids as $aVal) {
			$order = wc_get_order($aVal);
			$shipping_method = $order->get_shipping_methods();
			$shipping_method = array_slice($shipping_method, 0, 1);
			if($shipping_method[0]['method_id'] != 'csmlogistics'){
				$err_count = 1;
				break;
			}
		}
        
       $count = intval( sanitize_text_field($_REQUEST['processed_count']) );  

       if(sanitize_text_field($_GET['print_labels']) == 1 && $err_count == 0) { ?>       
      	<ul class="download_multiple_labels" style="display: none;">
      	<?php 
      	
        foreach ( $processed_ids as $order_ids ) {
        	$service_csm_id = get_post_meta( $order_ids, '_order_csm_service_id', true );
        	$order = wc_get_order($order_ids );
    		$shipping_method = $order->get_shipping_methods();
    		$shipping_method = array_slice($shipping_method, 0, 1);
    		
			if($shipping_method[0]['method_id'] == 'csmlogistics' && !empty($service_csm_id)){			
		        $basic_infos_meta = get_post_meta( $order_ids, '_order_consignment_data', true );
				$basic_infos = unserialize($basic_infos_meta);
				$label_print_datas['order_consignment_data'] = $basic_infos['Parcel']['LabelData']; 
				
				echo '<li><a id="dwnldLnk" data-attr="'.$label_print_datas['order_consignment_data'].'" href="javascript:void(0)" download="label.pdf">Print Label</a></li>';

				echo '<a id="back_to_order" href="'.home_url().'/wp-admin/edit.php?post_type=shop_order" style="display:none;"></a>';
			}	
        }
        echo '</ul>';
        echo '<div id="overlay"><div id="progress"></div></div>';
    	echo '<button id="cont" class="count_process" style="display:none;">cont</button>';
    	printf( '<div class="notice notice-success fade is-dismissible"><p>' .
            _n( 'Print Label for %s Order.',
            'Print Label for %s Orders.',
            $count,
            'woocommerce'
        ) . '</p></div>', $count );
	    } else {
	    	printf( '<div class="notice notice-error fade is-dismissible"><p>' .
            _n( 'Select only those orders which have CSM applied shipping methods.',
            'Select only those orders which have CSM applied shipping methods.',            
            'woocommerce'
        	) . '</p></div>');
	    }    
    }
}

/********************* Calculate total order weight ************************/

function CSML_save_weight_order( $order_id ) {
    $weight = WC()->cart->get_cart_contents_weight();
    update_post_meta( $order_id, '_cart_weight', $weight );
}