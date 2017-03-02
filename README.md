# LTSlack-SlashCommands

**Please note that this is effectively a proof of concept for the LabTech REST API. Due to it's lack of support and documentation by LabTech, it may break at any time with any LT update. Only tested using LT11.**

This script, when hosted on a PHP supported web server, will act as a bridge between the JSON requests of Slack and the JSON responses of the LabTech REST API.

Based on my similar script https://github.com/jundis/CWSlack-SlashCommands

Type /lt [computer name or computer ID] in chat and it will pull relevant information about the machine.

Type /lt client [client name] in chat and it will pull the specified client's address and phone/fax number

Type /lt [computer name] script [script name] and it will return a list of scripts that can be run along with their IDs

Type /lt [computer name] script [script ID] and it will execute that script 1 minute from the current time on the specified computer.

# Installation Instructions

1. Download the ltslack.php file and lt-config.php file.
2. Place on a compatible web server
3. Create a new slack slash command integration at https://SLACK TEAM.slack.com/apps/A0F82E8CA-slash-commands
4. Set command to /lt (or other if you prefer)
5. Set the URL to http://domain.tld/ltslack.php
6. Set Method to GET
7. Copy the token
8. Set a name, icon, and autocomplete text if wanted.
9. Modify the lt-config.php file with your companies values and timezone.
10. Test it in Slack!

# Config.php setup

See below for list of variables and what they need to be.

* $slacktoken: Set this to the slack token generated in Step 7 above.
* $ltuser: Set this to the user account Slack will use for LT lookups and scripts. Must have permissions to any client/computer that you want to work with as well as the ability to run scripts if you want to use that.
* $ltpassword: Password for above user
* $labtech: Labtech domain, no slash. Example: https://lt.domain.tld
* $timezone: Your current timezone in PHP timezone format. List here: http://php.net/manual/en/timezones.php
* $timeoutfix: Keep it at true unless you're having issues, at which point set to false and it will show error codes


* $usedatabase: When set to 1, it will log all script executions to the specified database below. All fields below required if this is set to 1.
* $dbhost: Database host/IP
* $dbusername: Your database username, must have add rights, and ideally create database rights unless you create the database yourself.
* $dbpassword: Password for above MySQL user
* $dbdatabase: Database name, so you can use on an existing MySQL install


* $helpurl: Set to a command list. Defaults to this page