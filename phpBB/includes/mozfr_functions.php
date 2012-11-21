<?php
/**
*
* @package phpBB3
* @version $Id$
* MOZFR MODS
*/


/*
 * Append poster's User Agent to message to help support
 * Don't insert UA in edit mode, this way a moderator can mark a subject
 * as Resolved without inserting his own UA in the original message.
 *
 * Variables:
 * @user object
 * @mode array
 * 
 */

function showUserAgent($user, $mode) {
    if ($mode != 'edit') {
        return "\n <i style=\"color:gray; font-size:0.9em\">Votre Navigateur&nbsp;: {$user->data['session_browser']}</i>";
    } else {
        return '';
    }
}
