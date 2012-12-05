<?php

/*****************************************************/
/******************* Stop Forum Spam *****************/
/************ By Joe Hayes, for ProphpBB.com *********/
/******************** Version 1.0.6 ******************/
/*****************************************************/

// Send out an email if someone is rejected?
$sfs_send_email    = true;
$sfs_email_address = 'mozilla@chevrel.org'; // Also used by default in message to spammer

// Log a message to the administrator log?
$sfs_log_admin_message = true;

// Threshold score to reject registration
$sfs_threshold_score = 5; // Records of the IP, email and the username found in the SFS database

// Email addresses to overrride and allow
$allowed_emails = array(
    'support@prophpbb.com',
    'abuse@prophpbb.com',
    'mozilla@chevrel.org',
);

/* Message to spammer */
$sfs_message = '<h1>No soup for you!</h1><p>It looks like you\'ve been flagged as a spammer. <em>You may not pass.</em> If you feel like this decision was made in error, you may contact '.$sfs_email_address.'</p>';

/*****************************************************/
/*************** DO NOT MODIFY BELOW *****************/
/*****************************************************/

// Turns object results into an array, from http://www.stopforumspam.com/apiscode
function objectsIntoArray($arrObjData, $arrSkipIndices = array()) {
    $arrData = array();

    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices);
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

// Where the good stuff happens
function checkStopForumSpam($username, $email, $ip, $message, $allowed_emails) {
    // Default value
    $spam_score = 0;

    // Override allowed emails
    if (in_array($email, $allowed_emails)) {
        return $spam_score;
    }

    // Query the SFS database and pull the data into script
    $xmlUrl = 'http://www.stopforumspam.com/api?username='.$username.'&ip='.$ip.'&email='.$email.'&f=xmldom';
    $xmlStr = get_file($xmlUrl);

    // Check if user is a spammer, but only if we successfully got the SFS data
    if ($xmlStr) {
        $xmlObj = @simplexml_load_string($xmlStr);
        $arrXml = objectsIntoArray($xmlObj);

        // Assign points for the total number of times each have been flagged
        $ck_username = $arrXml['username']['frequency'];
        $ck_email = $arrXml['email']['frequency'];
        $ck_ip = $arrXml['ip']['frequency'];

        // Let's not ban a registrant with a common username, who is otherwise clean
        if ($ck_email + $ck_ip == 0) {
            $ck_username = 0;
        }

        // Let's not ban a registrant with a common IP address, who is otherwise clean
        if ($ck_username + $ck_email == 0) {
            $ck_ip = 0;
        }

        // Return the total score
        $spam_score = ($ck_username + $ck_email + $ck_ip);
    }

    return $spam_score;
}

// Gettin' Jiggy Wit It
$spam_value = checkStopForumSpam($data['username'], $data['email'], $user->ip, $sfs_message, $allowed_emails);

// If we've got a spammer we'll take away their soup!
if ($spam_value >= $sfs_threshold_score) {

    // Do we need to send an email? If so, do it
    if ($sfs_send_email) {
        $headers = 'From: '.$sfs_email_address."\r\n"
                .'Reply-To: '.$sfs_email_address."\r\n"
                .'X-Mailer: PHP/'.phpversion();
        $message = "Stopped a spam registration...\n\nUsername: ".$data['username']
                ."\nEmail: ".$data['email'].
                "\nIP: ".$user->ip.
                "\nScore: ".$spam_value;
        mail($sfs_email_address, 'Forum Spam Stopped!', $message, $headers);
    }

    // Do we need to log an administrator message?
    if ($sfs_log_admin_message)
    {
        $sfs_ip_check = '<a target="_new" title="Check IP at StopForumSpam.com (opens in a new window)" href="http://www.stopforumspam.com/ipcheck/'.$user->ip.'">IP</a>';
        $sfs_username_check = '<a target="_new" title="Check Username at StopForumSpam.com (opens in a new window)" href="http://www.stopforumspam.com/search/?q='.$data['username'].'">Username</a>';
        $sfs_email_check = '<a target="_new" title="Check Email at StopForumSpam.com (opens in a new window)" href="http://www.stopforumspam.com/search/?q='.$data['email'].'">Email</a>';

        add_log('admin' , '<strong>Spam Registration Stopped: '.$sfs_username_check.' - '.$sfs_email_check.' - '.$sfs_ip_check.'</strong>');
    }

    // Let's kill the user registration process with a nice little message
    die($sfs_message);
}

// Try to get with file_get_contents, else use curl
function get_file($url)
{
    if (ini_get('allow_url_fopen')) // file_get_contents() will work
    {
        return @file_get_contents($url);
    }
    else if (function_exists('curl_init')) // We'll use curl
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);

        return ($contents) ? $contents : FALSE;
    }
    else // Neither working
    {
        echo 'Could not user file_get_contents or cURL. Get host to enable one of them.';
        return FALSE;
    }
}