<?php

require('vendor/autoload.php');

use Codebird\Codebird;

# Start routine by asking for Consumer Key Pair
echo("Welcome to the setup routine.\n");
$TWITTER_CONSUMER_KEY = readline("Please enter your TWITTER_CONSUMER_KEY: ");
$TWITTER_CONSUMER_SECRET = readline("Please enter your TWITTER_CONSUMER_SECRET: ");

if($TWITTER_CONSUMER_KEY == "" || $TWITTER_CONSUMER_SECRET == "")
{
    die("\033[31m No complete Key Pair provided\033[0m\n");
}

# Set provided key and start oauth process
Codebird::setConsumerKey($TWITTER_CONSUMER_KEY, $TWITTER_CONSUMER_SECRET);
$cb = Codebird::getInstance();
$reply = $cb->oauth_requestToken([
    'oauth_callback' => 'oob'
]);
if($reply->httpstatus != 200){
    die("\033[31m An error occured while authenticating you\033[0m\n");
}
$cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
$url = $cb->oauth_authorize();

# Show the url to the user and ask for PIN
echo("Visit the following URL and enter the returned PIN:\n");
echo($url."\n");
$pin = readline("Enter PIN: ");

if($pin == "")
{
    die("\033[31m No PIN provided\033[0m\n");
}


# Verify and set tokens.
$reply = $cb->oauth_accessToken([
    'oauth_verifier' => $pin
]);
if($reply->httpstatus != 200){
    die("\033[31m An error occured while authenticating you\033[0m\n");
}
$ACCESS_TOKEN = $reply->oauth_token;
$ACCESS_TOKEN_SECRET = $reply->oauth_token_secret;

# Write details to file
$envfile = fopen(".env", "w");
$envcontent =
"TWITTER_CONSUMER_KEY = $TWITTER_CONSUMER_KEY
TWITTER_CONSUMER_SECRET = $TWITTER_CONSUMER_SECRET
ACCESS_TOKEN = $ACCESS_TOKEN
ACCESS_TOKEN_SECRET = $ACCESS_TOKEN_SECRET";
fwrite($envfile, $envcontent);

exit("\033[32mSetup complete!\033[0m\n");

