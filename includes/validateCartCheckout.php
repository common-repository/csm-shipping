<?php
    function CSML_csmlogistics_validate_order( $posted )   {
        $packages = WC()->shipping->get_packages();

        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

        

        /*************** new code by developer ******************************/
        
        $rate_table = array();
		foreach($packages as $package){
		       foreach($package['rates'] as $key=>$val){		 
		              $rate_table[$key]= $val;
		       }	
		}

		$shipping_price = $rate_table[WC()->session->get( 'chosen_shipping_methods' )[0]]->cost;

		if($shipping_price == 0.0){
			$message = sprintf( __( 'Something went wrong, Unable to calculate price.', 'csmlogistics' ), $weight, $weightLimit, $CsmLogistics_Shipping_Method->title );
                             
            $messageType = "error";

            if( ! wc_has_notice( $message, $messageType ) ) {                         
                wc_add_notice( $message, $messageType );                      
            }
		}
        
         /**************** end *****************/

        if( is_array( $chosen_methods) && in_array( 'csmlogistics', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[$i] != "csmlogistics" ) {                             
                    continue;
                }
 
                $CsmLogistics_Shipping_Method = new CsmLogistics_Shipping_Method();

                $weightLimit = (int) $CsmLogistics_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'csmlogistics' ), $weight, $weightLimit, $CsmLogistics_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {                         
                            wc_add_notice( $message, $messageType );                      
                        }
                }
            }       
        } 
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'CSML_csmlogistics_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'CSML_csmlogistics_validate_order' , 10 );
?>