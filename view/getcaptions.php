<?php

// Call set_include_path() as needed to point to your client library.
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
session_start();

$OAUTH2_CLIENT_ID = '862152832600-io3ji7pnrt9oh30oqgkov9kidtio0ipt.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'GdFH__C8jXegNtJbPEgrsiVA';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

// YouTube object used to make all Data API requests.
$youtube = new Google_YoutubeService($client);

if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

// Check if access token successfully acquired
if ($client->getAccessToken()) {
  try{

    // REPLACE with the video ID that you want to update
    $videoId = $_POST['videoId'];

    // Create a video list request
    $listResponse = $youtube->videos->listVideos("snippet,contentDetails",
        array('id' => $videoId));

    $videoList = $listResponse['items'];
    if (empty($videoList)) {
      $htmlBody .= sprintf('<h3>Can\'t find a video with video id: %s</h3>', $videoId);
    } else {
      // Since a unique video id is given, it will only return 1 video.
      $video = $videoList[0];
      
      $videoSnippet = $video['snippet'];
      $tags = $videoSnippet['tags'];
      
      $htmlBody .= "<h3>Video Tags</h3><ul>";
    $htmlBody .= sprintf('<li>Tags "%s" found for video %s (%s) </li>',
        implode(',',$tags),$videoId, $videoSnippet['title']);

    $htmlBody .= '</ul>';
    
      
      $videoContentDetails = $video['contentDetails'];
      $captions =  $videoContentDetails['duration'];
     


    $htmlBody .= "<h3>Video Captions</h3><ul>";
    $htmlBody .= sprintf('<li>Caption Track id: "%s" found for video %s (%s)</li>',$captions,$videoId,$videoSnippet['title']);

    $htmlBody .= '</ul>';
  }
    } catch (Google_ServiceException $e) {
      $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
      $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    }

    $_SESSION['token'] = $client->getAccessToken();
    } else {
      // If the user hasn't authorized the app, initiate the OAuth flow
      $state = mt_rand();
      $client->setState($state);
      $_SESSION['state'] = $state;

      $authUrl = $client->createAuthUrl();
      $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
    }
    ?>

    <!doctype html>
    <html>
    <head>
    <title>Video Updated</title>
    </head>
    <body>
      <?=$htmlBody?>
    </body>
    </html>