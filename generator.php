<?php

# Require composer autoload
require('vendor/autoload.php');

# Initialize loading from .env
$dotenv = new Dotenv\Dotenv(__DIR__);

$mediaDir = __DIR__ . '/media/';
$afterTweet = ' | #ManedWolf';
$cacheFile = __DIR__ . '/cache.json';

# Define required functions
function validMedia($file)
{
    /*
     * Check if file from list is valid
     * We only take meta-files and check for the corresponding image
     * Throw and exception if the media file is missing
     */
    global $mediaDir;

    if(preg_match('/(.*).txt/', $file, $match) ? true : false)
    {
        if(count(glob($mediaDir . 'pics/' . $match[1] . '.*')) !== 0)
        {
            return true;
        } else {
            throw new RuntimeException("Media meta file exists, but media is missing (" . $file . ")");
        }
    }
    return false;
}

function checkFile($file)
{
    /*
     * Check if we used that file already
     */
    global $cacheFile;

    if (!file_exists($cacheFile)) touch($cacheFile);
    if (!is_writable($cacheFile)) throw new RuntimeException("Cache file isn't writable");
    $cache = json_decode(file_get_contents($cacheFile) ?: "[]");
    if (!in_array($file, $cache)) {
        $cache[] = $file;
        file_put_contents($cacheFile, json_encode($cache));
        return true;
    }
    return false;
}

function tweetRandomPicture()
{
    /*
    * First we determine available files
    * Then, we pick a random one.
    * After that, we remember that we have used that file already by
    * saving it in a json array
    */
    global $mediaDir, $cb, $afterTweet, $cacheFile;

    if(is_dir($mediaDir))
    {
        $fileList = scandir($mediaDir);
        if(count($fileList) == 2) throw new RuntimeException("Media dir is empty");
        $files = array_filter($fileList, "validMedia");
        sort($files);
        $id = explode('.', $files[array_rand($files)])[0];
        $meta = file_get_contents($mediaDir . $id . '.txt');
        $pic = glob($mediaDir . 'pics/' . $id . '.*')[0];
        if(!checkFile($id . '.txt'))
        {
            if(json_decode(file_get_contents($cacheFile)) == $files) throw new RuntimeException("No unused media left");
            tweetRandomPicture();
            return true;
        }
        // At this point we are ready to actually tweet the media
        $mReply = $cb->media_upload(['media' => $pic]);
        if(array_key_exists('errors', $mReply)) throw new RuntimeException("Failed to upload media: " . $mReply->errors[0]->message);
        $tReply = $cb->statuses_update([
            'status' => $meta . $afterTweet,
            'media_ids' => $mReply->media_id_string
        ]);
        if(array_key_exists('error', $tReply)) throw new RuntimeException("Failed to tweet media: " . $tReply->error);
        return true;
    } else {
        throw new RuntimeException("Media dir does not exist");
    }
}

# Check if .env exists, otherwise throw runtime exception
try
{
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e)
{
    throw new RuntimeException("Setup required, see setup.php");
}


# Check if all vars are available. If not, throw runtime exception
try
{
    $dotenv->required(['TWITTER_CONSUMER_KEY', 'TWITTER_CONSUMER_SECRET', 'ACCESS_TOKEN', 'ACCESS_TOKEN_SECRET']);
} catch (RuntimeException $e){
    throw new RuntimeException("Setup required, see setup.php");
}

# Set Tokens from env
\Codebird\Codebird::setConsumerKey($_ENV["TWITTER_CONSUMER_KEY"], $_ENV["TWITTER_CONSUMER_SECRET"]);
$cb = \Codebird\Codebird::getInstance();
$cb->setToken($_ENV["ACCESS_TOKEN"], $_ENV["ACCESS_TOKEN_SECRET"]);

# Tweet random image
# I moved that to another function for better overview
tweetRandomPicture();

#And we're done.
exit(0);