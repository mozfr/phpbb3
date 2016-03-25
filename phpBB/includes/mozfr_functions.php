<?php
/**
 * MOZFR MODS
 * @package phpBB3
 * @version $Id$
 */

/**
 * Append poster's User Agent to message to help support
 * Don't insert UA in edit mode, this way a moderator can mark a subject
 * as Resolved without inserting his own UA in the original message.
 *
 * @param  object $user User object, contains the UA information
 * @param  string $mode Editing mode
 * @return string       The html fragment containing the UA or an empty string
 */
function showUserAgent($user, $mode) {
    if ($mode != 'edit') {
        return "\n <i style=\"color:gray; font-size:0.9em\">Votre Navigateur&nbsp;: {$user->data['session_browser']}</i>";
    }

    return '';
}
