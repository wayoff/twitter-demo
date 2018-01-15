<?php
session_start();
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$config = (require 'config.php');
$accessToken = !empty($_SESSION['access_token']) ? $_SESSION['access_token'] : null;

if (!empty($accessToken)) {
	header('Location: ./index.php');
	die();
}

$connection = new TwitterOAuth($config['twitter']['key'], $config['twitter']['secret']);

// get the oath token, secret from twitter api
$response = $connection->oauth('oauth/request_token', ['oauth_callback' => $config['twitter']['callback']]);

// save to session, ideally on Redis or Memcache
// and should have session array instead of using php
// own session array $_SESSION['APP_NAME'] = [ ['session_key' => 'value' ] ];

$_SESSION['oauth_token'] = $response['oauth_token'];
$_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];

$url = $connection->url('oauth/authorize', ['oauth_token' => $response['oauth_token']]);

// redirect to twitter api for authorization
header('Location: ' . $url);