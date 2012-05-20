autoposter
==========

Automagically posts Instagram and Twitter Images to Wordpress.
You need Postie (http://wordpress.org/extend/plugins/postie/) for posting the blog entry.

For setting up API access, please refer to the according developer manuals at http://instagr.am/developer/ and https://dev.twitter.com/.

A cron job should be set up to call the script (index.php) e.g. every five minutes.
When already setting up cron, I suggest to disable Wordpress' internal "cron" system and use it the right way: http://dansgalaxy.co.uk/2010/10/03/how-to-disable-wordpress-wp-cron/
Furthermore, I recommend Cronic (http://habilis.net/cronic/) to cure cron's email "problem".

I know it's very ugly but it works for me and I just needed about 2 hours of coding...