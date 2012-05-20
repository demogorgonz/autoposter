#!/usr/bin/php
<?php
header("Content-Type: text/plain");
error_reporting(E_ALL ^ E_NOTICE);
$docroot = "/path/to/autoposter/autoposter/";

// ugly, but works!
getTwitter($docroot);
getInstagram($docroot);

// getting last instagram image via API
function getInstagram($docroot) {
  $client_id = "your client id";
  $access_token = "your access token";
  $contents = file_get_contents("https://api.instagram.com/v1/users/15555557/media/recent?count=1&access_token=$access_token"); 
  $json = json_decode($contents, true);

  $created_time_saved = file_get_contents($docroot."data/instagram.txt");
  $created_time = $json["data"][0]["created_time"];
  
  if ($created_time_saved != $created_time) {
    echo "Instagram: getting last image\n";
    $imageurl = $json["data"][0]["images"]["standard_resolution"]["url"];
    $filename = $docroot."images/instagram-".$json["data"][0]["caption"]["id"].".jpg";
    $imagetext = $json["data"][0]["caption"]["text"];

    // if geocoordinates exist, post them
    if ($json["data"][0]["location"]["latitude"] != '') {
      $body = "<a href=\"http://maps.google.com/maps?q=".$json["data"][0]["location"]["latitude"].",".$json["data"][0]["location"]["longitude"]."\" target=\"_blank\">Location</a><br />";
    }

    $body .= "Autoposted from <a href=\"".$json["data"][0]["link"]."\">Instagram</a>";
  
    file_put_contents($filename, file_get_contents($imageurl));
    $filenameshort = "instagram-".$json["data"][0]["caption"]["id"].".jpg";
    sendMail($filename, $filenameshort, $imagetext, "instagram", $body);
    $handle = fopen($docroot."data/instagram.txt","w");
    fwrite($handle,$created_time);
    fclose($handle);
  } else {
    echo "Instagram: no action needed\n";
  }
}

// getting last Twitter image (media entity) via API
function getTwitter($docroot) {
  $consumer_key = "your consumer key";
  $consumer_secret = "your consumer secret";
  $access_key = "your access key";
  $access_secret = "your access secret";
  require_once($docroot."lib/twitteroauth.php");
  $id_saved = file_get_contents($docroot."data/twitter.txt");
  $twitter = new TwitterOAuth ($consumer_key, $consumer_secret, $access_key, $access_secret);
  $results = $twitter->get('statuses/user_timeline', array('include_entities' => true, 
                                                           'include_rts' => false,
                                                           'count' => 1));

  $id = $results[0]->id;
  $imageurl = $results[0]->entities->media[0]->media_url;
  
  if ($id_saved != $id) {
    if ($imageurl != '') {
      echo "Twitter: getting last image\n";
      $imagetext = split("http://", $results[0]->text);
      $imagetext = $imagetext[0];
      $imagetext = str_replace("#fb", "", $imagetext);
      $filename = $docroot."images/twitter-".$id.".jpg";
      $filenameshort = "twitter-".$id.".jpg";

      // if geocoordinates exist, post them
      if ($results[0]->geo != '') {
        $body = "<a href=\"http://maps.google.com/maps?q=".$results[0]->geo->coordinates[0].",".$results[0]->geo->coordinates[1]."\" target=\"_blank\">Location</a><br />";
      }
      
      $body .= "Autoposted from <a href=\"http://twitter.com/nodomain/status/".$id."\">Twitter</a>";

      file_put_contents($filename, file_get_contents($imageurl.":large"));
      sendMail($filename, $filenameshort, $imagetext, "twitter", $body);
    } else {
      echo "Twitter: Tweet without image\n";
    }

    $handle = fopen($docroot."data/twitter.txt","w");
    fwrite($handle,$id);
    fclose($handle);
  
  } else {
    echo "Twitter: no action needed\n";
  }
}

// send the mail
function sendMail($filename, $filenameshort, $imagetext, $tags, $body) {
  echo "sending mail...\n";
  $rcpt = "postie's mail address";
  $subject = $imagetext;

  $sectionmarker = md5(uniqid(rand()));

  $head = "MIME-Version: 1.0\n";
  $head.= "Content-Type: multipart/mixed; boundary = \"$sectionmarker\"\n";

  $text.= "--".$sectionmarker."\n";
  $text.= "Content-Type: text/html; charset=\"UTF-8\"\n";
  $text.= "Content-Transfer-Encoding: 8bit\n\n";
  $text.= "Tags: ".$tags."<br />\n";
  $text.= $body."<br />\n";

  $text.= "--".$sectionmarker."\n";
  $text.= "Content-Type: image/jpeg; name=".$filenameshort."\n";
  $text.= "Content-Transfer-Encoding: base64\n";
  $text.= "Content-Disposition: attachment; filename=".$filenameshort."\n\n";

  $attachment = fread(fopen($filename, "r"), filesize($filename));
  $attachment = chunk_split(base64_encode($attachment));
  $text.= $attachment."\n";
  $text.= "--".$sectionmarker."--\n";
  mail($rcpt, $subject, $text, $head);
}

?>