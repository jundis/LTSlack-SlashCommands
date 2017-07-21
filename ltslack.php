<?php
ini_set('display_errors', 1); //Display errors in case something occurs
header('Content-Type: application/json'); //Set the header to return JSON, required by Slack
require_once 'lt-config.php';
require_once 'lt-functions.php';

if(empty($_GET['token']) || ($_GET['token'] != $slacktoken)) die; //If Slack token is not correct, kill the connection. This allows only Slack to access the page for security purposes.
if(empty($_GET['text'])) die; //If there is no text added, kill the connection.

$exploded = explode(" ",$_GET['text']); //Explode the string attached to the slash command for use in variables.

$url = NULL; //Set to null just in case.

if ($exploded[0]=="help") 
{
	die(json_encode(array("parse" => "full", "response_type" => "in_channel","text" => "Please visit " . $helpurl . " for more help information","mrkdwn"=>true)));
}
if ($exploded[0]=="scriptlog")
{

}

//Timeout Fix Block
if($timeoutfix == true)
{
    ob_end_clean();
    header("Connection: close");
    ob_start();
    echo ('{"response_type": "in_channel"}');
    $size = ob_get_length();
    header("Content-Length: $size");
    ob_end_flush();
    flush();
    session_write_close();
}
//End timeout fix block

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
if(empty($dataTData))
{
    if ($timeoutfix == true) {
        $return = array("parse" => "full", "response_type" => "ephemeral","text" => "No computer found named " . $exploded[0]);

        slack($_GET["response_url"], $return); // Post to Slack using the slack function
        die();
    } else {
        die("No computer found named " . $exploded[0]);
    }
}
$dataTData = $dataTData[0];

if (array_key_exists(1,$exploded) && ($exploded[1]=="script"||$exploded[1]=="run"))
{
    $clientstring = $dataTData["Domain"] . '\\' . $dataTData["Name"];
    $computerid = $dataTData["ComputerID"];
    if(array_key_exists(2,$exploded))
    {
        if(!is_numeric($exploded[2]))
        {
            $url = $labtech . '/WCC2/API/ScriptStubs?$filter=contains(ScriptName,%27' . $exploded[2] . '%27)';

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
            if(empty($dataTData))
            {
                if ($timeoutfix == true) {
                    $return = array("parse" => "full", "response_type" => "ephemeral","text" => "No script found named " . $exploded[2]);

                    slack($_GET["response_url"], $return); // Post to Slack using the slack function
                    die();
                } else {
                    die("No script found named " . $exploded[2]);
                }
            }

            $textreturn = "";

            foreach($dataTData as $script)
            {
                $textreturn = $textreturn . $script["ScriptName"] . " | " . $script["ScriptId"] . "\n";
            }

            $return =array(
                "parse" => "full",
                "response_type" => "in_channel",
                "attachments"=>array(array(
                    "fallback" => "Script info for $exploded[2]", //Fallback for notifications
                    "title" => "Scripts for $clientstring",
                    "text" =>  $textreturn,
                    "mrkdwn_in" => array(
                        "text",
                        "pretext"
                    )
                ))
            );

            if ($timeoutfix == true) {
                slack($_GET["response_url"], $return); // Post to Slack using the slack function
                die();
            } else {
                die(json_encode($return, JSON_PRETTY_PRINT));
            }
        }
    }
    else
    {
        if ($timeoutfix == true) {
            $return = array("parse" => "full", "response_type" => "ephemeral","text" => "No script specified");

            slack($_GET["response_url"], $return); // Post to Slack using the slack function
            die();
        } else {
            die("No script specified");
        }
    }

    //Block to verify script exists

    $url = $labtech . '/WCC2/API/ScriptStubs?$filter=ScriptId%20eq%20' . $exploded[2];
    $header_data =array(
        "Authorization: LTToken ". $authorization,
    );

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
    if(empty($dataTData))
    {
        if ($timeoutfix == true) {
            $return = array("parse" => "full", "response_type" => "ephemeral","text" => "No script found with ID " . $exploded[2]);

            slack($_GET["response_url"], $return); // Post to Slack using the slack function
            die();
        } else {
            die("No script found with ID " . $exploded[2]);
        }
    }
    $dataTData = $dataTData[0];

    $scriptstring = $dataTData["ScriptName"];

    //Blcok to actually run script

    $url = $labtech . "/WCC2/api/Computers(" . $computerid . ")/RunScript";
    $header_data =array(
        "Authorization: LTToken ". $authorization,
        'Content-Type: application/json'
    );

    $ch = curl_init(); //Initiate a curl session_cache_expire

    $body = json_encode(array("ScriptID" => $exploded[2], "NextRun" => gmdate("Y-m-d\TH:i:s" . $utccode, strtotime("+1 minutes"))));

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

    $dataTData = json_decode($curlBodyTData);

    if(array_key_exists("value",$dataTData))
    {
        if($usedatabase == 1)
        {
            $mysql = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbdatabase);
            if (!$mysql)
            {
                die("Connection Error: " . mysqli_connect_error());
            }

            $val1 = mysqli_real_escape_string($mysql,$_GET['user_name']);
            $val2 = mysqli_real_escape_string($mysql,$_GET['text']);
            $date = date('Y-m-d H:i:s');

            $sql = "INSERT INTO `log` (`id`, `dates`, `slackuser`, `command`) VALUES (NULL, '" . $date . "', '" . $val1 . "', '" . $val2 . "');";

            if(mysqli_query($mysql,$sql))
            {
                //Do nothing if successful
            }
            else
            {
                die("MySQL Error: " . mysqli_error($mysql));
            }
        }
        if ($timeoutfix == true) {
            $return = array("parse" => "full", "response_type" => "ephemeral","text" => "Successfully set script " . $scriptstring . " to run on " . $clientstring);

            slack($_GET["response_url"], $return); // Post to Slack using the slack function
            die();
        } else {
            die("Successfully set script " . $scriptstring . " to run on " . $clientstring);
        }
    }
    else
    {
        var_dump($answerTData);
        if ($timeoutfix == true) {
            $return = array("parse" => "full", "response_type" => "ephemeral","text" => "Unknown error occurred. Turn timeoutfix to false in lt-config.php and try again");

            slack($_GET["response_url"], $return); // Post to Slack using the slack function
            die();
        } else {
            die("Unknown error occurred. Please see above for error data");
        }
    }

}

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

    if($dataTData["IdleTime"]==0)
    {
        $idletime = "Active";
    }
    else if($dataTData["IdleTime"]==-1)
    {
        $idletime = "Not logged in";
    }
    else
    {
        $hours = floor($dataTData["IdleTime"] / 3600);
        $minutes = floor($dataTData["IdleTime"] / 60 % 60);
        $seconds = floor($dataTData["IdleTime"] % 60);

        $idletime = $hours . "h " . $minutes . "m " . $seconds . "s";
    }

	if($dataTData["UpTime"]>=1440)
    {
        $updays = floor($dataTData["UpTime"] / 1440);
        $uphours = floor(($dataTData["UpTime"] - $updays * 1440) / 60);
        $upminutes = $dataTData["UpTime"] - $updays * 1440 - $uphours * 60;
        $uptime = $updays . "d:" . $uphours . "h:" . $upminutes . "m";
    }
    else if ($dataTData["UpTime"]<1440 && $dataTData["UpTime"]>=60)
    {
        $uphours = floor($dataTData["UpTime"] / 60);
        $upminutes = $dataTData["UpTime"] - $uphours * 60;
        $uptime = $uphours . "h:" . $upminutes . "m";
    }
    else
    {
        $uptime = $dataTData["UpTime"] . "m";
    }

    $dnsservers = explode(":", $dataTData["DNSInfo"]);
    $dnsservers = explode(";", $dnsservers[2]);
    $dnsservers = implode(", ", $dnsservers);

    $sharesraw = explode(":",$dataTData["Shares"]);
    $shareslist = array();
    $sharestext = "None";
    // TO DO STILL
    foreach($sharesraw as $share)
    {
        if(substr($share, -1) == "$")
        {
            // Do nothing for now, may use later but for now just drop admin shares
        }
        else
        {
            $shareslist[] = $share;
        }
    }
    if(empty($shareslist))
    {
        $sharestext = implode(", ",$shareslist);
        $sharestext = "\nShares: " . $sharestext;
    }

	$return =array(
	"parse" => "full",
	"response_type" => "in_channel",
	"attachments"=>array(array(
		"fallback" => "Info on System " . $dataTData["Name"] . " (" . $hardware . ")", //Fallback for notifications
		"title" => "Info on System " .  $dataTData["Name"] . " (" . $hardware . ")",
		"text" =>  "Last Checkin: " . $dateformat . " | Uptime: " . $uptime .
		"\nLast User: " . $dataTData["LastUsername"] . " | Idle Time: " . $idletime . //Return last logged in user
        "\n*Network*\nLocal IP: " . $dataTData["LocalAddress"] . " | WAN IP: " . $dataTData["RouterAddress"] . "\nMAC: " . $dataTData["MAC"] . " | RDP Port: " . $dataTData["ManagementPort"] . "\nDNS Servers: " . $dnsservers . //Return network block
        "\n*System*\nOS: " . $dataTData["OS"] . "\nCPU: " . $dataTData["CPUUsage"] . "% | Memory: " . $dataTData["MemoryAvail"] . "MB/" . $dataTData["TotalMemory"] . "MB" . $sharestext, //Return system block
		"mrkdwn_in" => array(
			"text",
			"pretext"
			)
		))
	);
}

if ($timeoutfix == true)
{
    slack($_GET["response_url"], $return); // Post to Slack using the slack function
    die();
}
else
{
    die(json_encode($return, JSON_PRETTY_PRINT));
}
?>