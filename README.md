# LTSlack-SlashCommands

**Please note that this is effectively a proof of concept for the LabTech REST API. Due to it's lack of support and documentation by LabTech, it may break at any time with any LT update. Only tested using LT11.**

This script, when hosted on a PHP supported web server, will act as a bridge between the JSON requests of Slack and the JSON responses of the LabTech REST API.

Based on my similar script https://github.com/jundis/CWSlack-SlashCommands

Type /lt [computer name or computer ID] in chat and it will pull relevant information about the machine.

Type /lt client [client name] in chat and it will pull the specified client's address and phone/fax number

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

# API Key Setup

1. Visit http://justanotherpsblog.com/2016/04/01/428/ and complete the powershell script variables at the top.  
Also available at https://gist.github.com/hematic/4286a68b3ba1d3835c7608e726b1e8d8#file-gistfile1-txt if site is down.
2. Run the Powershell script.
3. Copy the resulting key.
4. Paste it into the $authorization variable in lt-config.php.