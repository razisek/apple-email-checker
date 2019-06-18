<?php

/*
 * Mukhlis Akbarrudin
 * 18 June 2019
 * Recode doesn't make you a Coder
 * Don't delete or change anything here
 */

require_once('vendor/autoload.php');

$climate = new League\CLImate\CLImate;

date_default_timezone_set('Asia/Jakarta');

use Curl\Curl;

function get_rand_string ($ln) {
    $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLn = strlen($char);
    $rnd = '';
    for ($i = 0; $i < $ln; $i++) { $rnd .= $char[rand(0, $charLn - 1)]; }
    return $rnd;
}

function get_sstt () {
    $curl = new Curl();
    $curl->setUserAgent(get_rand_string(100));
    $curl->setCookieJar('cookies/cookies.txt');
    $curl->get('https://iforgot.apple.com/password/verify/appleid');
    preg_match('/encodeURIComponent\(\"(.*?)\"\)/', $curl->response, $match);
    return $match[1];
}

function get_status ($email) {
    $curl = new Curl();
    $sstt = get_sstt();
    $curl->setUserAgent(get_rand_string(100));
    $curl->setCookieFile('cookies/cookies.txt');
    $curl->setHeader('Content-Type', 'application/json');
    $curl->setHeader('sstt', $sstt);
    $curl->post('https://iforgot.apple.com/password/verify/appleid', array(
        "id" => $email,
    ));
    return $curl->getResponseHeaders()['content-location'];
}

function get_valid_or_not ($email) {
    $respon = get_status($email);
    if ($respon === '/password/verify/appleid') {
        $file = fopen('rezult/live.txt', 'a');
        fwrite($file, $email ."\n");
        fclose($file);
        return "<light_green>Live</light_green>";
    } elseif ($respon === 'account/emailnotfound') {
        $file = fopen('rezult/die.txt', 'a');
        fwrite($file, $email ."\n");
        fclose($file);
        return "<light_red>Die</light_red>";
    } else {
        $file = fopen('rezult/unknown.txt', 'a');
        fwrite($file, $email ."\n");
        fclose($file);
        return "<light_blue>Unknown</light_blue>";
    }
}

// get email from run.php
$options = getopt("e:");
$email = $options["e"];

$progress = array(
    "[+] <light_blue>eppleval v1.0</light_blue>",
    " - <light_yellow>".date("Y-m-d H:i:s")."</light_yellow>",
    " - <light_magenta>$email</light_magenta> -> ".get_valid_or_not($email)
);
$climate->out($progress[0].$progress[1].$progress[2]);
