<?php


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EventDatabaseManager
 *
 * @author oxana
 */

define("STATUS_SIGN_IN", 'signed_in');
define("STATUS_SIGN_OUT", 'signed_out');
define("STATUS_COMPLETED", 'completed');
define("STATUS_MISSED", 'missed');

require_once( ABSPATH . 'wp-includes/load.php' );

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

include_once 'Event.php';
include_once 'Address.php';

/**
 * Takes care of reading and storing application data according to business logic
 */
class EventDatabaseManager {
    
    public static $userHasTopicsParams = array(
        USER_PARAMS_REPETITION_FREQUENCY_DAYS => 'repetition_frequency_days', 
        USER_PARAMS_EXPIRY_DATE => 'topic_expiry_date', 
        USER_PARAMS_CURRENT_DEADLINE => 'current_deadline', 
        USER_PARAMS_REMIND_FREQUENCY_DAYS => 'remind_frequency_days'
        );

    public static $topicParams = array(
        "Name" => 'Name',
        'Template: Long Title' => 'template_title_long',
        'Template: Short Title' => 'template_title_short',
        'Template: Description' => 'template_description',
        'Template: Duration (min)' => 'template_duration_min',
        'Template: Content Link' => 'content_link');

    /**
     * Gets the list of all events in database
     * The events can be ordered by order attribute (a column name)
     * The order direction should be "desc" or "asc"
     * @global wpdb $wpdb
     * @param String $orderAttribute
     * @param String $orderDirection
     * @return Array
     */
    public static function getEventsList($orderAttribute = "", $orderDirection = "asc") {
        global $wpdb;
        $query = "SELECT eventID, eventType, title_long, title_short, date_time, duration_min, invitation_text, lecturer_name, booked_participants, max_participants, topicID, mandantID, datr_Locations.name as location, event_visible 
            FROM datr_Events JOIN datr_Addresses JOIN datr_Locations 
            WHERE datr_Addresses.addressID = datr_Events.addressID 
            AND datr_Locations.locationID = datr_Addresses.locationID";
        if ($orderAttribute != "")
            $query .= " ORDER BY $orderAttribute $orderDirection";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res;
    }


    /**
     * Returns filter of results by column value to database query
     * 
     * @param String $column
     * @param String $type
     * @param String $value
     * @return String
     */
    private static function addFilter($column, $type, $value) {
        if ($type == "int")
            return " $column = $value";
        else
            return " $column = '$value'";
    }

    /**
     * Adds new location to the database
     * Returns the ID of a new location on success, FALSE if no location was added
     * 
     * @global wpdb $wpdb
     * @param String $name
     * @return int|boolean
     */
    public static function addLocation($name) {
        global $wpdb;
        $query = "INSERT INTO datr_Locations (name) VALUES ('$name')";
        if ($wpdb->query($query))
            return $wpdb->insert_id;
        else
            return false;
    }
    
    /**
     * Returns the ID of a corresponding location (it is assumed, that the location names are unique)
     * If no location with such name found, returns -1
     * @global wpdb $wpdb
     * @param String $locationName
     * @return int
     */
    private static function getLocationID($locationName) {
        global $wpdb;
        $query = "SELECT locationID FROM datr_Locations WHERE name = '$locationName'";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return -1;
        
        return $res[0]['locationID'];
    }
    
    
    /**
     * Adds access to location for user
     * Returns TRUE if location has been added, FALSE if there has been an error, or if the location is already available to user
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $locationID
     * @return boolean
     */
    public static function addLocationToUser($userID, $locationID) {
        global $wpdb;
         
        $locations = EventDatabaseManager::getUserLocations($userID);
        
        if (isset($locations[$locationID]))
                return false;
        
        $query = "INSERT INTO datr_User_has_Locations (userID, locationID) VALUES ($userID, $locationID)";
                
        return $wpdb->query($query);
            
    }
    
    /**
     * Adds a location available for mandant
     * Returns TRUE if location has been added, FALSE if there has been an error, or if the location is already available to mandant
     * 
     * @global wpdb $wpdb
     * @param int $mandantID
     * @param int $locationID
     * @return boolean
     */
    public static function addLocationToMandant($mandantID, $locationID) {
        global $wpdb;
        
        $locations = EventDatabaseManager::getMandantLocations($mandantID);
        
        if (isset($locations[$locationID]))
                return false;
        
        $query = "INSERT INTO datr_Mandant_has_Locations (mandantID, locationID) VALUES ($mandantID, $locationID)";
        
        return $wpdb->query($query);
    }
    
    /**
     * Removes location from the list of available locations for user
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $locationID
     * @return boolean
     */
    public static function removeLocationFromUser($userID, $locationID) {
        global $wpdb;
        $query = "DELETE FROM datr_User_has_Locations WHERE userID =  $userID AND locationID =  $locationID";
        
        return $wpdb->query($query);
    }
    
    /**
     * Removes location from the list of available locations for mandant
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $mandantID
     * @param int $locationID
     * @return boolean
     */
    public static function removeLocationFromMandant($mandantID, $locationID) {
        global $wpdb;
        $query = "DELETE FROM datr_Mandant_has_Locations WHERE mandantID =  $mandantID AND locationID =  $locationID";
        
        return $wpdb->query($query);
    }
    
    /**
     * Adds topic to database
     * As parameters, accepts array in form [column]=>[value]
     * Returns the ID of a new topic on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param Array $params
     * @return int|boolean
     */
    public static function addTopic($params) {
        global $wpdb;
        $query = "INSERT INTO datr_Topics (";
        foreach ($params as $key => $value) {
            $query .= "$key, ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ") VALUES (";
        foreach ($params as $key => $value) {
            if ($key == 'template_duration_min') {
                
            if ($value == '')
                $value = 'null';
                $query .= "$value, ";
                              
            if ($value == '')
                $value = 'null';
            }
            else
                $query .= "'$value', ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ")";

        if ($wpdb->query($query))
            return $wpdb->insert_id;
        else
            return false;
    }

    /**
     * Adds topic to database
     * Returns the ID of a new mandant on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param String $company
     * @return int|boolean
     */
    public static function addMandant($company) {
        global $wpdb;
        $query = "INSERT INTO datr_Mandants (company) VALUES ('$company')";
        if ($wpdb->query($query))
            return $wpdb->insert_id;
        else
            return false;
    }

    /**
     * Adds topic to database
     * Returns the ID of a new event on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param Event $event
     * @return int|boolean
     */
    public static function addEvent($event) {
        global $wpdb;
        $duration = ($event->getDuration() == null ? 'null' : $event->getDuration());
        $bookedParticipants = ($event->getBookedParticipants() == null ? 'null' : $event->getBookedParticipants());
        $maxParticipants = ($event->getMaxParticipants() == null ? 'null' : $event->getMaxParticipants());
        $visibility = ($event->getVisibility() == null ? 'null' : $event->getVisibility());
        $query = "INSERT INTO `datr_Events` 
            (`eventType`, `title_long`, `title_short`, 
            `date_time`, `duration_min`, `invitation_text`,
            `lecturer_name`, `booked_participants`, `max_participants`,
            `topicID`, `mandantID`, `addressID`, event_visible) 
            VALUES ('" . $event->getEventType()
                . "', '" . $event->getTitleLong()
                . "', '" . $event->getTitleShort()
                . "', '" . $event->getDateTime()
                . "', " . $duration
                . ", '" . $event->getInvitationText()
                . "', '" . $event->getLecturerName()
                . "', " . $bookedParticipants
                . ", " . $maxParticipants
                . ", " . $event->getTopicID() . ", "
                . $event->getMandantID() . ", "
                . $event->getAddressID() . ", "
                . $visibility
                . ")";
        return $wpdb->query($query);
    }

    /**
     * Returns TRUE if the corresponding field in Events table is an integer
     * Needed for preserving correct DB syntax in queries
     * @param String $field
     * @return boolean
     */
    private static function isEventFieldInteger($field) {
        return $field == 'duration_min' 
                || $field == 'booked_participants' 
                || $field == 'max_participants' 
                || $field == 'topicID' 
                || $field == 'mandantID' 
                || $field == 'addressID'
                || $field == 'event_visible';
    }

    /**
     * Updates the event with corresponding data
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param Event $event
     * @return boolean
     */
    public static function updateEvent($event) {
        global $wpdb;
        $query = "UPDATE `datr_Events` SET ";
        foreach ($event->getEventParams() as $key => $value) {
            if (EventDatabaseManager::isEventFieldInteger($key)) {
                $value = ($value == "" ? '0' : $value);
                $query .= "$key = $value, ";
            }
            else
                $query .= "$key = '$value', ";
        }
        $query = substr($query, 0, strlen($query) - 2) . " WHERE eventID = " . $event->getEventID();
        return $wpdb->query($query);
    }

    /**
     * Return a list of events filtered by required attributes
     * $attributes is an associative array [field] => [value]
     * $orderAttribute is a parameter for ordering
     * $orderDirection is either "asc" or "desc"
     * If all the parameters are empty, the functions returns the complete list of events stored in database
     * 
     * @global wpdb $wpdb
     * @param Array $attributes
     * @param String $orderAttribute
     * @param String $orderDirection
     * @return Array
     */
    public static function getEventsByAttributes($attributes = "", $orderAttribute = "", $orderDirection = "asc") {
        global $wpdb;
        if ($attributes == "" || count($attributes) == 0)
            return EventDatabaseManager::getEventsList($orderAttribute, $orderDirection);

        $query = "SELECT eventID, eventType, title_long, title_short, date_time, duration_min, invitation_text, lecturer_name, booked_participants, max_participants, topicID, mandantID, datr_Locations.name as location, event_visible 
            FROM datr_Events JOIN datr_Addresses JOIN datr_Locations 
            WHERE datr_Addresses.addressID = datr_Events.addressID 
            AND datr_Locations.locationID = datr_Addresses.locationID 
            AND";

        foreach ($attributes as $field => $value) {
            if (EventDatabaseManager::isEventFieldInteger($field))
                $query .= EventDatabaseManager::addFilter($field, "int", $value);
            else
                $query .= EventDatabaseManager::addFilter($field, "string", $value);
            $query .= " AND";
        }
        $query = substr($query, 0, strlen($query) - 4);
        

        if ($orderAttribute != "")
            $query .= " ORDER BY $orderAttribute $orderDirection";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res;
    }

    
    /**
     * Returns the data for event with corresponding ID
     * If no event with such ID is found, returns NULL
     * @global wpdb $wpdb
     * @param int $eventID
     * @return null|Event
     */
    public static function getEvent($eventID) {
        global $wpdb;
        $query = "SELECT * FROM datr_Events WHERE eventID = $eventID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        
        $date = $res[0]['date_time'];
        
        $res[0]['date_time'] = Utils::getTime($date);
        $event = new Event($res[0]);
        $topic = EventDatabaseManager::getTopic($event->getTopicID());
        $mandant = EventDatabaseManager::getMandant($event->getMandantID());
        $address = EventDatabaseManager::getAddress($event->getAddressID());
        $event->setMandant($mandant['company']);
        $event->setTopic($topic['Name']);
        $event->setAddress($address);
        return $event;
    }

    /**
     * Returns the list of all topics in database with corresponding data
     * If $full is FALSE: returns an array [topicID]=>[topicName]
     * Otherwise, returns all the data for the topic
     * @global wpdb $wpdb
     * @param boolean $full
     * @return Array
     */
    public static function getAllTopics($full = false) {
        global $wpdb;
        if (!$full) {
            $query = "SELECT topicID, Name from datr_Topics";
            $res = $wpdb->get_results($query, ARRAY_A);
            $topicsValues = array();
            foreach ($res as $topic) {
                $topicsValues[$topic['topicID']] = $topic['Name'];
            }
            return $topicsValues;
        } else {
            $query = "SELECT * from datr_Topics";
            return $wpdb->get_results($query, ARRAY_A);
        }
    }

    /**
     * Returns the data for a corresponding topic
     * If no topic with such ID is found, returns NULL
     * Otherwise, returns an array in form [column]=>[value]
     * 
     * @global wpdb $wpdb
     * @param int $topicID
     * @return null|Array
     */
    public static function getTopic($topicID) {
        global $wpdb;
        $query = "SELECT * FROM datr_Topics WHERE topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        return $res[0];
    }
    
    /**
     * Returns the data for a corresponding address
     * If no address with such ID is found, returns NULL
     * Otherwise, returns an Address object
     * 
     * @global wpdb $wpdb
     * @param int $addressID
     * @return null|Address
     */
    public static function getAddress($addressID) {
        global $wpdb;
        $query = "SELECT * from datr_Addresses 
            JOIN datr_Locations 
            ON datr_Addresses.locationID = datr_Locations.locationID
             WHERE addressID = $addressID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        return new Address($res[0]);
    }

    /**
     * Updates the topic with corresponding data
     * Returns TRUE on success, FALSE otherwise
     * Params are an associative array in form [column]=>[value]
     * 
     * @global wpdb $wpdb
     * @param int $topicID
     * @param Array $params
     * @return boolean
     */
    public static function updateTopic($topicID, $params) {
        global $wpdb;
        $query = "UPDATE datr_Topics SET ";
        foreach ($params as $key => $value) {
            if ($key == 'template_duration_min') {
                
            if ($value == '')
                $value = 'null';
                $query .= "$key = $value, ";
            }
            else
                $query .= "$key = '$value', ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= " WHERE topicID = $topicID";

        return $wpdb->query($query);
    }

    /**
     * Returns the data for a corresponding mandant as array [column]=>[value]
     * If no mandant with such ID found, returns NULL
     * 
     * @global wpdb $wpdb
     * @param int $mandantID
     * @return null|Array
     */
    public static function getMandant($mandantID) {
        global $wpdb;
        $query = "SELECT * FROM datr_Mandants WHERE mandantID = $mandantID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) != 0)
            return $res[0];
        else
            return null;
    }
    
    /**
     * Returns the data for a corresponding location as array [column]=>[value]
     * If no location with such ID found, returns NULL
     * 
     * @global wpdb $wpdb
     * @param int $locationID
     * @return null|Array
     */
    public static function getLocation($locationID) {
        global $wpdb;
        $query = "SELECT * FROM datr_Locations WHERE locationID = $locationID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) != 0)
            return $res[0];
        else
            return null;
    }
    
    /**
     * Returns the list of locations available for a specified mandant
     * The result is an array, where each entry has the form [locationID] => [locationName]
     * @global wpdb $wpdb
     * @param int $mandantID
     * @return Array
     */
    public static function getMandantLocations($mandantID) {
        global $wpdb;
        $query = "SELECT datr_Locations.locationID, name FROM datr_Locations 
            JOIN datr_Mandant_has_Locations 
            ON datr_Mandant_has_Locations.locationID = datr_Locations.locationID 
            WHERE mandantID = $mandantID";
        $res = $wpdb->get_results($query, ARRAY_A);
        $locationsValues = array();
        foreach ($res as $location) {
            $locationsValues[$location['locationID']] = $location['name'];
        }
        return $locationsValues;
    }

    /**
     * Updates the mandant with corresponding data
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $mandantID
     * @param String $company
     * @return boolean
     */
    public static function updateMandant($mandantID, $company) {
        global $wpdb;
        $query = "UPDATE datr_Mandants SET company = '$company' WHERE mandantID = $mandantID";
        return $wpdb->query($query);
    }
    
    /**
     * Updates the location with corresponding data
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $locationID
     * @param String $name
     * @return boolean
     */
    public static function updateLocation($locationID, $name) {
        global $wpdb;
        $query = "UPDATE datr_Locations SET name = '$name' WHERE locationID = $locationID";
        return $wpdb->query($query);
    }

    /**
     * Gets the list of all mandants in database
     * The list can be sorted by corresponding attribute
     * The entries are of the form [mandantID] => [company]
     * 
     * @global wpdb $wpdb
     * @param String $orderAttribute
     * @return Array
     */
    public static function getAllMandants($orderAttribute = 'mandantID') {
        global $wpdb;
        $query = "SELECT mandantID, company from datr_Mandants ORDER BY $orderAttribute";
        $res = $wpdb->get_results($query, ARRAY_A);
        $mandants = array();
        foreach ($res as $mandant) {
            $mandants[$mandant['mandantID']] = $mandant['company'];
        }
        return $mandants;
    }

    /**
     * Gets the list of all locations in database
     * The entries are of the form [locationID] => [locationName]
     * 
     * @global wpdb $wpdb
     * @return Array
     */
    public static function getAllLocations() {
        global $wpdb;
        $query = "SELECT locationID, name from datr_Locations";
        $res = $wpdb->get_results($query, ARRAY_A);
        $locationsValues = array();
        foreach ($res as $location) {
            $locationsValues[$location['locationID']] = $location['name'];
        }
        return $locationsValues;
    }

    /**
     * Gets the list of all addresses in database
     * The entries are of the form [addressID] => [address value]
     * 
     * @global wpdb $wpdb
     * @return Array
     */
    public static function getAllAddresses() {
        global $wpdb;
        $query = "SELECT * from datr_Addresses JOIN datr_Locations ON datr_Addresses.locationID = datr_Locations.locationID";
        $res = $wpdb->get_results($query, ARRAY_A);
        $values = array();
        foreach ($res as $addressValues) {
            $address = new Address($addressValues);
            $values[$addressValues['addressID']] = $address->getAddressValue();
        }
        return $values;
    }
    
    /**
     * Gets the list of all locations available to user
     * The list entries are of the form [location id] => [location name]
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @return Array
     */
    public static function getUserLocations($userID) {
        global $wpdb;
        $query = "SELECT name, datr_Locations.locationID as ID FROM datr_User_has_Locations JOIN datr_Locations 
            ON datr_Locations.locationID = datr_User_has_Locations.locationID
            WHERE userID = $userID";
        $res = $wpdb->get_results($query, ARRAY_A);
        
        $locations = array();
        foreach ($res as $location) {
            $locations[$location['ID']] = $location['name'];
        }
        return $locations;
    }
    
    /**
     * Adds a new address to the database
     * Also, modifies an Address object by setting the ID to a new value
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param Address $address
     */
    public static function addAddress($address) {
        global $wpdb;
        $query = "INSERT INTO `datr_Addresses` (`address`, `meeting_room`, `locationID`, `instructionLink`, `contentLink`) VALUES ('" . $address->getAddress() . "', '" . $address->getMeetingRoom() . "', " . $address->getLocationID() . ", '" . $address->getInstructionLink() . "', '" . $address->getContentLink() . "')";
        $result = $wpdb->query($query);
        $address->setID($wpdb->insert_id);
        return $result;
    }

    /**
     * Deletes the specified mandant from the system; all events for this mandant will also be deleted
     * Only if no user with this mandant exists
     * Returns TRUE on success, FALSE otherwise
     * 
     * @param int $mandantID
     * @return boolean
     */
    public static function deleteMandant($mandantID) {
        global $wpdb;
        if (!EventDatabaseManager::usersWithMandantExist($mandantID)) {
            $query = "DELETE FROM datr_Mandants WHERE mandantID = $mandantID";
            $res = $wpdb->query($query);
            return $res;
        }
        return false;
    }
    
    /**
     * Deletes the specified location from the system
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param type $locationID
     * @return type
     */
    public static function deleteLocation($locationID) {
        global $wpdb;
            $query = "DELETE FROM datr_Locations WHERE locationID = $locationID";
            $res = $wpdb->query($query);
            return $res;
    }

    /**
     * Deletes the specified event from the system
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param type $eventID
     * @return boolean
     */
    public static function deleteEvent($eventID) {
        global $wpdb;
        if (!EventDatabaseManager::eventParticipantsExist($eventID)) {
            $query = "DELETE FROM datr_Events WHERE eventID = $eventID";
            $res = $wpdb->query($query);
            return $res;
        }
        return false;
    }

    /**
     * Returns TRUE if the users with a specified mandant exist, FALSE otherwise
     * Used for determining whether a mandant can be safely deleted from the system
     * 
     * @global wpdb $wpdb
     * @param int $mandantID
     * @return boolean
     */
    private static function usersWithMandantExist($mandantID) {
        global $wpdb;
        $query = "SELECT COUNT(*) as total FROM wp_usermeta WHERE meta_key = 'datr_mandantID' AND meta_value = $mandantID";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }
    
    private static function usersWithTopicExist($topicID) {
        global $wpdb;
        $query = "SELECT COUNT(*) as total FROM datr_User_has_Topics WHERE topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }

    
    /**
     * Returns TRUE if the users that are participants of a specified event exist, FALSE otherwise
     * Also accounts for users, that have signed up for the event, but missed it or signed out later
     * Used for determining whether an event can be safely deleted from the system
     * 
     * @global wpdb $wpdb
     * @param int $eventID
     * @return boolean
     */
    private static function eventParticipantsExist($eventID) {
        global $wpdb;
        $query = "SELECT COUNT(*) as total FROM datr_User_has_Events WHERE eventID = $eventID";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }

    /**
     * Returns the events, that a user is allowed to access
     * The result is a list of associative arrays [column]=>[name]
     * The user must posess:
     * - correct mandant
     * - access to correct topics
     * - The event must be in future
     * 
     * @param int $userID
     */
    public static function getAvailableEventsForUser($userID) {
        global $wpdb;
        $mandantID = EventDatabaseManager::getMandantID($userID);
        $query = "SELECT * FROM datr_Events JOIN datr_User_has_Topics 
            ON datr_Events.topicID = datr_User_has_Topics.topicID 
            JOIN datr_Addresses ON datr_Addresses.addressID = datr_Events.addressID 
            JOIN datr_User_has_Locations ON datr_User_has_Locations.locationID = datr_Addresses.locationID 
            AND datr_User_has_Locations.userID = datr_User_has_Topics.userID 
            WHERE datr_User_has_Topics.userID = $userID 
                AND mandantID = $mandantID 
                    AND (date_time > NOW() OR eventType = 'elearning')
                    AND event_visible = 1
                ORDER BY eventType, title_long    
                ";
        $res = $wpdb->get_results($query, ARRAY_A);
        return EventDatabaseManager::getEventFullValuesArray($res);
    }
    
    /**
     * Returns the additional data for the events, such as full data for the event topic, event mandant, event address
     * @param Array $events
     * @return Array
     */
    private static function getEventFullValuesArray($events) {
        foreach($events as $index => $event) {
            $topic = EventDatabaseManager::getTopic($event['topicID']);
            $mandant = EventDatabaseManager::getMandant($event['mandantID']);
            $address = EventDatabaseManager::getAddress($event['addressID']);
            $events[$index]['topic'] = $topic['Name'];
            $events[$index]['mandant'] = $mandant['company'];
            $events[$index]['address'] = $address->getAddressValue();
            $events[$index]['location'] = $address->getLocationName();
        }
        return $events;
    }

    /**
     * Returns the mandantID of a specified user
     * @param int $userID
     * @return int
     */
    private static function getMandantID($userID) {
        return get_user_meta($userID, 'datr_mandantID', true);
    }

    /**
     * Signs a user for the event
     * A user must posess the necessary access rights (see comment for the getAvailableEventsForUser function)
     * Returns TRUE on success, FALSE otherwise
     * 
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function signIn($userID, $eventID) {
        global $wpdb;
        if (EventDatabaseManager::canParticipate($userID, $eventID)) {
            $query = "INSERT INTO `datr_User_has_Events` 
                (`userID`, `eventID`, `sign_in_datetime`, `sign_out_datetime`, `completion_date`, status) 
                VALUES ($userID, $eventID, NOW(), NULL, NULL, 'signed_in')
                    ON DUPLICATE KEY UPDATE sign_in_datetime = NOW(), status = 'signed_in';";
                                    
            if(!$wpdb->query($query))
                return false;
            
            $event = EventDatabaseManager::getEvent($eventID);
            $topicID = $event->getTopicID();
            
            if (EventDatabaseManager::isCurrentEventCompleted($userID, $topicID)) {
                $currentEventID = EventDatabaseManager::getCurrentEventID($userID, $topicID);
                if(!EventDatabaseManager::setLastEvent($userID, $currentEventID))
                        return false;
            }
            if (EventDatabaseManager::setCurrentEvent($userID, $eventID) === false) {
                return false;
            }
            else
                return EventDatabaseManager::incrementParticipantsNum($eventID);
        }
        return false;
    }
    
    /**
     * Increases the number of users, signed in for the event, by 1
     * Returns TRUE on success, FALSE otherwise
     * Used when a new user signs in for the event
     * 
     * @global wpdb $wpdb
     * @param int $eventID
     * @return boolean
     */
    public static function incrementParticipantsNum($eventID) {
        global $wpdb;
        $query = "UPDATE datr_Events SET booked_participants = booked_participants + 1 WHERE eventID = $eventID";
        return $wpdb->query($query);
    }
    
    /**
     * Decreases the number of users, signed in for the event, by 1
     * Returns TRUE on success, FALSE otherwise
     * Used when a participant signs out of the event
     * 
     * @global wpdb $wpdb
     * @param int $eventID
     * @return boolean
     */
    public static function decrementParticipantsNum($eventID) {
        global $wpdb;
        $query = "UPDATE datr_Events SET booked_participants = booked_participants - 1 WHERE eventID = $eventID";
        return $wpdb->query($query);
    }
    
    /**
     * Returns TRUE if the specified user has successfully completed the current event for a selected topic, FALSE otherwise
     * 
     * @param int $userID
     * @param int $topicID
     * @return boolean
     */
    public static function isCurrentEventCompleted($userID, $topicID) {
        return EventDatabaseManager::getCurrentEventStatus($userID, $topicID) == 'completed';
    }
    
    /**
     * Gets the status of a specified user for the current event on a specified topic
     * The status can have following values:
     * - signed_in - the user has signed in for the event, yet have not completed it yet
     * - signed_out - the user has signed out of the event after signing up for it
     * - missed - the user has signed up for the event, but have not participated in it
     * - completed - the user has successfully completed the event
     * 
     * Returns NULL, if no user or event with such ID found, or if the user has never signed up for the event
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return String|boolean
     */
    public static function getCurrentEventStatus($userID, $topicID) {
        global $wpdb;
        $query = "SELECT status FROM datr_User_has_Events 
            JOIN datr_User_has_Topics 
            ON datr_User_has_Events.eventID = datr_User_has_Topics.current_eventID 
            AND datr_User_has_Events.userID = datr_User_has_Topics.userID
            WHERE datr_User_has_Events.userID = $userID AND topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        return $res[0]['status'];
    }
    
    /**
     * Gets the ID of a current event, that a user participates in for a specified topic
     * Returns -1 if no such user or topic exist, or if the user has no access to a specified topic
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return int
     */
    public static function getCurrentEventID($userID, $topicID) {
        global $wpdb;
        $query = "SELECT current_eventID FROM datr_User_has_Topics 
            WHERE userID = $userID AND topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return -1;
        return $res[0]['current_eventID'];
    }
    
    /**
     * Returns TRUE, if the date of the current event, that a user participantes in for a specified topic, lies in future
     * Returns FALSE if the current event is in the past, if no user or topic with such ID found, if the user has not yet participated in any event on this topic, or if the user has no access to this topic
     * Also returns TRUE if the event type is E-Learning
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return boolean
     */
    public static function isCurrentEventInFuture($userID, $topicID) {
        global $wpdb;
        $query = "SELECT COUNT(*) AS total FROM datr_User_has_Topics 
            JOIN datr_Events 
            ON datr_Events.eventID = datr_User_has_Topics.current_eventID 
            WHERE userID = $userID AND topicID = $topicID AND (date_time > NOW() OR eventType = 'elearning')";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }
    
    /**
     * Sets current event for the topic for a specified user
     * Used while signing in the user for the event
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    private static function setCurrentEvent($userID, $eventID) {
        global $wpdb;
        $event = EventDatabaseManager::getEvent($eventID);
        $topicID = $event->getTopicID();
        $query = "UPDATE datr_User_has_Topics 
            SET current_eventID = $eventID 
                WHERE userID = $userID AND topicID = $topicID";
        return $wpdb->query($query);
    }
    
    /**
     * Sets last event for the topic for a specified user
     * Used while signing in the user for new event
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    private static function setLastEvent($userID, $eventID) {
        global $wpdb;
        $event = EventDatabaseManager::getEvent($eventID);
        $topicID = $event->getTopicID();
        $query = "UPDATE datr_User_has_Topics 
            SET last_eventID = $eventID 
                WHERE userID = $userID AND topicID = $topicID";
        return $wpdb->query($query);
    }
    
    /**
     * Clears the entry for a current event for a specified user on a topic
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return boolean
     */
    public static function removeCurrentEvent($userID, $topicID) {
        global $wpdb;
        $query = "UPDATE datr_User_has_Topics 
            SET current_eventID = null 
                WHERE userID = $userID AND topicID = $topicID";
        return $wpdb->query($query);
    }

    /**
     * Signes the user out of the event
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function signOut($userID, $eventID) {
        global $wpdb;
        $query = "UPDATE `datr_User_has_Events` 
                SET sign_out_datetime = NOW(), status = 'signed_out'
                WHERE userID = $userID AND eventID = $eventID";
        if (!$wpdb->query($query))
            return false;
        
        $event = EventDatabaseManager::getEvent($eventID);
        $topicID = $event->getTopicID();
        if(EventDatabaseManager::removeCurrentEvent($userID, $topicID))
                return EventDatabaseManager::decrementParticipantsNum ($eventID);
    }

    /**
     * Returns TRUE if the user is allowed to participate in the event
     * A user can sign in for the event if:
     * - an event is generally available to him/her (see getAvailableEventsForUser)
     * - an event is either of elearning type, or less than the maximum allowed number of participants have already signed in
     * - a user's last event on this topic was long ago enough
     * 
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function canParticipate($userID, $eventID) {
        if (EventDatabaseManager::isEventAvailable($userID, $eventID)) {
            if (!EventDatabaseManager::isEventFull($eventID)) {
                if (!EventDatabaseManager::hasToWait($userID, $eventID)) {
                    return !EventDatabaseManager::isSignedForEventsTopic($userID, $eventID);
                }
            }
        }
        return false;
    }

    /**
     * Returns FALSE, if the user can join the event right away
     * Return TRUE, if the user has to wait a certain amount of days in order to sign up for the event
     * Also returns TRUE if the user has no access to this event's topic, or if no user or event with such ID exist
     * 
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function hasToWait($userID, $eventID) {
        $event = EventDatabaseManager::getEvent($eventID);
        return EventDatabaseManager::daysTillAbleToJoin($userID, $event->getTopicID()) > 0;
    }

    /**
     * Returns the number of days, until the user is allowed to participate in another event on this topic
     * Returns -1 if the topic is not accessable to the user, or if no such user or topic with such ID found
     * 
     * @global wpdb $wpdb
     * @param type $userID
     * @param type $topicID
     * @return type
     */
    private static function daysTillAbleToJoin($userID, $topicID) {
        global $wpdb;
        $queryTopic = "SELECT * FROM datr_User_has_Topics JOIN datr_Events 
            ON datr_User_has_Topics.last_eventID = datr_Events.eventID
            WHERE userID = $userID AND datr_Events.topicID = $topicID";
        $res = $wpdb->get_results($queryTopic, ARRAY_A);

        if (empty($res))
            return -1;

        $interval = date_diff(date_create($res[0]['date_time']), date_create());
        $daysFromLastEvent = $interval->format('%a');
        return $res[0]['repetition_frequency'] - $daysFromLastEvent;
    }

    /**
     * Returns TRUE, if the event has already reached the limit of participants
     * 
     * @param int $eventID
     * @return boolean
     */
    public static function isEventFull($eventID) {
        $event = EventDatabaseManager::getEvent($eventID);
        return $event->getMaxParticipants() <= $event->getBookedParticipants() && $event->getEventType() != "elearning";
    }

    /**
     * Adds the specified topic as available to the user
     * The access conditions are then determined by other arguments
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @param int $repetition_frequency_days
     * @param String $current_deadline
     * @param String $topic_expiry_date
     * @param int $remind_frequency_days
     * @return boolean
     */
    public static function registerUserForTopic($userID, $topicID, $repetition_frequency_days, $current_deadline, $topic_expiry_date, $remind_frequency_days) {
        global $wpdb;
        $query = "INSERT INTO datr_User_has_Topics `datr_User_has_Topics` 
            (`userID`, `topicID`, `repetition_frequency_days`, 
            `topic_expiry_date`, `current_deadline`,
            `remind_frequency_days`) 
            VALUES ($userID, $topicID, $repetition_frequency_days, '$topic_expiry_date', 
                '$current_deadline', $remind_frequency_days)";
        return $wpdb->query($query);
    }

    /**
     * Returns TRUE if the event is available to the user (see comment for getAvailableEventsForUser)
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function isEventAvailable($userID, $eventID) {
        global $wpdb;
        $mandantID = EventDatabaseManager::getMandantID($userID);
        $query = "SELECT count(*) AS total FROM datr_Events JOIN datr_User_has_Topics 
            ON datr_Events.topicID = datr_User_has_Topics.topicID 
            JOIN datr_Addresses ON datr_Addresses.addressID = datr_Events.addressID 
            JOIN datr_User_has_Locations ON datr_User_has_Locations.locationID = datr_Addresses.locationID 
            AND datr_User_has_Locations.userID = datr_User_has_Topics.userID 
            WHERE datr_User_has_Topics.userID = $userID AND mandantID = $mandantID AND eventID = $eventID AND (date_time > NOW() OR eventType = 'elearning') AND event_visible = 1";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }
    
    /**
     * Returns TRUE if the user has access to the topic
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return boolean
     */
    public static function hasTopic($userID, $topicID) {
        global $wpdb;
        $query = "SELECT count(*) AS total FROM datr_User_has_Topics 
            WHERE userID = $userID AND topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }
    
    /**
     * Returns the parameters of a topic assignment to user (array in form [column]=>[value]
     * Returns NULL if the user has access to the topic, or if no user or topic with such ID found
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return null|Array
     */
    public static function getAssignedTopicParams($userID, $topicID) {
        global $wpdb;
        $query = "SELECT * FROM datr_User_has_Topics 
            WHERE userID = $userID AND topicID = $topicID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        
        return $res[0];
    }
    
    /**
     * Removes the assignment of a topic to user, 
     * unless the user is currently participating in event on this topic
     * Returns NULL on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @return boolean
     */
    public static function deleteAssignedTopic($userID, $topicID) {
        global $wpdb;
        if (!EventDatabaseManager::isSignedInForTopic($userID, $topicID)) {
            $query = "DELETE FROM datr_User_has_Topics WHERE topicID = $topicID AND useriD = $userID";
            $res = $wpdb->query($query);
            return $res;
        }
        return false;
    }
    
    public static function deleteTopic($topicID) {
        global $wpdb;
        if (!EventDatabaseManager::usersWithTopicExist($topicID)) {
            $query = "DELETE FROM datr_Topics WHERE topicID = $topicID";
            $res = $wpdb->query($query);
            return $res;
        }
        return false;
    }
    
    /**
     * If the topic is already available to the user, updates the assignment details with the set parameters)
     * Otherwise, adds the access for the topic to user
     * The topic parameters must be of the form [column]=>[value]
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $topicID
     * @param Array $topicParams
     * @return boolean
     */
    public static function addOrUpdateAssignedTopic($userID, $topicID, $topicParams) {
        global $wpdb;
        $query = "INSERT INTO datr_User_has_Topics (userID, topicID, ";
        foreach ($topicParams as $key => $value) {
            $query .= "$key, ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ") VALUES ($userID, $topicID, ";
        foreach ($topicParams as $key => $value) {
            if ($key == 'repetition_frequency_days' || $key == 'remind_frequency_days') {
                
                if ($value == '')
                    $value = 'null';
                $query .= "$value, ";
            }
            else
                $query .= "'$value', ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ") ON DUPLICATE KEY UPDATE ";
        foreach ($topicParams as $key => $value) {
            if ($key == 'repetition_frequency_days' || $key == 'remind_frequency_days') {
                if ($value == '')
                    $value = 'null';
                $query .= "$key = $value, ";
            }
            else
                $query .= "$key = '$value', ";
        }
        $query = substr($query, 0, strlen($query) - 2);
                
        return $wpdb->query($query);
    }

    /**
     * Returns TRUE if the user is signed in for the event, FALSE otherwise
     * 
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function isSignedIn($userID, $eventID) {
        global $wpdb;
        $query = "SELECT count(*) as total FROM datr_User_has_Events WHERE userID = $userID AND eventID = $eventID AND status = 'signed_in'";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }

    /**
     * Returns TRUE, if the user is signed up for any event on the specified event's topic
     * 
     * @param int $userID
     * @param int $eventID
     * @return boolean
     */
    public static function isSignedForEventsTopic($userID, $eventID) {
        $event = EventDatabaseManager::getEvent($eventID);
        return EventDatabaseManager::isSignedInForTopic($userID, $event->getTopicID());
    }

    /**
     * Returns TRUE, if the user is signed up for any event on the specified topic
     * 
     * @global wpdb $wpdb
     * @param type $userID
     * @param type $topicID
     * @return type
     */
    public static function isSignedInForTopic($userID, $topicID) {
        global $wpdb;
        $query = "SELECT count(*) as total FROM datr_User_has_Events JOIN datr_Events 
            ON datr_Events.eventID = datr_User_has_Events.eventID
            WHERE userID = $userID AND topicID = $topicID AND status = 'signed_in'";
        $res = $wpdb->get_results($query, ARRAY_A);
        return $res[0]['total'] != 0;
    }
    
    /**
     * Returns the list of all the users who has signed in for the event
     * The result is an array of the form [user ID] => [status]
     * The users can then have any status (on different status value, see comment for getCurrentEventStatus)
     * Returns NULL if no participants for the event found
     * 
     * @global wpdb $wpdb
     * @param int $eventID
     * @return Array|null
     */
    public static function getParticipantsList($eventID) {
        global $wpdb;
        $query = "SELECT userID, status FROM datr_User_has_Events 
            WHERE eventID = $eventID";
        $res = $wpdb->get_results($query, ARRAY_A);
        if (count($res) == 0)
            return null;
        return $res;
    }
    
    /**
     * Sets the status of a participant in the event
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $userID
     * @param int $eventID
     * @param String $status
     * @return boolean
     */
    public static function setParticipantStatus($userID, $eventID, $status) {
        global $wpdb;
        $query = "UPDATE datr_User_has_Events 
            SET status = '$status' 
                WHERE userID = $userID
                AND eventID = $eventID";
        return $wpdb->query($query);
    }

    /* GW */

     /**
    * Gets the Content Link of a given Event(ID) and returns it
    * If eventID is invalid or void, "" is returned.
    * @global wpdb $wpdb
    * @param int $eventID
    * @return string $contentLink
    * author: GW
    * created: 24.09.2014
    * last modified: 27.09.2014
    */
    public static function getContentLink($eventID) {
        global $wpdb;
        $query = "SELECT contentLink 
                  FROM datr_Addresses a, datr_Events e
                  WHERE a.addressID = e.addressID
                  AND e.eventID = ".$eventID;
        $res = $wpdb->get_results($query, ARRAY_A);
        $contentLink = $res[0]['contentLink'];
        return $contentLink;
    }


    /**
    * Gets the Event Type of a given Event(ID) and returns it
    
    * @global wpdb $wpdb
    * @param int $eventID
    * @return string $eventType
    * author: GW
    * created: 06.10.2014
    * last modified: 06.10.2014
    */
    public static function getEventType($eventID) {
        global $wpdb;
        $query = "SELECT eventType 
                  FROM datr_Events e
                  WHERE e.eventID = ".$eventID;
        $res = $wpdb->get_results($query, ARRAY_A);
        $eventType = $res[0]['eventType'];
        return $eventType;
        }


    /**
     * Returns the list of all users in the wp13_users database
     * @global wpdb $wpdb
     * @return Array
     * author: GW
     * created: 03.11.2014
     * last modified: 03.11.2014
     */
    public static function getAllUsers() {
        global $wpdb;
       
            $query = "SELECT u.id, 
                             u.display_name, 
                             u.user_email,
                             uc.course_id, 
                             uc.course_progress, 
                             um.meta_value AS mandant_id,
                             c.course_title
                      from wp13_users u
                      LEFT JOIN wp13_usermeta um 
                      ON (um.user_id = u.id AND um.meta_key = 'datr_mandantID')
                      LEFT JOIN wp13_wpcw_user_courses uc
                      ON uc.user_id = u.id
                      LEFT JOIN wp13_wpcw_courses c
                      ON c.course_id = uc.course_id
                      ORDER BY u.display_name ";
            $res = $wpdb->get_results($query, ARRAY_A);
           
            $fez = 0;
            foreach ($res as $user => $user_values) {
                //$usersValues[$user['id']] = $user['display_name'];
                //echo $user." -> ".$user_values['mandant_id']."<br>";
                $query = "SELECT um.meta_value AS first_name
                          FROM wp13_usermeta um 
                          WHERE um.user_id = ".$user_values['id']." AND um.meta_key = 'first_name'";
                $res1 = $wpdb->get_var($query);
                $res[$fez]['first_name'] = $res1;
                $query = "SELECT um.meta_value AS last_name
                          FROM wp13_usermeta um 
                          WHERE um.user_id = ".$user_values['id']." AND um.meta_key = 'last_name'";
                $res1 = $wpdb->get_var($query);
                $res[$fez]['last_name'] = $res1;
                if (isset($user_values['mandant_id']))
                    {
                    $query = "SELECT m.company AS company
                              FROM datr_Mandants m 
                              WHERE m.MandantID = ".$user_values['mandant_id'];
                    $res2 = $wpdb->get_var($query);
                    }
                else
                    {
                    $res2="";    
                    }    
                $res[$fez]['company'] = $res2;    
                $fez++;
            }
            return $res;
       
    }

    /**
     * Returns all mandants and their number of users.
     * @global wpdb $wpdb
     * @return Array
     * author: GW
     * created: 04.11.2014
     * last modified: 04.11.2014
     */
    public static function getUsersPerMandant() {
        global $wpdb;
        
        $query = "SELECT * 
                  FROM datr_Mandants 
                  ORDER BY mandantId";
        $res = $wpdb->get_results($query, ARRAY_A);
       
        $mandantsValues = array();
        $fez = 0;
        foreach ($res as $mandant => $mandantsValues) {
            $query = "SELECT count(*) 
                      FROM wp13_usermeta
                      WHERE meta_value = ".$mandantsValues['mandantID']."
                      AND meta_key = 'datr_mandantID'";
            $res1 = $wpdb->get_var($query);
            $res[$fez]['number_users'] = $res1;
            $fez++;
        }
        return $res;
        
    }

     /**
     * Returns all WPCW-Courses and their Topic.
     * @global wpdb $wpdb
     * @return Array
     * author: GW
     * created: 31.01.2015
     * last modified: 31.01.2015
     */
    public static function getTopicPerCourse() {
        global $wpdb;
        
        $query = "SELECT co.course_id AS course_id,
                  co.course_title AS course_title,
                  cht.topicID AS topic_id,
                  top.Name AS topic_title
                  FROM wp13_wpcw_courses co
                  LEFT JOIN datr_Course_has_Topic cht
                  ON (co.course_id = cht.courseID)
                  LEFT JOIN datr_Topics top
                  ON (cht.topicID = top.topicID)
                  ORDER BY cht.courseID";
        $res = $wpdb->get_results($query, ARRAY_A);

        $coursesUsers = array();
        $fez = 0;
        foreach ($res as $course => $coursesUsers) {
            $query = "SELECT uht.userID, uht.topic_expiry_date, uht.repetition_frequency_days, wpu.user_login
                      FROM datr_User_has_Topics uht
                      LEFT JOIN wp13_users wpu
                      ON uht.userID = wpu.ID
                      WHERE uht.topicID = ".$coursesUsers["topic_id"]." 
                      AND wpu.user_login IS NOT NULL
                      ORDER BY uht.userID";
                      //echo $query."<br>";

                      
            $res1 = $wpdb->get_results($query, ARRAY_A);
            //echo "<pre>";var_dump($res1);echo "</pre>";
            //echo "<br>";
            $fez1 = 0;
            foreach ($res1 as $users1 => $user1) {
                $res[$fez]['users'][$fez1]['id'] .= (int)$user1["userID"];
                $res[$fez]['users'][$fez1]['name'] .= $user1["user_login"];
                $res[$fez]['users'][$fez1]['topic_expiry_date'] .= $user1["topic_expiry_date"];
                $res[$fez]['users'][$fez1]['repetition_frequency_days'] .= $user1["repetition_frequency_days"];
                $fez1++;
                }
            $fez++;
        }
        return $res;
        //SELECT uht.userID, wpu.user_login FROM datr_User_has_Topics uht LEFT JOIN wp13_users wpu ON uht.userID = wpu.ID AND uht.topicID = 1
    }

     public static function isTopicForUserActive($userID, $topicID) {
        global $wpdb;
        $query = "  SELECT * 
                    FROM datr_User_has_Topics uht 
                    LEFt JOIN wp13_wpcw_certificates cert 
                    ON ( cert.cert_user_id = uht.userID )
                    LEFT JOIN datr_Course_has_Topic cht 
                    ON cht.topicID = uht.topicID 
                    WHERE uht.userID = $userID AND uht.topicID = $topicID";
                    $res = $wpdb->get_results($query, ARRAY_A);
        $str  = " user ".$userID." + topic ".$topicID." cert: ";
       // $str .= $res[0]['cert_generated'];
        $date = $res[0]['cert_generated'];
        if ($date!="") 
            {
            $date = date("d.m.Y",strtotime($date));
            $str .= $date;
            $str .= " + ";
            $str .= $res[0]['repetition_frequency_days'];
            $str .= " = ";
            $rep_date = strtotime($res[0]['cert_generated']." +".$res[0]['repetition_frequency_days']." days")."=";
            $str .= date("d.m.Y",$rep_date);
            }
         else
            {
            $str .= "no certificate!";
            }   

        return $str;
    }


     public static function getTopicPerCourse_test_CertDate() {
        global $wpdb;
        
        $query = "SELECT co.course_id AS course_id,
                  co.course_title AS course_title,
                  cht.topicID AS topic_id,
                  top.Name AS topic_title
                  FROM wp13_wpcw_courses co
                  LEFT JOIN datr_Course_has_Topic cht
                  ON (co.course_id = cht.courseID)
                  LEFT JOIN datr_Topics top
                  ON (cht.topicID = top.topicID)
                  ORDER BY cht.courseID";
        $res = $wpdb->get_results($query, ARRAY_A);

        $coursesUsers = array();
        $fez = 0;
        foreach ($res as $course => $coursesUsers) {
            $query = "SELECT 
                        uht.userID, 
                        uht.topicID,
                        uht.topic_expiry_date, 
                        uht.repetition_frequency_days, 
                        wpu.user_login,
                        wpc.cert_generated,
                        wpc.cert_course_id
                        FROM datr_User_has_Topics uht

                        LEFT JOIN wp13_wpcw_certificates wpc
                        ON wpc.cert_user_id = uht.userID

                        LEFT JOIN wp13_users wpu
                        ON uht.userID = wpu.ID

                        WHERE uht.topicID = ".$coursesUsers["topic_id"]." 
                        AND wpc.cert_course_id = ".$coursesUsers["course_id"]." 

                        AND wpu.user_login IS NOT NULL 
                        ORDER BY uht.userID";
                      //echo $query."<br>";

            $res1 = $wpdb->get_results($query, ARRAY_A);
            //echo "<pre>";var_dump($res1);echo "</pre>";
            //echo "<br>";
            $fez1 = 0;
            foreach ($res1 as $users1 => $user1) {
                $res[$fez]['users'][$fez1]['id'] .= (int)$user1["userID"];
                $res[$fez]['users'][$fez1]['name'] .= $user1["user_login"];
                $res[$fez]['users'][$fez1]['cert_generated'] .= $user1["cert_generated"];
                $res[$fez]['users'][$fez1]['repetition_frequency_days'] .= $user1["repetition_frequency_days"];
                $fez1++;
                }
            $fez++;
        }
        return $res;
        //SELECT uht.userID, wpu.user_login FROM datr_User_has_Topics uht LEFT JOIN wp13_users wpu ON uht.userID = wpu.ID AND uht.topicID = 1
    }

    /**
     * (re)sets the Topic for a given WPDB Course and deletes the previous assignment
     * Returns TRUE on success, FALSE otherwise
     * 
     * @global wpdb $wpdb
     * @param int $courseID
     * @param int $topicID
     * @return boolean
     */
    public static function setTopicForCourse($courseID, $topicID) {
        global $wpdb;
       
        $query = "DELETE FROM datr_Course_has_Topic 
                  WHERE courseID =  $courseID";
        $res = $wpdb->query($query);  

        $query = "INSERT INTO datr_Course_has_Topic (
                  courseID, 
                  topicID
                  ) VALUES (
                  ".$courseID.", 
                  ".$topicID.
                  ")";
        return $wpdb->query($query);
        
    }


    /* / *GW */

}

?>
