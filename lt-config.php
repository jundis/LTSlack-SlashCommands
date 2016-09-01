<?php

//SET THESE VARIABLES
//

$slacktoken = "Slack Token Here"; //Set token from the Slack slash command screen.
$authorization = "LT API KEY HERE"; //Enter your LabTech REST API key here.
$labtech = "https://lt.domain.tld" //Enter your LabTech FQDN here.
$timezone = "America/Chicago"; //Set your timezone here. 

//Change optional
$helpurl = "https://github.com/jundis/LTSlack-SlashCommands"; //Set your help article URL here.

//
//Don't modify below unless you know what you're doing!
//

//Timezone Setting to be used for all files.
date_default_timezone_set($timezone);
?>