<?php 
function CSML_get_shipping_rate($aPackage, $aSettings, $service_code, $action_type){
	global $woocommerce;

	$orderWeight = $woocommerce->cart->get_cart_contents_weight();
	//get CSM account details
	$ShipperNumber = $aSettings['shippernumber'];
	$ShipperUserName = $aSettings['shipperusername'];
	$ShipperPass = $aSettings['shipperpassword'];
	//get cart items
    $items = $woocommerce->cart->get_cart();

   	$store_address     = get_option( 'woocommerce_store_address' );
	$store_address_2   = get_option( 'woocommerce_store_address_2' );
	$store_city        = get_option( 'woocommerce_store_city' );
	$store_postcode    = get_option( 'woocommerce_store_postcode' );

	// The country/state
	$store_raw_country = get_option( 'woocommerce_default_country' );

	// Split the country/state
	$split_country = explode( ":", $store_raw_country );
	$store_country = $split_country[0];

	$eu_countries = array(
        'AL', 'AD', 'AM', 'AT', 'BY', 'BE', 'BA', 'BG', 'CH', 'CY', 'CZ', 'DE',
        'DK', 'EE', 'ES', 'FO', 'FI', 'FR', 'GB', 'GE', 'GI', 'GR', 'HU', 'HR',
        'IE', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MC', 'MK', 'MT', 'NO', 'NL', 'PL',
        'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SM', 'TR', 'UA', 'VA',
    );

	$shipperduttypaid = $aSettings['shipperduttypaid'];  // value yes or no
	  if (in_array($aPackage['destination']['country'], $eu_countries)){

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
    $userID = $aPackage['user']['ID'];
    $userData = get_user_meta($userID);

   	$user_info = get_userdata($userID);
 	$billing_email = $user_info->user_email;

    //$billing_email = $userData['billing_email'][0];
    
    if(empty($shipping_company) && empty($userData['first_name'][0])){
    	$shipping_company = $userData['nickname'][0];
    } else if(empty($shipping_company) && !empty($userData['first_name'][0])){
    	$shipping_company = $userData['first_name'][0].' '.$userData['last_name'][0];
    } else {
    	$shipping_company = $userData['shipping_company'][0];
    }

    if(empty($userData['first_name'][0])){
    	$contact_name = $userData['nickname'][0];
    } else {
    	$contact_name = $userData['first_name'][0].' '.$userData['last_name'][0];
    }

    if(empty($userData['billing_phone'])){
    	$billing_phone = '014242 604252'; 	
    } else {
    	$billing_phone = $userData['billing_phone'][0];
    }

    // create RATE XML
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
	$consignment_xml->addChild('PickupDate', "2019-10-07");
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
	$shipfromaddress->addChild('HouseNumber', $store_address);
	$shipfromaddress->addChild('AddressLine1', $store_address_2);
	$shipfromaddress->addChild('AddressLine2', "");
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
	$shiptoaddress->addChild('HouseNumber', $aPackage['destination']['address_1']);
	$shiptoaddress->addChild('AddressLine1', $aPackage['destination']['address_2']);
	$shiptoaddress->addChild('AddressLine2', "");
	$shiptoaddress->addChild('AddressLine3', "");
	$shiptoaddress->addChild('Town', $aPackage['destination']['city']);
	if(!empty($aPackage['destination']['state'])){
		$shiptoaddress->addChild('County', $aPackage['destination']['state']);
	} else {
		$shiptoaddress->addChild('County', "");
	}
	$shiptoaddress->addChild('PostCode', $aPackage['destination']['postcode']);
	$shiptoaddress->addChild('ISOCountryCode', $aPackage['destination']['country']);
	$Parcel = $consignment_xml->addChild('Parcel');
	$Parcel->addChild('Weight', $orderWeight);
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

    $Ratexmlrequest = $products_xml->asXML();

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
		        'body' => $Ratexmlrequest,
		        'sslverify' => false
		    )
		);

		$array_data = json_decode(json_encode(simplexml_load_string($data['body'])), true);


		// request end

		return $array_data['Consignment']['ConsignmentPrice'];
	}
?>