<?php
// Call set_include_path() as needed to point to your client library.
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
session_start();

/* You can acquire an OAuth 2 ID/secret pair from the API Access tab on the Google APIs Console
  <http://code.google.com/apis/console#access>
For more information about using OAuth2 to access Google APIs, please visit:
  <https://developers.google.com/accounts/docs/OAuth2>
Please ensure that you have enabled the YouTube Data API for your project. */
$OAUTH2_CLIENT_ID = '862152832600-io3ji7pnrt9oh30oqgkov9kidtio0ipt.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'GdFH__C8jXegNtJbPEgrsiVA';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
  FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

$youtube = new Google_YoutubeService($client);

if (isset($_GET['code'])) {
 
 //commenting session code until we start saving session in db
/*
 if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did n"ot match.');
  }
*/

  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {
  try {
    $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
      'mine' => 'true',
    ));

    $htmlBody = '';
    foreach ($channelsResponse['items'] as $channel) {
      $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

      $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
        'playlistId' => $uploadsListId,
        'maxResults' => 50
      ));
      $i = 0; 
      $htmlBody .= '<div class="well" style="max-width: 400px; margin: 0 auto 40px;">
                       <button type="button" class="btn btn-large btn-danger btn-block" disabled>Videos in list '.$uploadsListId.'</button>
                    </div>';
      $htmlBody .= '<div class="row-fluid"><ul class="thumbnails">';
      
      foreach ($playlistItemsResponse['items'] as $playlistItem) {
        
        $videoTitle = $playlistItem['snippet']['title'];
        $currentVideoTd = $playlistItem['snippet']['resourceId']['videoId'];
        $thumburl = 'https://i1.ytimg.com/vi/'. $currentVideoTd .'/mqdefault.jpg';
        $publishTime = $playlistItem['snippet']['publishedAt'];
        $description = $playlistItem['snippet']['description']; 
        
        if ($i > 0 && $i % 3 == 0) { 
           $htmlBody .= '</ul><ul class="thumbnails">'; 
        } 
        
        $htmlBody .= '<li class="span4">
                         <div id="cat-wall" style="position: relative;" class="masonry">';
        $htmlBody .= '<div class="cat-brick masonry-brick" style="position: relative; top: 0px; left: 0px;">'; 
        $htmlBody .= '<div class="caption">'; 
        $htmlBody .= "<blockquote><p>Title: $videoTitle </p></blockquote></div>";
        $htmlBody .= '<div class="content thumbz"><a href="" class="post-zoom url" data-title="photo"><img src="'.$thumburl.'" alt="Photo" width="250"></a> </div>';
        $htmlBody .= '<dl><dt>Published At</dt>';
        $htmlBody .= "<dd> $publishTime</dd>";
        $htmlBody .= '<dt>Video Id</dt>'; 
        $htmlBody .= "<dd>$currentVideoTd </dd>";
        $htmlBody .= '<dt>Description</dt>'; 
        $htmlBody .= "<dd> $description </dd></dl>";
          
              // REPLACE with the video ID that you want to update
    $videoId =  $playlistItem['snippet']['resourceId']['videoId'];

    // Create a video list request 
    $listResponse = $youtube->videos->listVideos("snippet",
        array('id' => $videoId));

    $videoList = $listResponse['items'];
    if (empty($videoList)) {
      $htmlBody .=  'Tags:  none </li>';
    } else {
      // Since a unique video id is given, it will only return 1 video.
      $video = $videoList[0];
      $videoSnippet = $video['snippet'];

      $tags = $videoSnippet['tags'];
	  
	   $htmlBody .= '<hr><form action="addtag.php" method="post" id="tag"><input type="hidden" name="videoId" value="'.$currentVideoTd.'"><input type="search" name="newtag" placeholder="TAG"></form>';
	  
	  if(!empty($tags))
	  {		 
			 foreach ($tags as $tag) {
			 $htmlBody .= sprintf('<span class="label label-important"> %s </span>&nbsp;',$tag);
			 }		 
	  }
	   $htmlBody .= '</li>';
    }
    $i++; 
      }
      $htmlBody .= '</ul></div>';
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
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
       <div class="email-wrap">
        <div class="insights-form">
         <div class="well" style="max-width: 400px; margin: 0 auto 40px;">
          <a href="$authUrl" class="btn btn-large btn-block btn-primary">Authorization Required ?</a>
          <button type="button" class="btn btn-large btn-block" disabled><small>You need to authorize access.</small></button>
         </div>
        </div>
       </div>
END;
}
?>

<html>
<head>
<title>Shabdo Khoj </title>

  <!-- Icon Fonts -->
  <link rel="stylesheet" href="assets/css/font-awesome.min.css">
  <!--[if IE 7]>
  <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css">
  <![endif]-->

  <!-- Stylesheet -->
	<link rel="stylesheet" href="assets/css/settings.css" type="text/css" media="screen">  
  <link rel="stylesheet" href="assets/css/shabdo.css" type="text/css" media="screen"> 
  <link rel="stylesheet" href="assets/css/shabdo-main.css" type="text/css" media="screen">
  <link rel="stylesheet" href="assets/css/shabdo-fixes.css" type="text/css" media="screen">
  <!-- Bootstrap -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/bootstrap-responsive.min.css">

  <!--[if lte IE 7]>
  <link href="shabdo-main-ie.css" rel="stylesheet" type="text/css"/>
  <![endif]-->

<link href='http://fonts.googleapis.com/css?family=Poiret+One' rel='stylesheet' type='text/css'>
<style>
 #cat-wall{
margin:0 auto 90px auto
}
.cat-brick{
width:288px;
overflow:hidden;
color:#666;
padding-bottom:15px;
margin-top:15px;
-webkit-border-radius:3px;
-moz-border-radius:3px;
border-radius:3px;
background:#fff;
-webkit-box-sizing:border-box;
-moz-box-sizing:border-box;
box-sizing:border-box;
-webkit-box-shadow:0 0 0 1px rgba(0,0,0,0.18),0 0 5px 0 rgba(0,0,0,0.2);
-moz-box-shadow:0 0 0 1px rgba(0,0,0,0.18),0 0 5px 0 rgba(0,0,0,0.2);
box-shadow:0 0 0 1px rgba(0,0,0,0.18),0 0 5px 0 rgba(0,0,0,0.2)
}
.cat-brick .caption{
background:#f5f1eb;
padding:10px;
-webkit-border-radius:3px 3px 0 0;
-moz-border-radius:3px 3px 0 0;
border-radius:3px 3px 0 0;
border-bottom:1px solid #e2d7cc;
color:#6f6b61;
-ms-word-break:break-all;
word-break:break-all;
word-break:break-word;
-webkit-hyphens:auto;
-moz-hyphens:auto;
hyphens:auto

}
.cat-brick .touchy-bar{
overflow:hidden;
margin:5px 10px 0;
background:#f5f1eb
}
.cat-brick .touchy-bar ul.action{
text-align:center;
position:relative;
list-style:none;
margin:3px 0;
-webkit-touch-callout:none;
-webkit-user-select:none;
-moz-user-select:none;
-ms-user-select:none;
user-select:none
}
.cat-brick .touchy-bar ul.action>li{
display:inline-block;
margin:5px;
width:30px;
height:30px;
cursor:pointer
}
.cat-brick .touchy-bar ul.action>li .val{
cursor:default
}
.cat-brick .meta{
font-size:12px;
color:#c0beb8;
margin:5px 10px 10px;
overflow:hidden
}
.cat-brick .meta .poststats{
float:right;
margin-left:8px
}
.cat-brick .content{
margin:10px 10px 0;
min-height:100px;
max-height:700px;
overflow:hidden;
position:relative
}
.cat-brick .content .overlay{
background:transparent;
background:rgba(0,0,0,0.1);
display:none;
position:absolute;
padding:10px;
top:0;
left:0;
width:100%;
height:100%;
-webkit-box-sizing:border-box;
-moz-box-sizing:border-box;
box-sizing:border-box
}
.cat-brick .content .overlay .popover-emoji{
display:none
}
.cat-brick .content .overlay .popover-emoji ul{
text-align:center;
list-style:none;
margin:2px 0 0 0
}
.cat-brick .content .overlay .popover-emoji ul li{
margin:5px 0 0 3px;
display:inline-block;
cursor:pointer
}
.cat-brick .content .overlay .popover-emoji ul li:first-child{
margin-left:0
}
.cat-brick .content .overlay .overlay-top{
position:absolute;
width:200px;
left:50%;
padding:5px 0;
margin-left:-100px;
background:#f5f1eb;
-webkit-border-radius:3px;
-moz-border-radius:3px;
border-radius:3px;
-webkit-box-shadow:0 0 0 0 rgba(0,0,0,0.18),0 0 3px 0 rgba(0,0,0,0.2);
-moz-box-shadow:0 0 0 0 rgba(0,0,0,0.18),0 0 3px 0 rgba(0,0,0,0.2);
box-shadow:0 0 0 0 rgba(0,0,0,0.18),0 0 3px 0 rgba(0,0,0,0.2)
}
.cat-brick .content .overlay .overlay-top .sep{
height:1px;
margin:3px 8px 0;
background:#d4d0ca;
border-bottom:1px solid rgba(255,255,255,0.9)
}
.cat-brick .content .overlay .overlay-top ul.action{
text-align:center;
position:relative;
list-style:none;
margin:0;
-webkit-touch-callout:none;
-webkit-user-select:none;
-moz-user-select:none;
-ms-user-select:none;
user-select:none
}
.cat-brick .content .overlay .overlay-top ul.action>li{
display:inline-block;
margin:5px;
width:30px;
height:30px;
cursor:pointer
}
.cat-brick .content .overlay .overlay-top ul.action>li .val{
cursor:default
}
.cat-brick .content .overlay .post-zoom{
display:block;
position:absolute;
top:0;
bottom:0;
left:0;
right:0;
cursor:zoom-in;
cursor:-webkit-zoom-in;
cursor:-moz-zoom-in
}
.cat-brick .user,.cat-brick .comment li,.cat-brick .add-comment{
padding:10px 10px 0;
overflow:hidden;
border-top:1px solid #e2d7cc
}
.cat-brick .user .user-avatar,.cat-brick .comment li .user-avatar,.cat-brick .add-comment .user-avatar{
float:left;
margin-right:5px;
width:40px;
height:40px
}
.cat-brick .user .user-avatar img,.cat-brick .comment li .user-avatar img,.cat-brick .add-comment .user-avatar img{
-webkit-border-radius:3px;
-moz-border-radius:3px;
border-radius:3px
}
.cat-brick .user .details p,.cat-brick .comment li .details p,.cat-brick .add-comment .details p{
margin-bottom:2px
}
.cat-brick .user .post-info,.cat-brick .comment li .post-info,.cat-brick .add-comment .post-info,.cat-brick .user .post-comment,.cat-brick .comment li .post-comment,.cat-brick .add-comment .post-comment{
font-size:12px
}
.cat-brick .separator{
height:1px;
background:#e3d7cc;
margin-top:15px;
margin:15px 10px 5px
}
.cat-brick .user{
padding-top:0;
border-top:0
}
.cat-brick .comment ul{
margin:0;
list-style:none
}
.cat-brick .comment li,.cat-brick .add-comment{
margin-top:5px;
border-top:0
}
.cat-brick .comment li .details,.cat-brick .add-comment .details{
margin-left:45px
}
.cat-brick .comment li .comment-input,.cat-brick .add-comment .comment-input{
margin-left:45px
}
.cat-brick .comment li .comment-input textarea,.cat-brick .add-comment .comment-input textarea{
width:100%;
height:40px;
-webkit-box-sizing:border-box;
-moz-box-sizing:border-box;
box-sizing:border-box;
font-size:12px;
line-height:12px;
resize:none;
padding:7px;
margin-bottom:5px
}
.cat-brick .comment li .post-comment-btn,.cat-brick .add-comment .post-comment-btn{
float:right
}
.cat-brick .comment li:first-child{
border-top:0
}
.cat-brick img{
vertical-align:middle;
max-width:100%;
height:auto
}
.touch .cat-brick .overlay-top{
display:none
}
.touch .cat-brick .overlay{
display:block;
background:transparent
}
p{
margin:0 0 9px;
font-family:'Muli','Helvetica Neue','Helvetica',Arial,Helvetica,sans-serif;
font-size:14px;
line-height:18px
}
p small{
font-size:12px;
color:#6f6b61
}
  /* Custom container */
      .container {
        margin: 0 auto;
        max-width: 1000px;
      }
      .container > hr {
        margin: 60px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 80px 0;
        text-align: center;
      }
      .jumbotron h1 {
        margin-top: 40px;
        font-size: 70px;
        line-height: 1;
        color: rgb(255, 255, 255)
      }
      .jumbotron .lead {
        font-size: 30px;
        line-height: 1.25;
        margin-bottom: 40px;
        color: rgb(247, 247, 249)
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
/* reset webkit search input browser style */
input {
	outline: none;
}
input[type=search] {
	-webkit-appearance: textfield;
	-webkit-box-sizing: content-box;
	font-family: inherit;
	font-size: 100%;
}
input::-webkit-search-decoration,
input::-webkit-search-cancel-button {
	display: none; /* remove the search and cancel icon */
}

/* search input field */
input[type=search] {
	background: #ededed url(assets/img/icon-tag_transparent.png) no-repeat 9px center;
	border: solid 1px #ccc;
	padding: 9px 10px 9px 32px;
	width: 55px;
	
	-webkit-border-radius: 10em;
	-moz-border-radius: 10em;
	border-radius: 10em;
	
	-webkit-transition: all .5s;
	-moz-transition: all .5s;
	transition: all .5s;
}
input[type=search]:focus {
	width: 130px;
	background-color: #fff;
	border-color: #6dcff6;
	
	-webkit-box-shadow: 0 0 5px rgba(109,207,246,.5);
	-moz-box-shadow: 0 0 5px rgba(109,207,246,.5);
	box-shadow: 0 0 5px rgba(109,207,246,.5);
}

/* placeholder */
input:-moz-placeholder {
	color: #999;
}
input::-webkit-input-placeholder {
	color: #999;
}

/* demo B */
#tag input[type=search] {
	width: 15px;
	padding-left: 10px;
	color: transparent;
	cursor: pointer;
}
#tag input[type=search]:hover {
	background-color: #fff;
}
#tag input[type=search]:focus {
	width: 130px;
	padding-left: 32px;
	color: #000;
	background-color: #fff;
	cursor: auto;
}
#tag input:-moz-placeholder {
	color: transparent;
}
#tag input::-webkit-input-placeholder {
	color: transparent;
}
 </style>
</head>
<body>
 <div id="wrap">
  <div id="topLine">
    <div class="inner">
     <div style="background-color: rgb(248, 148, 6);"></div>
     <div style="background-color: rgb(0, 136, 204);"></div>
     <div style="background-color: rgb(248, 148, 6);"></div>
     <div style="background-color: rgb(229, 102, 102);"></div>
     <div style="background-color: rgb(0, 136, 204);"></div>
     <div style="background-color: rgb(229, 102, 102);"></div>
     <div style="background-color: rgb(0, 136, 204);"></div>
     <div style="background-color: rgb(91, 183, 91);"></div>
    </div>
  </div>
  <div id="header">
    <div class="container">
      <div class="inner">
        <br>
        <a href="#" class="logo">
         <img src="assets/img/shabdo_transparent.png" alt="Shabdo Khoj..">
        </a>
        <div class="btns">
          <a style="background: #3473a8" href="#">YOUTUBE</a>        
          <a href="#">GOOGLE DRIVE</a>
          <a href="#">ANDRIOD APP</a>
        </div>
      </div>
      <div class="masthead">
      <div class="jumbotron">
        <a style="text-align: center;"><img alt="" src="logo.JPG" class="img-polaroid"></a>
        <h1>Welcome to Shabdo Khoj!</h1>
        <p class="lead">A better way to index and search your audio &amp; video files...</p>
      </div>
     </div>
      <?=$htmlBody?>
    </div>
  </div> 
  <div id="testimonials">
  <div class="container">
    <div class="inner" style="opacity: 1;">
      <h2>CREDITS</h2>
      <div class="lists">
        <div class="item">
          <div class="avatar">
            <img src="logo.JPG" alt="">
          </div>
          <div class="text">
            <p>Created as part of MTech Project by</p>
              <a href="http://in.linkedin.com/in/sayan801/" target="_blank" class="ml"> <strong>Chandra Shekhar Sengupta</strong></a><br>
            <span>Student</span>
          </div>
        </div>
        <div class="item">
          <div class="avatar">
            <img src="logo.JPG" alt="">
          </div>
          <div class="text">
            <p>Under guidance of </p>
               <a href="http://www.iitkgp.ac.in/fac-profiles/showprofile.php?empcode=bYmWU" target="_blank" class="ml"><strong>Prof. K. S. Rao</strong></a><br>
            <span>IIT Kgp</span>
          </div>
        </div>
      </div>
  </div>
</div>
</div>
<div id="anonymous-footer" class="visible-desktop hidden-phone visible-tablet">
 <div class="container">
  <div class="inner">
   <div class="row-fluid">
    <hr>
        <div class="media span3">
          <a href="#" target="_blank" class="ml"><span>Mail</span></a>
          <a href="#" target="_blank" class="tw"><span>Twitter</span></a>
          <a href="#" target="_blank" class="fb"><span>Facebook</span></a>
        </div>
      </div>
     </div>
 </div>
</div>
 </div>
</body>
</html>
