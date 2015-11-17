<?php


/**
 * A class of managing email notifications sent to users on different occasions
 *
 * @author oxana
 */
class MailNotifications {
    public static $headers = "Content-type: text/html";
    /**
     * Sends a mail notification that the user is signed in for the event
     * 
     * @param string $email
     * @param Event $event
     */
    public static function sendSignInMail($email, $event) {
        $message = MAIL_CONTENT_SIGNIN . View::outputEventInfo($event, array(EVENT_TITLE_LONG => 'title_long',
                    EVENT_TITLE_SHORT => 'title_short',
                    EVENT_DATE_TIME => 'date_time'
					/** LOCATION_NAME => 'address'
					*/
					))
                ."\r\n".MAIL_CONTACT_INFO;
        wp_mail($email, MAIL_CONTENT_HEADER_SIGNIN, $message, MailNotifications::$headers);
    }
    
    /**
     * Sends a mail notification that the user is signed out from the event
     * 
     * @param string $email
     * @param Event $event
     */
    public static function sendSignOutMail($email, $event) {
        $message = MAIL_CONTENT_SIGNOUT . View::outputEventInfo($event, array(EVENT_TITLE_LONG => 'title_long',
                    EVENT_TITLE_SHORT => 'title_short',
                    EVENT_DATE_TIME => 'date_time'))
                ."\r\n".MAIL_CONTACT_INFO;
        wp_mail($email, MAIL_CONTENT_HEADER_SIGNOUT, $message, MailNotifications::$headers);
    }
}

?>
