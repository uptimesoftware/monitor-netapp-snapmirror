<?php
	//error_reporting(E_ERROR);
	$NetApp_Host = getenv('UPTIME_HOSTNAME');
	$NetApp_Port = getenv('UPTIME_SNMP-PORT');
	$NetApp_Community = getenv('UPTIME_READ-COMMUNITY');
	$SNMP_version = getenv('UPTIME_SNMPVERSION');
	$SNMP_v3_agent = getenv('UPTIME_AGENT-USERNAME');
	$SNMP_v3_auth_type = getenv('UPTIME_AUTH-TYPE');
	$SNMP_v3_auth_pass = getenv('UPTIME_AUTH-PASS');
	$SNMP_v3_priv_type = getenv('UPTIME_PRIVACY-TYPE');
	$SNMP_v3_priv_pass = getenv('UPTIME_PRIVACY-PASS');
	$SNMP_Connection =  getenv('UPTIME_CONNECTION');
	
	// 20 second SNMP Timeout
	$SNMP_TIMEOUT = 20000000;
	
	// snapmirrorStatus 
	//{idle(1), transferring(2), pending(3), aborting(4), migrating(5), quiescing(6), resyncing(7), waiting(8), syncing(9), inSync(10)
	
	// snapvaultStatus
	//{idle(1), transferring(2), pending(3), aborting(4), quiescing(6), resyncing(7), paused(12) }
	
	// SnapMirror
	$snapmirrorFilterString=getenv('UPTIME_SMFILTERSTRING');
	//None|Include|Exclude
	$snapmirrorFilter=getenv('UPTIME_SMFILTEROPTION');
	if ($snapmirrorFilter == "Include") {
		$tmpSMIncludeList = explode(',', $snapmirrorFilterString);
		$SMIncludeList = array_map('trim', $tmpSMIncludeList);
	}
	elseif($snapmirrorFilter == "Exclude") {
		$tmpSMExcludeList = explode(',', $snapmirrorFilterString);
		$SMExcludeList = array_map('trim', $tmpSMExcludeList);
	}
	
	/*
	// SnapVault
	$snapvaultFilterString=getenv('UPTIME_SVFILTERSTRING');
	$snapvaultFilter=getenv('UPTIME_SVFILTEROPTION');
	if ($snapvaultFilter == "Include") {
		$tmpSVIncludeList = explode(',', $snapvaultFilterString);
		$SVIncludeList = array_map('trim', $tmpSVIncludeList);
	}
	elseif($snapvaultFilter == "Exclude") {
		$tmpSVExcludeList = explode(',', $snapvaultFilterString);
		$SVExcludeList = array_map('trim', $tmpSVExcludeList);
	}
	*/
	
	$NetApp_Connection_String = $NetApp_Host . ":" . $NetApp_Port;
	
	if (!extension_loaded("snmp")) {
		echo "PHP SNMP Extension not loaded!";
		exit(2);
	}
	
	
	if($SNMP_version == "v1") {
		if ($NetApp_Community == "") {
			echo "Please enter the SNMP community string.";
			exit(2);
		}
		if ($SNMP_Connection=="") {
			echo "Device Unavailable... Please Check your Connections.";
			exit(2);
		}

		$snapmirror_src = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.2",$SNMP_TIMEOUT );
		$snapmirror_src_name = parseADDR($snapmirror_src);
		
		$snapmirror_dst = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.3",$SNMP_TIMEOUT );
		$snapmirror_dst_name = parseADDR($snapmirror_dst);
		
		$snapmirrorStatus = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.5", $SNMP_TIMEOUT );
		$snapmirrorStatus = parseData($snapmirrorStatus);
		
		$snapmirrorLag = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.6", $SNMP_TIMEOUT );
		$snapmirrorLag = parseTime($snapmirrorLag);
		
		/*
		$snapvault_src = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.2",$SNMP_TIMEOUT );
		$snapvault_src_name = parseADDR($snapvault_src);
		
		$snapvault_dst = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.3",$SNMP_TIMEOUT );
		$snapvault_dst_name = parseADDR($snapvault_dst);
		
		$snapvaultStatus = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.4", $SNMP_TIMEOUT );
		$snapvaultStatus = parseData($snapvaultStatus);
		
		$snapvaultLag = snmpwalk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.6", $SNMP_TIMEOUT );
		$snapvaultLag = parseTime($snapvaultLag);
		*/
		
		$cifsStatus = snmpget($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.7.2.1.0", $SNMP_TIMEOUT );
		$cifsStatus = parseData($cifsStatus);
		
	} elseif($SNMP_version == "v2") {
		if ($NetApp_Community == "") {
			echo "Please enter the SNMP community string.";
			exit(2);
		}
		if ($SNMP_Connection=="") {
			echo "Device Unavailable... Please Check your Connections.";
			exit(2);
		}
		
		$snapmirror_src = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.2",$SNMP_TIMEOUT );
		//Convert --> STRING:: "hsypofila001:/vol/Installs/SAP"  to hsypofila001:/vol/Installs/SAP
		$snapmirror_src_name = parseADDR($snapmirror_src);
		
		$snapmirror_dst = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.3",$SNMP_TIMEOUT );
		$snapmirror_dst_name = parseADDR($snapmirror_dst);
		
		$snapmirrorStatus = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.5", $SNMP_TIMEOUT );
		$snapmirrorStatus = parseData($snapmirrorStatus);
		
		$snapmirrorLag = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.9.20.1.6", $SNMP_TIMEOUT );
		$snapmirrorLag = parseTime($snapmirrorLag);		
		
		/*
		$snapvault_src = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.2",$SNMP_TIMEOUT );
		$snapvault_src_name = parseADDR($snapvault_src);
		
		$snapvault_dst = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.3",$SNMP_TIMEOUT );
		$snapvault_dst_name = parseADDR($snapvault_dst);
		
		$snapvaultStatus = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.4", $SNMP_TIMEOUT );
		$snapvaultStatus = parseData($snapvaultStatus);
		
		$snapvaultLag = snmp2_walk($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.19.11.1.6", $SNMP_TIMEOUT );
		$snapvaultLag = parseTime($snapvaultLag);
		*/
		
		// nodata
		$cifsStatus = snmp2_get($NetApp_Connection_String,$NetApp_Community,"1.3.6.1.4.1.789.1.7.2.1.0", $SNMP_TIMEOUT );
		// has test data
		//$cifsStatus = snmp2_get($NetApp_Connection_String,$NetApp_Community,".1.3.6.1.4.1.789.1.24.1.0", $SNMP_TIMEOUT );
		$cifsStatus = parseGetInteger($cifsStatus);

		
	}	elseif ($SNMP_version == "v3") {
	
		if ($SNMP_v3_agent == "") {
			echo "Please enter the SNMP v3 username";
			exit(2);
		}
		if ($SNMP_Connection=="") {
			echo "Device Unavailable... Please Check your Connections.";
			exit(2);
		}
	
		if ($SNMP_v3_priv_type == "") {
			if ($SNMP_v3_auth_type == "") {
				$SNMP_sec_level = "noAuthNoPriv";
			} else {
				$SNMP_sec_level = "authNoPriv";
				if ($SNMP_v3_auth_pass == "") {
					echo "Please enter the SNMP v3 authentication passphrase.";
					exit(2);
				}
			}
		} else {
			$SNMP_sec_level = "authPriv";
			if (($SNMP_v3_auth_pass == "") && ($SNMP_v3_priv_pass != "")) {
					echo "Please enter the SNMP v3 authentication passphrase.";
					exit(2);
			}
			if (($SNMP_v3_auth_pass != "") && ($SNMP_v3_priv_pass == "")) {
					echo "Please enter the SNMP v3 privacy passphrase.";
					exit(2);
			}
			if (($SNMP_v3_auth_pass == "") && ($SNMP_v3_priv_pass == "")) {
					echo "Please enter the SNMP v3 authentication & privacy passphrase.";
					exit(2);
			}
		}
		
		
		$snapmirror_src = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.9.20.1.2",$SNMP_TIMEOUT);
		$snapmirror_src_name = parseADDR($snapmirror_src);
		
		$snapmirror_dst = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.9.20.1.3",$SNMP_TIMEOUT );
		$snapmirror_dst_name = parseADDR($snapmirror_dst);
		
		$snapmirrorStatus = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.9.20.1.5", $SNMP_TIMEOUT );
		$snapmirrorStatus = parseData($snapmirrorStatus);
		
		$snapmirrorLag = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.9.20.1.6", $SNMP_TIMEOUT );
		$snapmirrorLag = parseTime($snapmirrorLag);
		
		/*
		$snapvault_src = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.19.11.1.2",$SNMP_TIMEOUT );
		$snapvault_src_name = parseADDR($snapvault_src);
		
		$snapvault_dst = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.19.11.1.3",$SNMP_TIMEOUT );
		$snapvault_dst_name = parseADDR($snapvault_dst);
		
		$snapvaultStatus = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.19.11.1.4", $SNMP_TIMEOUT );
		$snapvaultStatus = parseData($snapvaultStatus);
		
		$snapvaultLag = snmp3_walk($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.19.11.1.6", $SNMP_TIMEOUT );
		$snapvaultLag = parseTime($snapvaultLag);
		*/
		
		$cifsStatus = snmp3_get($NetApp_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,"1.3.6.1.4.1.789.1.7.2.1.0", $SNMP_TIMEOUT );
		$cifsStatus = parseData($cifsStatus);
	}





// Test if connection info is correct
if ($snapmirror_src_name == false) {
	echo "Fail to get SNMP Data! Please check credentials\n";
	exit(2);
}


// Replace special characters in names & output
$sm_count = count($snapmirror_src_name);
for($i=0; $i < $sm_count; $i++) {
	$smFullName[$i] = $snapmirror_src_name[$i] . "_" . $snapmirror_dst_name[$i];
	$smFullName[$i] = str_replace(".","-",$smFullName[$i]);
	$smFullName[$i] = str_replace("\"","",$smFullName[$i]);
	$smFullName[$i] = str_replace(" ","_",$smFullName[$i]);
	$smFullName[$i] = str_replace("/","_",$smFullName[$i]);
	
	//Output depending on filter option
	if($snapmirrorFilter == "None") {
		// Time is in hundreth of a second.  Converting to hours.
		echo $smFullName[$i] . "." . "snapmirrorLag " . $snapmirrorLag[$i] * 0.01 / 60 / 60 . "\n";
		echo $smFullName[$i] . "." . "snapmirrorStatus " . $snapmirrorStatus[$i] . "\n";
	}
	elseif ($snapmirrorFilter == "Include") {
		if (in_array($smFullName[$i], $SMIncludeList)) {
			echo $smFullName[$i] . "." . "snapmirrorLag " . $snapmirrorLag[$i] * 0.01 / 60 / 60 . "\n";
			echo $smFullName[$i] . "." . "snapmirrorStatus " . $snapmirrorStatus[$i] . "\n";
			
			// Remove value from array so we know what we haven't found yet
			$indexFound = array_search($smFullName[$i], $SMIncludeList);
			unset($SMIncludeList[$indexFound]);
			$SMIncludeList = array_values($SMIncludeList);
		}
	}
	elseif ($snapmirrorFilter == "Exclude") {
		if (!in_array($smFullName[$i], $SMExcludeList)) {
			echo $smFullName[$i] . "." . "snapmirrorLag " . $snapmirrorLag[$i] * 0.01 / 60 / 60 . "\n";
			echo $smFullName[$i] . "." . "snapmirrorStatus " . $snapmirrorStatus[$i] . "\n";
		}
	}
}

/*
$sv_count = count($snapvault_src_name);
for($i=0; $i < $sv_count; $i++) {
	$svFullName[$i] = $snapvault_src_name[$i] . "_" . $snapvault_dst_name[$i];
	$svFullName[$i] = str_replace(".","-",$svFullName[$i]);
	$svFullName[$i] = str_replace("\"","",$svFullName[$i]);
	$svFullName[$i] = str_replace(" ","_",$svFullName[$i]);
	$svFullName[$i] = str_replace("/","_",$svFullName[$i]);
	

	//Output depending on filter option
	if($snapvaultFilter == "None") {
		echo $svFullName[$i] . "." . "snapvaultLag " . $snapvaultLag[$i] * 0.01 / 60 / 60 . "\n";
		echo $svFullName[$i] . "." . "snapvaultStatus " . $snapvaultStatus[$i] . "\n";
	}
	elseif ($snapvaultFilter == "Include") {
		if (in_array($svFullName[$i], $SVIncludeList)) {
			echo $svFullName[$i] . "." . "snapvaultLag " . $snapvaultLag[$i] * 0.01 / 60 / 60 . "\n";
			echo $svFullName[$i] . "." . "snapvaultStatus " . $snapvaultStatus[$i] . "\n";
			
			// Remove value from array so we know what we haven't found yet
			$indexFound = array_search($svFullName[$i], $SVIncludeList);
			unset($SVIncludeList[$indexFound]);
			$SVIncludeList = array_values($SVIncludeList);
		}
	}
	elseif ($snapvaultFilter == "Exclude") {
		if (!in_array($svFullName[$i], $SVExcludeList)) {
			echo $svFullName[$i] . "." . "snapvaultLag " . $snapvaultLag[$i] * 0.01 / 60 / 60 . "\n";
			echo $svFullName[$i] . "." . "snapvaultStatus " . $snapvaultStatus[$i] . "\n";
		}
	}
}
*/

if ($cifsStatus != "") {
	echo "cifsStatus ".$cifsStatus."\n";
}

if ((!empty($SMIncludeList))||(!empty($SVIncludeList)))   {
		
	$tmpArray = array_merge($SMIncludeList, $SVIncludeList);
	$comma_separated = implode(",", $tmpArray);
	echo "The following were not found but were specified in the include filter: ". $comma_separated;
	exit(2);
}


function parseData($data) {
	
	$data_count=  count($data);
	for($i=0; $i < $data_count; $i++) {
		$data_output[$i] = substr($data[$i], strrpos($data[$i],':')+2);
	}
	return $data_output;
}


function parseGetInteger($data) {
	
	$data_count =  count($data);

	// If able to get SNMP data, parse through it.  Else, just return empty string.
	if (strpos($data,'INTEGER') !== false) {
		$data_output = substr($data, strrpos($data,':')+2);
	}
	else {
		$data_output = "";
	}
	return $data_output;
}

// $data looks like this:
//	STRING:: "hsypofila001:/vol/Installs/SAP"
function parseADDR($data) {
	
	$data_count=  count($data);
	for($i=0; $i < $data_count; $i++) {
		$data_output[$i] = substr($data[$i], strpos($data[$i],':')+2);
		$data_output[$i] = str_replace("\"","", $data_output[$i]);		
	}
	return $data_output;
}
function parseTime($data) {
	$data_count = count($data);
	// Each item looks like this -> Timeticks: (316300) 0:52:43.00
	// We only want the seconds
	for($i=0; $i < $data_count; $i++) {
		$tmp = explode('(',$data[$i]);
		// $tmp[1] = 316300) 0:52:43.00
		$tmp2 = explode(')',$tmp[1]);
		$data_output[$i] = $tmp2[0];		
	}
	return $data_output;
}



?>