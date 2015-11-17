<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This is a class containing the data for the event
 *
 * @author oxana
 */
class Event {
    public static $eventParamsNames = array(EVENT_TYPE => 'eventType', 
        EVENT_TITLE_LONG => 'title_long', 
        EVENT_TITLE_SHORT => 'title_short', 
        EVENT_DATE_TIME => 'date_time', 
        EVENT_DURATION => 'duration_min', 
        EVENT_INVITATION_TEXT => 'invitation_text', 
        EVENT_LECTURER => 'lecturer_name', 
        EVENT_BOOKED_PARTICIPANTS => 'booked_participants', 
        EVENT_CAPACITY => 'max_participants', 
        EVENT_TOPIC => 'topicID', 
        EVENT_MANDANT => 'mandantID', 
        EVENT_ADDRESS => 'addressID',
        EVENT_VISIBILITY => 'event_visible');
    
    public static $integer = array('eventID', 'booked_participants', 'max_participants', 'topicID', 'mandantID', 'addressID');
    
    public static $boolean = array('event_visible');
    
    public static $eventTypes = array('offline' => EVENT_TYPE_OFFLINE, 'online' => EVENT_TYPE_ONLINE, 'elearning' => EVENT_TYPE_ELEARNING);
    
    private $eventParams;
    
    private $topic;
    
    private $mandant;
    
    private $eventID;
    
    private $address;

    public function __construct($eventParams) {
        foreach ($eventParams as $key=>$value) {
            if (!in_array($key, Event::$eventParamsNames))
                    continue;
            if ($value == '')
                $value = null;
                    $this->eventParams[$key] = $value;
        }
        if(isset($eventParams['eventID']))
            $this->eventID = $eventParams['eventID'];
    }
    
    public function getAddressID() {
        return $this->eventParams['addressID'];
    }
    
    public function getBookedParticipants() {
        return $this->eventParams['booked_participants'];
    }
    
    public function getDateTime() {
        return $this->eventParams['date_time'];
    }
    
    public function getDuration() {
        return $this->eventParams['duration_min'];
    }
    
    public function getEventID() {
        return $this->eventID;
    }
    
    public function getTitleLong() {
        return $this->eventParams['title_long'];
    }
    
    public function getTitleShort() {
        return $this->eventParams['title_short'];
    }
    
    public function getInvitationText() {
        return $this->eventParams['invitation_text'];
    }
    
    public function getLecturerName() {
        return $this->eventParams['lecturer_name'];
    }
    
    public function getMaxParticipants() {
        return $this->eventParams['max_participants'];
    }
    
    public function getTopicID(){
        return $this->eventParams['topicID'];
    }
    
    public function getMandantID() {
        return $this->eventParams['mandantID'];
    }
    
    public function getEventType() {
        return $this->eventParams['eventType'];
    }
    
    public function getParam($param) {
        return $this->eventParams[$param];
    }
    
    public function getVisibility() {
        return $this->eventParams['event_visible'];
    }
    
    public function getMandant() {
        return $this->mandant;
    }
    
    public function setMandant($mandantTitle) {
        $this->mandant = $mandantTitle;
    }
    
    public function getTopic() {
        return $this->topic;
    }
    
    public function setTopic($topicTitle) {
        $this->topic = $topicTitle;
    }
    
    public function setAddress(Address $address) {
        $this->address = $address;
    }
    
    /**
     * 
     * @return Address
     */
    public function getAddress() {
        return $this->address;
    }
    
    public function setEventID($eventID) {
        $this->eventID = $eventID;
    }
    
    public function getEventParams() {
        return $this->eventParams;
    }

}

?>
