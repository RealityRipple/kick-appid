# Kick's Pusher App ID in JSON
The ID Kick.com uses for Pusher connections in JSON format.  

## Request
Access the App ID by requests to  
 > `//cdn.jsdelivr.net/gh/realityripple/kick-appid/app.json`  

## Updates
A cron job regularly runs `pull.php` once per 3 hours, which will find, download, commit, and push any new changes to the repository automatically. Each new version is marked with a tag representing the date (GMT) the update was retrieved.
