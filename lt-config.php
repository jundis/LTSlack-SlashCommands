<?php

//SET THESE VARIABLES
//

$slacktoken = "Slack Token Here"; //Set token from the Slack slash command screen.
$ltuser = "apiuser"; //Enter your LabTech username here.
$ltpassword = "ENTER PASSWORD"; //Enter your LabTech password here.
$labtech = "https://lt.domain.tld"; //Enter your LabTech FQDN here.
$timezone = "America/Chicago"; //Set your timezone here.
$timeoutfix = true; //Only change if you have issues with this posting to Slack.

// Database Configuration, required for if you want to use MySQL/Maria DB features.
$usedatabase = 0; // Set to 0 by default, set to 1 if you want to enable MySQL for logging purposes.
$dbhost = "127.0.0.1"; //Your MySQL DB
$dbusername = "username"; //Your MySQL DB Username
$dbpassword = "password"; //Your MySQL DB Password
$dbdatabase = "ltslack"; //Change if you have an existing database you want to use, otherwise leave as default.

//Change optional
$helpurl = "https://github.com/jundis/LTSlack-SlashCommands"; //Set your help article URL here.

//
//Don't modify below unless you know what you're doing!
//

$timez = new DateTime("now", new DateTimeZone($timezone));
$utccode = $timez->format('P');

//Timezone Setting to be used for all files.
date_default_timezone_set($timezone);


if($usedatabase == 1)
{
    $mysql = mysqli_connect($dbhost, $dbusername, $dbpassword);

    if(!$mysql)
    {
        echo "MySQL Connection Error: " . mysqli_connect_error();
        die();
    }

    $dbselect = mysqli_select_db($mysql, $dbdatabase);
    if (!$dbselect) {
        //Select database failed
        $sql = "CREATE DATABASE " . $dbdatabase;
        if (mysqli_query($mysql, $sql)) {
            //Database created successfully
            $dbselect = mysqli_select_db($mysql, $dbdatabase);
        } else {
            echo "Database Creation Error: " . mysqli_error($mysql);
            die();
        }
    }

    $sql = "CREATE TABLE IF NOT EXISTS log (id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY, dates TIMESTAMP, slackuser VARCHAR(25) NOT NULL, command VARCHAR(50) NOT NULL)";
    if (mysqli_query($mysql, $sql)) {
        //Table created successfully
    } else {
        echo "Log Table Creation Error: " . mysqli_error($mysql);
        die();
    }

    mysqli_close($mysql);
}

?>