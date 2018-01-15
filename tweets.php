<?php

session_start();
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$accessToken = !empty($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
$config = (require 'config.php');
$user = !empty($_GET['user']) ? $_GET['user'] : null;

if (empty($accessToken)) {
    throw new Exception("Unauthorized request", 401);
    die();
}

if (empty($user)) {
    throw new Exception("User is required", 442);
    die();
}

// initiate getTweets
function getTweets($connection, $user = 'snowden', $tweets = [], $count = 200, $maxCount = 500, $tries = 1) {
    $end = $count * $tries;

    // get recent tweeets by $user on twitter
    $options = [
        'count' => $count,
        'exclude_replies' => true,
        'screen_name' => $user,
        'include_rts' => true
    ];

    if (!empty($tweets)) {
        $options['max_id'] = $tweets[count($tweets) - 1]->id_str;
    }
    
    $newTweets = $connection->get('statuses/user_timeline', $options);
    
    $result = array_merge($tweets, $newTweets);

    if ($end < $maxCount) {
        return getTweets($connection, $user, $result, $count, $maxCount, ++$tries);
    }

    return $result;
}

$token = $_SESSION['access_token'];

$connection = new TwitterOAuth(
    $config['twitter']['key'],
    $config['twitter']['secret'],
    $token['oauth_token'],
    $token['oauth_token_secret']
);

$tweets = getTweets($connection, $user, [], 200, 500, 1);

echo json_encode($tweets);
