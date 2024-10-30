<?php 
function CSML_get_ship_consignment($aOrder, $aSettings, $service_code, $action_type){
	
	global $woocommerce;	
	//get CSM account details

	$cartWeight = get_post_meta( $aOrder->get_order_number(), '_cart_weight', true );

	$ShipperNumber = $aSettings['shippernumber'];
	$ShipperUserName = $aSettings['shipperusername'];
	$ShipperPass = $aSettings['shipperpassword'];
	//get order items
    $items = $aOrder->get_items();
   	$store_address     = get_option( 'woocommerce_store_address' );
	$store_address_2   = get_option( 'woocommerce_store_address_2' );
	$store_city        = get_option( 'woocommerce_store_city' );
	$store_postcode    = get_option( 'woocommerce_store_postcode' );
	$currency		   = get_option('woocommerce_currency');

	// The country/state
	$store_raw_country = get_option( 'woocommerce_default_country' );

	// Split the country/state
	$split_country = explode( ":", $store_raw_country );

	// Country and state separated:
	$store_country = $split_country[0];

	$eu_countries = array(
        'AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE',
        'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR',
        'IE', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PL',
        'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA',
    );

	$shipperduttypaid    = $aSettings['shipperduttypaid'];  // value yes or no
	$order_id = trim(str_replace('#', '', $aOrder->get_order_number()));

	if (in_array($aOrder->get_shipping_country(), $eu_countries)){
	  	// for EU Country
	  	$DutiesAndTaxesPaidByShipper = 0;
		$DutiableContents = 0;
	} elseif($shipperduttypaid=='yes') { // for Non EU Country with Duty paid by shipper
		$DutiesAndTaxesPaidByShipper = 1;
		$DutiableContents = 1;
	} else {
		$DutiesAndTaxesPaidByShipper = 0; // for Non EU Country without Duty paid by shipper
		$DutiableContents = 1;
	}
	
    //get user data
    $userID = $aOrder->get_customer_id();
    $userData = get_user_meta($userID);
    $billing_email = $aOrder->get_billing_email();
    $shipping_company = $aOrder->get_shipping_company();
    if(empty($shipping_company)){
    	$shipping_company = $aOrder->get_shipping_first_name().' '.$aOrder->get_shipping_last_name();
    }
    $contact_name = $aOrder->get_shipping_first_name().' '.$aOrder->get_shipping_last_name();
    $billing_phone = $aOrder->get_billing_phone();

    // create Ship XML
	$products_xml = new SimpleXMLElement('<Request/>');

	$products_xml->addChild('RequestAction', $action_type);

	$account_xml = $products_xml->addChild('Account');
	$account_xml->addChild('ShipperNumber', $ShipperNumber);
	$account_xml->addChild('ShipperUserName', $ShipperUserName);
	$account_xml->addChild('ShipperPass', $ShipperPass);

	$consignment_xml = $products_xml->addChild('Consignment');
	$consignment_xml->addChild('Reference', "APITest");
	$consignment_xml->addChild('Description', "APITest");
	$consignment_xml->addChild('IsReturn', "0");
	$consignment_xml->addChild('LabelType', "PDF");
	$consignment_xml->addChild('ParcelType', "P");
	$consignment_xml->addChild('ServiceCode', $service_code);
	$consignment_xml->addChild('PickupDate', date('Y-m-d'));

	$notify_xml = $consignment_xml->addChild('Notify');
	$notify_xml->addChild('EmailAddress', $billing_email);
	$notify_xml->addChild('Language', "ENG");
	$notify_xml->addChild('Created', "1");
	$notify_xml->addChild('InTransit', "1");
	$notify_xml->addChild('Delivered', "1");
	$notify_xml->addChild('Exception', "1");

	$AdditionalOptions = $consignment_xml->addChild('AdditionalOptions');
	$AdditionalOptions->addChild('SignatureRequired', "0");
	$AdditionalOptions->addChild('AdultSignatureRequired', "0");
	$duty = $AdditionalOptions->addChild('Duty');
	$duty->addChild('DutiesAndTaxesPaidByShipper', $DutiesAndTaxesPaidByShipper);
	$duty->addChild('DutiableContents', $DutiableContents);

	$ShipFrom = $consignment_xml->addChild('ShipFrom');
	$ShipFrom->addChild('CompanyName', "Test API");
	$ShipFrom->addChild('ContactName', "Test API");
	$ShipFrom->addChild('ContactPhone', "014242 604252");
	$ShipFrom->addChild('ContactEmail', "mark@graphitedesign.com");
	$shipfromaddress = $ShipFrom->addChild('Address');
	$shipfromaddress->addChild('HouseNumber', "");
	$shipfromaddress->addChild('AddressLine1', $store_address);
	$shipfromaddress->addChild('AddressLine2', $store_address_2);
	$shipfromaddress->addChild('AddressLine3', "");
	$shipfromaddress->addChild('Town', $store_city);
	$shipfromaddress->addChild('County', "");
	$shipfromaddress->addChild('PostCode', $store_postcode);
	$shipfromaddress->addChild('ISOCountryCode', $store_country);

	$ShipTo = $consignment_xml->addChild('ShipTo');
	$ShipTo->addChild('CompanyName', $shipping_company);
	$ShipTo->addChild('ContactName', $contact_name);
	$ShipTo->addChild('ContactPhone', $billing_phone);
	$ShipTo->addChild('ContactEmail', $billing_email);
	$shiptoaddress = $ShipTo->addChild('Address');
	$shiptoaddress->addChild('HouseNumber', "");
	$shiptoaddress->addChild('AddressLine1', $aOrder->get_shipping_address_1());
	$shiptoaddress->addChild('AddressLine2', $aOrder->get_shipping_address_2());
	$shiptoaddress->addChild('AddressLine3', "");
	$shiptoaddress->addChild('Town', $aOrder->get_shipping_city());
	$shiptoaddress->addChild('County', $aOrder->get_shipping_state());
	$shiptoaddress->addChild('PostCode', $aOrder->get_shipping_postcode());
	$shiptoaddress->addChild('ISOCountryCode', $aOrder->get_shipping_country());

	$Parcel = $consignment_xml->addChild('Parcel');
	$Parcel->addChild('Weight', $cartWeight);
	$dimension = $Parcel->addChild('Dimensions');
	$dimension->addChild('Length', 10);
	$dimension->addChild('Width', 10);
	$dimension->addChild('Height', 10);
	$products = $Parcel->addChild('Products');

	foreach($items as $item => $values) { 
		$getProductDetail = wc_get_product( $values['product_id'] );
        $quantity = $values['quantity'];
        $price = get_post_meta($values['product_id'] , '_price', true);
        $sku = get_post_meta($values['product_id'] , '_sku', true);
        $ShortDescription = $getProductDetail->get_short_description();
        $DetailedDescription = $getProductDetail->get_description();
        $Weight = get_post_meta($values['product_id'] , '_weight', true);
        $title = $getProductDetail->get_title();

        // creating XML for products
		$product = $products->addChild('Product');
    	$product->addChild('ShortDescription', $ShortDescription);
    	$product->addChild('DetailedDescription', $DetailedDescription);
    	$product->addChild('Composition', "API Test");
    	$product->addChild('HarmonisedCode', $sku);
    	$product->addChild('UnitValue', $price);
    	$product->addChild('UnitWeight', $Weight);
    	$product->addChild('Quantity', $quantity);
    	$product->addChild('UnitsOfMeasure', "Kg");
    	$product->addChild('CountryOfOrigin', "GB");
    }

    if(($DutiesAndTaxesPaidByShipper == 0 && $DutiableContents == 1) || ($DutiesAndTaxesPaidByShipper == 1 && $DutiableContents == 1)){
	    $CommercialInvoice = $consignment_xml->addChild('CommercialInvoice');
	    $CommercialInvoice->addChild('InvoiceNumber', $order_id);
	    $CommercialInvoice->addChild('InvoiceDate', date('Y-m-d'));
	    $CommercialInvoice->addChild('Currency', $currency);
	    $CommercialInvoice->addChild('SenderVATNumber', "");
	    $CommercialInvoice->addChild('ReceiverVATNumber', "");
	    $CommercialInvoice->addChild('TermsOfSale', "DDP");
	    $CommercialInvoice->addChild('ReasonForExport', "Web Order");
	    $SoldTo = $CommercialInvoice->addChild('SoldTo');
		$SoldTo->addChild('ContactPhone', $billing_phone);
		$SoldTo->addChild('ContactName', $contact_name);
		$SoldTo->addChild('VATNumber', "");
		$shiptoaddress = $SoldTo->addChild('Address');
		$shiptoaddress->addChild('AddressLine1', $aOrder->get_shipping_address_1());
		$shiptoaddress->addChild('AddressLine2', $aOrder->get_shipping_address_2());
		$shiptoaddress->addChild('AddressLine3', "");
		$shiptoaddress->addChild('Town', $aOrder->get_shipping_city());
		$shiptoaddress->addChild('County', $aOrder->get_shipping_state());
		$shiptoaddress->addChild('PostCode', $aOrder->get_shipping_postcode());
		$shiptoaddress->addChild('ISOCountryCode', $aOrder->get_shipping_country());
		$CommercialInvoice->addChild('Signature', "Test API");
	    $CommercialInvoice->addChild('SignatureDate', date('Y-m-d'));
	}
	
    $Shipxmlrequest = $products_xml->asXML();
    
    // XML ends

	// request data
		$url = "https://api.csmlogistics.co.uk/Ship/";

		$data = wp_remote_post( 
		    $url, 
		    array(
		        'method' => 'POST',
		        'timeout' => 45,
		        'redirection' => 5,
		        'httpversion' => '1.0',
		        'headers' => array(
		            'Content-Type' => 'text/xml'
		        ),
		        'body' => $Shipxmlrequest,
		        'sslverify' => false
		    )
		);

		$array_data = json_decode(json_encode(simplexml_load_string($data['body'])), true);

		// request end

		return $array_data;
	}
?>