<?php

/*
 * Mukhlis Akbarrudin
 * 18 June 2019
 * Recode doesn't make you a Coder
 * Don't delete or change anything here
 */

require_once('vendor/autoload.php');

$climate = new League\CLImate\CLImate;

$climate->out("
<light_red> ___           _   </light_red><light_blue>__   __    _ </light_blue>
<light_red>| __|_ __ _ __| |__</light_red><light_blue>\ \ / /_ _| |</light_blue>
<light_red>| _|| '_ \ '_ \ / -_)</light_red><light_blue> V / _` | |</light_blue>
<light_red>|___| .__/ .__/_\___|</light_red><light_blue>\_/\__,_|_|</light_blue>
<light_red>    |_|  |_|               <light_yellow>v1.0</light_yellow>           
");

// set file name live, die, unknown
$file['live'] = "rezult/live.txt";
$file['die'] = "rezult/die.txt";
$file['unknown'] = "rezult/unknown.txt";

// clean rezult folder
$climate->br();
$clean = $climate->confirm('[+] Clean rezult folder ?');
if ($clean->confirmed()) {
    file_put_contents($file['live'], "");
    file_put_contents($file['die'], "");
    file_put_contents($file['unknown'], "");
}

// get email list file
$file['list'] = $climate->input('[+] Email list file ?')->prompt();

// remove duplicate line
$rmDuplicate = $climate->confirm('[+] Remove duplicate line ?');
if ($rmDuplicate->confirmed()) {
    $lines = file($file['list'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_map('trim',$lines);
    $lines = array_unique($lines);
    file_put_contents($file['list'], implode(PHP_EOL, $lines));
}

// count total email list file
$climate->br();
$climate->out("[+] Total ".count(file($file['list']))." emails");
$climate->br();

// get input req and delay
$request = $climate->input('[+] Request ?')->prompt();
$delay = $climate->input('[+] Delay ?')->prompt();
$climate->br();

// start loop popen
for ($i=0; $i<count(file($file['list'])); $i++) {

    // start popen loop
    for ($j=0; $j<$request; $j++) {
        $contents = file($file['list'], FILE_IGNORE_NEW_LINES);
        $first_line = array_shift($contents);       
        $pipe[$j] = popen('php epple.php -e='.$first_line, 'w');
        file_put_contents($file['list'], implode("\r\n", $contents));
    }

    // kill popen loop
    for ($j=0; $j<$request; ++$j) {
        pclose($pipe[$j]);
    }

    // count temp rezult file
    $count['list'] = count(file($file['list']));
    $count['live'] = count(file("rezult/live.txt"));
    $count['die'] = count(file("rezult/die.txt"));
    $count['unknown'] = count(file("rezult/unknown.txt"));

    // print chk progress
    $progress = array(
        "[+] Remaining: ".$count['list'],
        " - <light_green>Live: ".$count['live']."</light_green>",
        " - <light_red>Die: ".$count['die']."</light_red>",
        " - <light_blue>Unknown: ".$count['unknown']."</light_blue>",
        " - Ratio : $request req / $delay sec"
    );
    $climate->out($progress[0].$progress[1].$progress[2].$progress[3].$progress[4]);
    sleep($delay);
}