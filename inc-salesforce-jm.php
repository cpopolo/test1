<?

function PostSalesforce($awebreg) {
    
    // input: array $awebreg contains a range of form field data from some user-registration context
    // output:  whatever you want it to be; this exmaple returns the LeadId of the inserted person, along with the CampaignId the lead was assigned to.

	ini_set("soap.wsdl_cache_enabled", "0"); // general SOAP wsdl caching - no caching?
	require_once ('salesforce/soapclient/SforcePartnerClient.php'); // Partner or Enterprise?  Leroy provides this local WSDL
	require_once ('salesforce/soapclient/SforceHeaderOptions.php'); // Certain headers in a separate 

	$wsdl = 'salesforce/soapclient/aigpartner.wsdl.xml'; // Shissler
	$userName = "carl.aig@popolo.com";  // Shissler
	$password = "BiteMe9999dkbOe9rJyABy3CAevB1Fa9HNQ"; // I think this is built by requesting the key in the SF online wasteland.

	// Process of logging on and getting a salesforce.com session
	// Process of logging on and getting a salesforce.com session
	// Process of logging on and getting a salesforce.com session
	// Process of logging on and getting a salesforce.com session
	$client = new SforcePartnerClient(); // Partner or Enterprise?
	
	if ($client) {
		$csfresult .= writelog("+++ Connection Attempt START +++");
		$client->createConnection($wsdl);
		$csfresult .= writelog("+++ Connection Attempt ENDED +++");
        
		if ($client) {
			$csfresult .= writelog("+++ Login Attempt START, with u/p " . $userName . "/" . $password . " +++");
			$loginResult = $client->login($userName, $password);
			$csfresult .= writelog("+++ Login Attempt ENDED +++");
            
			if ($loginResult) {
				$areturn['login'] = parseObjectToString($loginResult);
				$csfresult .= writelog("*** login ***\n" . $areturn['login']);
				
			} else {
			 
				$areturn['login'] = "#### LOGIN FAILED ####";
				$csfresult .= writelog("#### LOGIN FAILED ####");
				return($areturn);
				die();
			}
		} else {
		  
			$areturn['login'] = "#### CREATECONNECTION FAILED ####";
			$csfresult .= writelog("#### CREATECONNECTION FAILED ####");
			return($areturn);
			die();
		}
	} else {
	   
		$areturn['login'] = "#### sforcePartnerClient create FAILED ####";
		$csfresult .= writelog("#### sforcePartnerClient create FAILED ####");
		return($areturn);
		die();
	}


	// Build up the transaction payload based on the incoming array of data fields
	// Build up the transaction payload based on the incoming array of data fields
	// Build up the transaction payload based on the incoming array of data fields
	// Build up the transaction payload based on the incoming array of data fields

	$records = array();

	// Parse out Industry from the 3-level choice sent in from the form
	$aIndustry = explode("|",$awebreg['Industry']);  // This is specific to old AS site, where a form returned a 3-level SF Industry code, showing the Industry/Sub/SF object id
	$thisIndustry = $aIndustry[0];

	$records[0] = new SObject();
	$records[0]->fields = array(
	    'FirstName' => htmlspecialchars($awebreg['FirstName']),
	    'LastName' => htmlspecialchars($awebreg['LastName']),
	    'Phone' => htmlspecialchars($awebreg['Phone']),
	    'Company' => htmlspecialchars($awebreg['Company']),
	    'Salutation' => htmlspecialchars($awebreg['salutation']),
	    'Title' => htmlspecialchars($awebreg['JobTitle']),
	    'Leadsource' => 'Web Direct',
	    'Email' => htmlspecialchars($awebreg['Useremail']),
	    'Street' => htmlspecialchars($awebreg['Address1']),
	    'City' => htmlspecialchars($awebreg['City']),
	    'State' => htmlspecialchars(($awebreg['State'])),
	    'PostalCode' => htmlspecialchars($awebreg['Zip']),
	    'Country' => htmlspecialchars($awebreg['Country']),
	    'HasOptedOutofEmail' => '0', //(isset($awebreg['OptOut']) && $awebreg['OptOut']>''?'1':'0'),
		'Product_line__c' => 'Portable Analytical Instruments',	
	    'Industry' => htmlspecialchars($thisIndustry),  //htmlspecialchars($awebreg['Industry']),
		'Markets_Application__c' => $awebreg['subIndustry'],
	    'Description' => htmlspecialchars("WebInquiry=" . $awebreg['HowBetterServed'] . ". Visitor was on webpage: " . $awebreg['WasOnPage']),
		'RecordTypeId' => '012600000001BnZ' // new on 2014/08/03, this is a constant from Shissler
	);
	$records[0]->type = 'Lead';
	
    // Attempt the create
	$response = $client->create($records);
    $leadId = $response->id; // an ugly SF ID reference
    
	// now insert CampaignMember - new on 2014/08/03
	$createFields = array('CampaignId'=>$awebreg['CampaignId'], 'LeadId'=>$leadId, 'Status'=>'Web To Lead');
	$camprecords[0] = new SObject();
	$camprecords[0]->fields = $createFields;
	$camprecords[0]->type = 'CampaignMember';
	$createResp = $client->create($camprecords, 'CampaignMember');
    $campaignId = $response->id;  // I'm not sure this actually works, but it makes sense from the response of the Lead creation above.  
    
	return array($leadId,$campaignId);
    
}

?>
