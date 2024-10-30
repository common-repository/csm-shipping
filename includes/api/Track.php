<?php 
function CSML_get_csm_shipping_track_record($aSettings,$tracknumber){
	global $woocommerce;	

	$ShipperNumber = $aSettings['woocommerce_csmlogistics_settings']['shippernumber'];
	$ShipperUserName = $aSettings['woocommerce_csmlogistics_settings']['shipperusername'];
	$ShipperPass = $aSettings['woocommerce_csmlogistics_settings']['shipperpassword'];

    // create Track XML
	$track_xml = new SimpleXMLElement('<Request/>');
	$track_xml->addChild('RequestAction', "Track");

	$account_xml = $track_xml->addChild('Account');
	$account_xml->addChild('ShipperNumber', $ShipperNumber);
	$account_xml->addChild('ShipperUserName', $ShipperUserName);
	$account_xml->addChild('ShipperPass', $ShipperPass);

	$consignment_xml = $track_xml->addChild('Consignment');
	$consignment_xml->addChild('ConsignmentNumber', $tracknumber);

    $Trackxmlrequest = $track_xml->asXML();
    // XML ends

	// request data
	$url = "https://api.csmlogistics.co.uk/Track/";

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
		        'body' => $Trackxmlrequest,
		        'sslverify' => false
		    )
		);

	$array_data = json_decode(json_encode(simplexml_load_string($data['body'])), true);

	// request end

	return $array_data;
} 

?>