<?php
session_start();
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$config = (require 'config.php');

if (isset($_REQUEST['oauth_verifier'], $_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] == $_SESSION['oauth_token']) {
	$token = [
		'oauth_token' => $_SESSION['oauth_token'],
		'oauth_token_secret' => $_SESSION['oauth_token_secret']
	];

	$connection = new TwitterOAuth(
		$config['twitter']['key'],
		$config['twitter']['secret'],
		$token['oauth_token'],
		$token['oauth_token_secret']
	);
	
	$accessToken = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
	
	$_SESSION['access_token'] = $accessToken;
	// redirect user back to index page
	header('Location: ./');
}

throw new Exception("Error Processing Request", 1);
