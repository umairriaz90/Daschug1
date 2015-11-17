<?php

/**
 * Returns a status code that determines the possible actions of a user towards the event
 * -1: doesn't have permissions
 * 0: has permissions, can join
 * 1: has permissions, cannot join because the event is full
 * 2: is participating
 * 3: has permissions, but has to wait untill allowed to participate
 * 4: has permissions, but has already signed in on another event on this topic
 * @param type $userID
 * @param type $eventID
 */
function getParticipantsStatus($userID, $eventID) {
    if (EventDatabaseManager::isEventAvailable($userID, $eventID)) {
        if (!EventDatabaseManager::isSignedIn($userID, $eventID)) {
            if (!EventDatabaseManager::isSignedForEventsTopic($userID, $eventID)) {
                if (!EventDatabaseManager::hasToWait($userID, $eventID)) {
                    if (!EventDatabaseManager::isEventFull($eventID)) {
                        return 0;
                    }
                    else
                        return 1;
                }
                else
                    return 3;
            }
            else
                return 4;
        }
        else
            return 2;
    }
    else
        return -1;
}

function eventsList() {

    $current_user = wp_get_current_user();

    if (isset($_GET['eventaction'])) {
        $event_action = (int) $_GET['eventaction'];
        if (is_numeric($_GET['eventid'])) {
            switch ($event_action) {
                case 0:
                    EventDatabaseManager::signIn($current_user->ID, $_GET['eventid']);
                    MailNotifications::sendSignInMail($current_user->user_email, EventDatabaseManager::getEvent($_GET['eventid']));
                    wp_redirect(wp_get_referer());
                    break;
                case 1:
                    break;
                case 2:
                    EventDatabaseManager::signOut($current_user->ID, $_GET['eventid']);
                    MailNotifications::sendSignOutMail($current_user->user_email, EventDatabaseManager::getEvent($_GET['eventid']));
                    wp_redirect(wp_get_referer());
                    break;
                default:
                    break;
            }
        }
        $events_custom = EventDatabaseManager::getAvailableEventsForUser($current_user->ID);
        return View::outputEventListForUser($_GET['page_id'], $_GET['page_id'], $current_user->ID, $events_custom);
    } elseif (isset($_GET['eventID'])) {
        $event = EventDatabaseManager::getEvent($_GET['eventID']);
        return eventsInfo($event);
    } else {
        $events_custom = EventDatabaseManager::getAvailableEventsForUser($current_user->ID);
        return View::outputEventListForUser($_GET['page_id'], $_GET['page_id'], $current_user->ID, $events_custom);
    }
}

/**
 * 
 * @param Event $event
 */
function eventsInfo($event) {
    $current_user = wp_get_current_user();
    $output = View::outputEventInfo($event);

    $participantsStatus = getParticipantsStatus($current_user->ID, $event->getEventID());
    $action = View::eventActionOutput($_GET['page_id'], $participantsStatus, $event->getEventID());
    $output .= "<table><tr><th>$action</th><th><a href = '?page_id=".$_GET['page_id']."'>".BACK."</a></tr>";
    $output .= "</table></div>";
    return $output;
}

function getCourseByTopic($topicID)
    {
    $courseID = EventDatabaseManager::getCourseByTopic($topicID);
    return $courseID;
    }

function getCertificateDate($userID, $courseID)
    {
    $certificate_date = EventDatabaseManager::getCertificateDate($userID, $courseID);
    return $certificate_date;
    }

function isTopicActive($userID, $topicID, $repetitionFrequency)
    {
    $str = "";    
    $str = $userID."  ".$topicID."  ".$repetitionFrequency." -> ";
    $courseID = EventDatabaseManager::getCourseByTopic($topicID);
    $certificate_date = EventDatabaseManager::getCertificateDate($userID, $courseID);
    
    $rep_date = strtotime($certificate_date." +".$repetitionFrequency." days");
    
    
    $rep_date = date("d.m.Y", time());
    $str .= $rep_date."<br>";
    
    $str = "";
    if ($certificate_date)
        {
        return 'done';
        }
    else
        {
        if ($repetitionFrequency==0) 
            {
            // RepFreq = 0 -> keine Wiedervorlage
            $str .= " <span style=\"background: green; color: white;\">0:keine&nbsp;WV</span>";
            } 
            else 
            {
            if (strtotime($certificate_date." +".$repetitionFrequency." days") - strtotime('today') < 0) 
                {
                // vergangen
                $str .= " <span style=\"background: red; color: white;\">V:zu&nbsp;bearb.</span>";
                }  
            if (strtotime($certificate_date." +".$repetitionFrequency." days") - strtotime('today') >= 0) 
                {
                // heute oder zukunft
                $str .= " <span style=\"background: green; color: white;\">Z:erledigt</span>";
                } 
            }

        }
    return $str;
}
    
?>
