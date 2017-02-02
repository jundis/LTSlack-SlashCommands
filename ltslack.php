<?php
ini_set('display_errors', 1); //Display errors in case something occurs
header('Content-Type: application/json'); //Set the header to return JSON, required by Slack
require_once 'lt-config.php';

if(empty($_GET['token']) || ($_GET['token'] != $slacktoken)) die; //If Slack token is not correct, kill the connection. This allows only Slack to access the page for security purposes.
if(empty($_GET['text'])) die; //If there is no text added, kill the connection.

$exploded = explode(" ",$_GET['text']); //Explode the string attached to the slash command for use in variables.

$url = NULL; //Set to null just in case.

if ($exploded[0]=="help") 
{
	$test=json_encode(array("parse" => "full", "response_type" => "in_channel","text" => "Please visit " . $helpurl . " for more help information","mrkdwn"=>true));
	echo $test;
	return;
}
if ($exploded[0]=="client" && array_key_exists(1,$exploded))
{
	$url = $labtech . '/WCC2/api/Clients?$top=1&$filter=contains(%27' . $exploded[1] . '%27,Name)';
}
else
{
	if(is_numeric($exploded[0]))
	{ //If it detects an ID, set the URL to use ID.
		$url = $labtech . '/WCC2/api/Computers?$filter=ComputerID%20eq%20' . $exploded[0]; //Set ticket API url
	} 
	else
	{
		$url = $labtech . '/WCC2/api/Computers?$filter=Name%20eq%20\'' . $exploded[0] . "'"; //Set the ticket API url to use name search.
	}
}


$urlapi = $labtech . '/WCC2/API/APIToken';

//CURL for API key
$ch = curl_init(); //Initiate a curl session_cache_expire

$body = json_encode(array("username" => $ltuser, "password" => $ltpassword));
//Create curl array to set the API url, headers, and necessary flags.
$curlOpts = array(
    CURLOPT_URL => $urlapi,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 1,
);
curl_setopt_array($ch, $curlOpts); //Set the curl array to $curlOpts

$answerTData = curl_exec($ch); //Set $answerTData to the curl response to the API.
$headerLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);  //Get the header length of the curl response
$curlBodyTData = substr($answerTData, $headerLen); //Remove header data from the curl string.

// If there was an error, show it
if (curl_error($ch)) {
    die(curl_error($ch));
}
curl_close($ch);


$authorization = str_replace('"', "", $curlBodyTData); //Enter your LabTech REST API key here.

$header_data =array(
 "Authorization: LTToken ". $authorization,
);

if ($exploded[0]=="test")
{
    $url = "https://lt.test.com/WCC2/api/Computers(1609)/RunScript";
    $header_data =array(
        "Authorization: LTToken ". $authorization,
        'Content-Type: application/json'
    );
    $ch = curl_init(); //Initiate a curl session_cache_expire

    $body = json_encode(array("ScriptID" => 5897, "NextRun" => gmdate("Y-m-d\TH:i:s-06:00", strtotime("+1 minutes"))));
    //Create curl array to set the API url, headers, and necessary flags.
    $curlOpts = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $header_data,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 1,
    );
    curl_setopt_array($ch, $curlOpts); //Set the curl array to $curlOpts

    $answerTData = curl_exec($ch); //Set $answerTData to the curl response to the API.
    $headerLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);  //Get the header length of the curl response
    $curlBodyTData = substr($answerTData, $headerLen); //Remove header data from the curl string.

    // If there was an error, show it
    if (curl_error($ch)) {
        die(curl_error($ch));
    }
    curl_close($ch);

    var_dump($answerTData);
    var_dump($curlBodyTData);

    die();
}

$ch = curl_init(); //Initiate a curl session_cache_expire

//Create curl array to set the API url, headers, and necessary flags.
$curlOpts = array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HTTPHEADER => $header_data,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HEADER => 1,
);
curl_setopt_array($ch, $curlOpts); //Set the curl array to $curlOpts

$answerTData = curl_exec($ch); //Set $answerTData to the curl response to the API.
$headerLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);  //Get the header length of the curl response
$curlBodyTData = substr($answerTData, $headerLen); //Remove header data from the curl string.

// If there was an error, show it
if (curl_error($ch)) {
	die(curl_error($ch));
}
curl_close($ch);

//Funky conversion for LT Data.
$dataTData = json_decode($curlBodyTData); //Decode the JSON returned by the CW API.
$dataTData = json_decode(json_encode($dataTData->value),true);
$dataTData = $dataTData[0];


$return="Nothing!"; //Just in case
if ($exploded[0]=="client" && array_key_exists("Company",$dataTData))
{
	$phone=preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $dataTData["Phone"]);
	$fax=preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $dataTData["Fax"]);
	$return =array(
	"parse" => "full",
	"response_type" => "in_channel",
	"attachments"=>array(array(
		"fallback" => "Info on Client " . $dataTData["Name"], //Fallback for notifications
		"title" => "Info on Client " .  $dataTData["Company"], 
		"text" =>  "Phone: " . $phone . " | Fax: " . $fax . 
		"\n*Address* - <http://maps.google.com/?q=" . $dataTData["Address1"] . " " .  $dataTData["Address2"] . ",". $dataTData["City"] . ", " . $dataTData["State"] . " " . $dataTData["Zip"] . "|Google Maps>" . 
		"\n" . $dataTData["Address1"] . ", " .  $dataTData["Address2"] .
		"\n" . $dataTData["City"] . ", " . $dataTData["State"] . " " . $dataTData["Zip"], //Return assigned resources
		"mrkdwn_in" => array(
			"text",
			"pretext"
			)
		))
	);
}
else
{
	//Last Contact date conversion
	$date=strtotime($dataTData["LastContact"]);
	$dateformat=date('m-d-Y g:i:sa',$date);
	if(strpos($dataTData["BiosName"],'VMware')!==false)
	{
		$hardware = "VMware VM";
	}
	else if(strpos($dataTData["BiosName"],'Virtual Machine')!==false)
	{
		$hardware = "Hyper-V VM";
	} else
	{
		$hardware = $dataTData["BiosName"];
	}
	$return =array(
	"parse" => "full",
	"response_type" => "in_channel",
	"attachments"=>array(array(
		"fallback" => "Info on System " . $dataTData["Name"] . " (" . $hardware . ")", //Fallback for notifications
		"title" => "Info on System " .  $dataTData["Name"] . " (" . $hardware . ")", 
		"text" =>  "Last Checkin: " . $dateformat . " | Uptime: " . date('H:i',mktime(0,$dataTData["UpTime"])) . 
		"\nCPU: " . $dataTData["CPUUsage"] . "% | Memory: " . $dataTData["MemoryAvail"] . "MB/" . $dataTData["TotalMemory"] . "MB". //Return "Date Entered / Status" string
		"\nLast User: " . $dataTData["LastUsername"], //Return assigned resources
		"mrkdwn_in" => array(
			"text",
			"pretext"
			)
		))
	);
}

echo json_encode($return, JSON_PRETTY_PRINT); //Return properly encoded arrays in JSON for Slack parsing.

?>