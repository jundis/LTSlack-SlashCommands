<?php

//SET THESE VARIABLES
//

$slacktoken = "Slack Token Here"; //Set token from the Slack slash command screen.
$ltuser = "apiuser"; //Enter your LabTech username here.
$ltpassword = "ENTER PASSWORD"; //Enter your LabTech password here.
$labtech = "https://lt.domain.tld"; //Enter your LabTech FQDN here.
$timezone = "America/Chicago"; //Set your timezone here.
$timeoutfix = true; //Only change if you have issues with this posting to Slack.

//Change optional
$helpurl = "https://github.com/jundis/LTSlack-SlashCommands"; //Set your help article URL here.

//
//Don't modify below unless you know what you're doing!
//

//Timezone Setting to be used for all files.
date_default_timezone_set($timezone);
?>