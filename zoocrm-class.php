<?php 
	
	require_once("vendor/autoload.php");
	use zcrmsdk\oauth\ZohoOAuth;
	 
	use zcrmsdk\crm\crud\ZCRMInventoryLineItem;
	use zcrmsdk\crm\crud\ZCRMJunctionRecord;
	use zcrmsdk\crm\crud\ZCRMNote;
	use zcrmsdk\crm\crud\ZCRMRecord;
	use zcrmsdk\crm\crud\ZCRMModule;
	use zcrmsdk\crm\crud\ZCRMTax;
	use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
	use zcrmsdk\crm\setup\users\ZCRMUser;
	use zcrmsdk\crm\crud\ZCRMCustomView;
	use zcrmsdk\crm\crud\ZCRMTag;
	use zcrmsdk\crm\exception\ZCRMException;
	use zcrmsdk\crm\bulkcrud\ZCRMBulkCallBack;
	use zcrmsdk\crm\bulkcrud\ZCRMBulkCriteria;
	use zcrmsdk\crm\bulkcrud\ZCRMBulkWriteFieldMapping;
	use zcrmsdk\crm\bulkcrud\ZCRMBulkWriteResource;
	use zcrmsdk\crm\utility\ZCRMConfigUtil;

// Exit if accessed directly
defined('ABSPATH') or die('Sorry!, You do not access the file directly');

class PR_ZOOCRM_RestClient{

	public function __construct(){	
		$configuration=array(
				"client_id"=>"1000.N700PHXL2QWPE75V0ZAGL2B03HPMGH",
				"client_secret"=>"5afac63364dfd609404ea1169f5dc0cdf2b95abd08",
				"redirect_uri"=>"http://peakrealtychicago.com/peakrealty_zoho/",
				"currentUserEmail"=>"miro@peakrealtychicago.com",
		        "applicationLogFilePath"=>__DIR__."/",
                "access_type"=> "offline ",
				"apiBaseUrl" => "www.zohoapis.com",
				"accounts_url"=>"https://accounts.zoho.com",
				"sandbox"	=> false,
				"db_name"	=> "zohooauth",
				"host_address"	=> "localhost",
				"db_username" => "zohooauth",
				"db_password" => "Mw5k7Kg}z?A3",
				//"db_port" => "3306",
				"fileUploadUrl " => "https://content.zohoapis.com"
			);
			$userIdentifier = "miro@peakrealtychicago.com"; 

			ZCRMRestClient::initialize($configuration);
			$oAuthClient = ZohoOAuth::getClientInstance();
		//	print_R($oAuthClient);
	 	// 	$grantToken = "1000.9fb3e08fd015496c4d6ded6f221768db.f44f9b8c90071c8a41e98a7504933450";
	 	// 	$oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
			// print_R( $oAuthTokens );
			$refreshToken = "1000.d06b1f082e5cbb936243f7c4d3ef6af6.80223778006c59b544ee254c28357dd7";
			$oAuthTokens = $oAuthClient->generateAccessTokenFromRefreshToken($refreshToken, $userIdentifier);
			// print_R( $oAuthTokens );
	}			
	        
	Public static function setnewRecord(){           				
		try{
		  $zcrmModuleIns = ZCRMRestClient::getInstance()->getModuleInstance("Units");
		  	$properties = self::getRecordData();
		  //	echo '<pre>';
		  //	print_r($properties);
		  //	echo '</pre>';
		  	if(!empty($properties)){
		  		$count = 0;
		  		foreach ($properties as $property) {
		  			$srecords = [];
		 			$records=array();
		  			$record=ZCRMRecord::getInstance("Units",null); //To get ZCRMRecord instance
			  		if(!empty($property)){
			  		     $moduleIns=ZCRMRestClient::getInstance()->getModuleInstance("Properties");
			  		     $moduleIns2=ZCRMRestClient::getInstance()->getModuleInstance("Buildings");
			  		     $param_map=array("page"=>1,"per_page"=>1);
			  			foreach ($property as $key => $value) {
			  			    if($key == 'Address'){
			  			        $response1 = $moduleIns->searchRecordsByWord($value, $param_map);
			  			        $pro_res = $response1->getData();
			  			        if(!empty($pro_res[0]->getEntityId())){
			  			            $record->setFieldValue($key,$pro_res[0]->getEntityId()); 
			  			        }
			  			    }elseif($key == 'Building'){
			  			         $response2 = $moduleIns2->searchRecordsByWord($value, $param_map);
			  			        $pro_res2 = $response2->getData();
			  			        if(!empty($pro_res2[0]->getEntityId())){
			  			            $record->setFieldValue($key,$pro_res2[0]->getEntityId()); 
			  			        }
			  			    }else{
			  				    $record->setFieldValue($key,$value); 
			  			    }
			  			}
			  		
			  		}
			  		array_push($records, $record);
			  	
			  		$responseIn=$zcrmModuleIns->upsertRecords($records);
			  		 	echo '<pre>';
		  	print_r($responseIn);
		  	echo '</pre>';
			  	$count++;
		  		}
			}
			echo "Count:  ".$count;
		}
		catch (ZCRMException $e)
		{
			echo $e->getCode();
			echo $e->getMessage();
			echo $e->getExceptionCode();
		}
	}

	public static function getRecordData(){
		$properties = [];
		$args = array( 
				'post_type' => 'property',
				'posts_per_page' =>50,
				'paged' =>5,
				'post_status' => array( 'publish'),
	 			'meta_query' => array(
			        array(
			            'key'     => 'api_name',
			            'value'   => 'rentcafe',
			            'compare' => '=',
			        ),
			    ),
	 		);
		$query = new WP_Query( $args);

		if($query -> have_posts()):
			$count = 0;
			while($query->have_posts()): $query-> the_post();
			
				$status = 'Active';
				$property_id = get_post_meta(get_the_id(), 'REAL_HOMES_property_id', true);
				$apartments = api_result('https://api.rentcafe.com/rentcafeapi.aspx?requestType=apartmentavailability&PropertyId='.$property_id);
				// if(isset( $apartments) && isset( $apartments[0]->Error) && $apartments[0]->Error != '' ) break;
				 if(!empty($apartments)){
					foreach($apartments as $ap){
					   	$properties[$count]['Name']=   get_the_title()." "."Unit ".$ap->ApartmentName;
				    	$properties[$count]['Address'] = get_post_meta(get_the_id(), 'REAL_HOMES_property_address', true);
				    	$properties[$count]['Building'] = get_post_meta(get_the_id(), 'REAL_HOMES_property_address', true);
				    	$properties[$count]['unit_name'] = "Unit ".$ap->ApartmentName;
				    	$properties[$count]['Unit_Type'] = $ap->FloorplanName;
				    	$properties[$count]['Baths'] = (int)$ap->Baths;
				    	$properties[$count]['Beds'] =$ap->Beds;
				    	$properties[$count]['Actual_Rent'] = $ap->MaximumRent;
				    	$properties[$count]['Market_Rent'] = $ap->MaximumRent;
				    	$properties[$count]['Unit_Owner'] = 'Zoho Admin';
				    	//$properties[$count]['Status'] = "";
				    	$properties[$count]['Property_Code'] = $ap->VoyagerPropertyCode;
				    	$properties[$count]['SQFT'] =$ap->SQFT;
				    	//$properties[$count]['Market_Rent'] = $ap->Beds;
				    	$properties[$count]['Date_Available'] = date('Y-m-d', strtotime($ap->AvailableDate));
				    	$properties[$count]['Web_Property_ID'] = get_the_id();
				    	$properties[$count]['Unit_Status'] = $status;
				    	$properties[$count]['Application_Link'] = $ap->ApplyOnlineURL;
				    	$count++;
			    	}
				}	
			endwhile;
		endif;
		echo $count;
		
		return $properties;
	}
    
    Public static function setnewRecord_properties(){           				
		try{
		  $zcrmModuleIns = ZCRMRestClient::getInstance()->getModuleInstance("Properties");
		  $properties = self::getRecordData_properties();
		  
		   	if(!empty($properties)){
		  		$count = 0;
		  		foreach ($properties as $property) {
		  			$srecords = [];
		 			$records=array();
		  			$record=ZCRMRecord::getInstance("Properties",null); //To get ZCRMRecord instance
			  		if(!empty($property)){
			  			foreach ($property as $key => $value) {
			  				$record->setFieldValue($key,$value); 
			  			}
			  		}
			  		array_push($records, $record);
			  		$responseIn=$zcrmModuleIns->upsertRecords($records);
					echo '<pre>'; 
			   		print_r($responseIn);
			   		echo '</pre>';
			   		
			  	$count++;
		  		}
			}
			echo "Count:  ".$count;
		}
		catch (ZCRMException $e)
		{
			echo $e->getCode();
			echo $e->getMessage();
			echo $e->getExceptionCode();
		}
	}

	public static function getRecordData_properties(){
		$properties = [];
		$args = array( 
				'post_type' => 'property',
				'posts_per_page' =>200,
				'paged' =>1,
				'post_status' => array( 'publish', 'draft'),
	 			'meta_query' => array(
			        array(
			            'key'     => 'api_name',
			            'value'   => 'rentcafe',
			            'compare' => '=',
			        ),
			    ),
	 		);
		$query = new WP_Query( $args);
		if($query -> have_posts()):
			$count = 0;
			while($query->have_posts()): $query-> the_post();
			   	$properties[$count]['Name'] = get_the_title();
			  	$properties[$count]['Application_Link']= get_permalink();
			   	$properties[$count]['Description'] = get_the_content();
			    $property_id =get_post_meta( get_the_ID(), 'REAL_HOMES_property_id', true);
			    $additional_details = get_post_meta( get_the_ID(), 'REAL_HOMES_additional_details', true );
			    if(!empty($additional_details)){
			        $properties[$count]['Utilities'] = $additional_details['Amenities'];
			    }
			   	$petpolicy = api_result('http://api.rentcafe.com/rentcafeapi.aspx?requestType=property&type=PetPolicy&propertyId='.$property_id);
			    if(!empty($petpolicy)){
    			    foreach ($petpolicy as $pet) {
    			   	    if($pet->PetType > 0){
    						if($pet->PetType == 1){
    							$properties[$count]['Pet_friendly'] = 'Cats';
    						}elseif($pet->PetType == 2){
    							$properties[$count]['Pet_friendly'] = 'Dogs';
    						}
    						elseif($pet->PetType == 3){
    							$properties[$count]['Pet_friendly'] = 'Dogs & Cats';
    						}elseif($pet->PetType == 4){
    						    $properties[$count]['Pet_friendly'] = 'No';
    						}		
    					}else{
    					     $properties[$count]['Pet_friendly'] = 'None';
    					}
    			    }
			    }
			   	$count++;
			endwhile;
		endif;
		echo $count;
		return $properties;
	}
	
	/* Building*/
	 
    Public static function setnewRecord_Buildings(){           				
		try{
		  $zcrmModuleIns = ZCRMRestClient::getInstance()->getModuleInstance("Buildings");
		  $properties = self::getRecordData_Buildings();
		 
		   	if(!empty($properties)){
		  		$count = 0;
		  		foreach ($properties as $property) {
		  			$srecords = [];
		 			$records=array();
		  			$record=ZCRMRecord::getInstance("Buildings",null); //To get ZCRMRecord instance
			  		if(!empty($property)){
			  			foreach ($property as $key => $value) {
			  				$record->setFieldValue($key,$value); 
			  			}
			  		}
			  		array_push($records, $record);
			  		$responseIn=$zcrmModuleIns->upsertRecords($records);
					echo '<pre>'; 
			   		print_r($responseIn);
			   		echo '</pre>';
			   		
			  	$count++;
		  		}
			}
			echo "Count:  ".$count;
		}
		catch (ZCRMException $e)
		{
			echo $e->getCode();
			echo $e->getMessage();
			echo $e->getExceptionCode();
		}
	}

	public static function getRecordData_Buildings(){
		$properties = [];
		$args = array( 
				'post_type' => 'property',
				'posts_per_page' =>50,
				'paged' =>1,
				'post_status' => array( 'publish', 'draft'),
	 			'meta_query' => array(
			        array(
			            'key'     => 'api_name',
			            'value'   => 'rentcafe',
			            'compare' => '=',
			        ),
			    ),
	 		);
		$query = new WP_Query( $args);
		if($query -> have_posts()):
			$count = 0;
			while($query->have_posts()): $query-> the_post();
			   $property_id =get_post_meta( get_the_ID(), 'REAL_HOMES_property_id', true);
			    $propertyData = api_result('https://api.rentcafe.com/rentcafeapi.aspx?&requestType=property&type=propertyData&propertyId='.$property_id);
			  
			    $properties[$count]['Name'] = ltrim($propertyData[0]->name, "-");
			    $properties[$count]['Property_Code'] = $propertyData[0]->PropertyCode;
			    $properties[$count]['Application_Link']= get_permalink();
			   	$properties[$count]['Description'] = get_the_content();
			    $property_id =get_post_meta( get_the_ID(), 'REAL_HOMES_property_id', true);
			    $additional_details = get_post_meta( get_the_ID(), 'REAL_HOMES_additional_details', true );
			    if(!empty($additional_details)){
			        $properties[$count]['Utilities'] = $additional_details['Amenities'];
			    }
			   	$petpolicy = api_result('http://api.rentcafe.com/rentcafeapi.aspx?requestType=property&type=PetPolicy&propertyId='.$property_id);
			    if(!empty($petpolicy)){
    			    foreach ($petpolicy as $pet) {
    			   	    if($pet->PetType > 0){
    						if($pet->PetType == 1){
    							$properties[$count]['Pet_friendly'] = 'Cats';
    						}elseif($pet->PetType == 2){
    							$properties[$count]['Pet_friendly'] = 'Dogs';
    						}
    						elseif($pet->PetType == 3){
    							$properties[$count]['Pet_friendly'] = 'Dogs & Cats';
    						}elseif($pet->PetType == 4){
    						    $properties[$count]['Pet_friendly'] = 'No';
    						}		
    					}else{
    					     $properties[$count]['Pet_friendly'] = 'None';
    					}
    			    }
			    }
			   	$count++;
			endwhile;
		endif;
		echo $count;
		return $properties;
	}
}
