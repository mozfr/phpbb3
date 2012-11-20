<?php
/**
*
* @package phpBB3
* @version $Id$
* MOZFR MODS
*/


/*
 * Append poster's User Agent to message to help support
 */

function showUserAgent($user) {
    $ua = "\n<aside style=\"color:gray; font-size:0.9em\"><em>Votre Navigateur&nbsp;: {$user->data['session_browser']}</em></aside>";
    return $ua;
}
