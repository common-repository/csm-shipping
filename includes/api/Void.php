<?php 
function CSML_void_ship_consignment($aSettings,$cNumber,$action_type){
	global $woocommerce;	

	$ShipperNumber = $aSettings['shippernumber'];
	$ShipperUserName = $aSettings['shipperusername'];
	$ShipperPass = $aSettings['shipperpassword'];

    // create Void XML
	$void_xml = new SimpleXMLElement('<Request/>');
	$void_xml->addChild('RequestAction', $action_type);

	$account_xml = $void_xml->addChild('Account');
	$account_xml->addChild('ShipperNumber', $ShipperNumber);
	$account_xml->addChild('ShipperUserName', $ShipperUserName);
	$account_xml->addChild('ShipperPass', $ShipperPass);

	$consignment_xml = $void_xml->addChild('Consignment');
	$consignment_xml->addChild('ConsignmentNumber', $cNumber);

    $Voidxmlrequest = $void_xml->asXML();
    // XML ends

	// request data
	$url = "https://api.csmlogistics.co.uk/Void/";
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
		        'body' => $Voidxmlrequest,
		        'sslverify' => false
		    )
		);

	$array_data = json_decode(json_encode(simplexml_load_string($data['body'])), true);

	// request end

	return $array_data;
} 

?>