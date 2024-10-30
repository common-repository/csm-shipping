<?php 
function CSML_get_label_for_consignment($cNumber, $aSettings, $service_code, $action_type){
	
	global $woocommerce;	
	//get CSM account details
	$ShipperNumber = $aSettings['shippernumber'];
	$ShipperUserName = $aSettings['shipperusername'];
	$ShipperPass = $aSettings['shipperpassword'];

    // create Label XML
	$label_xml = new SimpleXMLElement('<Request/>');
	$label_xml->addChild('RequestAction', "Label");

	$account_xml = $label_xml->addChild('Account');
	$account_xml->addChild('ShipperNumber', $ShipperNumber);
	$account_xml->addChild('ShipperUserName', $ShipperUserName);
	$account_xml->addChild('ShipperPass', $ShipperPass);

	$consignment_xml = $label_xml->addChild('Consignment');
	$consignment_xml->addChild('ConsignmentNumber', $cNumber);
	$consignment_xml->addChild('LabelType', 'PDF');

    $Labelxmlrequest = $label_xml->asXML();
    // XML ends

	// request data
		$url = "https://api.csmlogistics.co.uk/Label/";
		
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
		        'body' => $Labelxmlrequest,
		        'sslverify' => false
		    )
		);

		$array_data = json_decode(json_encode(simplexml_load_string($data)), true);

		// request end

		return $array_data;
	}
?>