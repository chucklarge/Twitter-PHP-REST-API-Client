<?php
require_once 'TwitterClient.php';

// fill in your keys - https://dev.twitter.com/apps/new
define ('CONSUMER_KEY',       '');
define ('CONSUMER_SECRET',    '');
define ('OAUTH_TOKEN',        '');
define ('OAUTH_TOKEN_SECRET', '');

$twitter = new TwitterClient(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);
$twitter->setResponseFormat('json'); // optional, json is default

// https://dev.twitter.com/docs/api/1/get/account/verify_credentials
$params = array();
$raw_response = $twitter->get('account/verify_credentials', $params);
$response = json_decode($raw_response, true);
var_dump($response);

// https://dev.twitter.com/docs/api/1/get/statuses/user_timeline
$params = array(
    'count' => 10
);
$raw_response = $twitter->get('statuses/user_timeline', $params);
$response = json_decode($raw_response, true);
var_dump($response);

// https://dev.twitter.com/docs/api/1/post/statuses/update
$params = array(
    'status' => 'I posted this with the @chucklarge php twitter reset api client. http://bit.ly/A8SQwg',
);
$raw_response = $twitter->post('statuses/update', $params);
$response = json_decode($raw_response, true);
var_dump($response);
