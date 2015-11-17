<?php

/*
 * Functions for managing additional entities, such as mandants, locations and topics
 */

require_once 'Event.php';
require_once 'EventDatabaseManager.php';
require_once 'View.php';

/**
 * Outputs the form for adding new mandant to system
 */
function add_mandant() {
    View::loadScripts();
    View::linkToBack('edit_mandants');
    View::mandantFormOutput();
}

/**
 * Outputs the form for adding new location to system
 */
function add_location() {
    View::loadScripts();
    View::linkToBack('edit_locations');
    View::locationFormOutput();
}

/**
 * Outputs the form for adding new topic to system
 */
function add_topic() {
    View::loadScripts();
    View::linkToBack('edit_topics');
    View::topicFormOutput();
}

/**
 * Outputs the form for editing existing mandant
 */
function edit_mandants() {
    View::loadScripts();
    if (isset($_GET['order']))
        $orderAttribute = mysql_real_escape_string($_GET['order']);
    else {
        $orderAttribute = "mandantID";
    }
    $mandants = EventDatabaseManager::getAllMandants($orderAttribute);
    View::outputAllMandants('edit_mandants', $mandants);
    View::linkToAddMandant();

    if (isset($_GET['mandantID']) && is_numeric($_GET['mandantID'])) {
        $mandant = EventDatabaseManager::getMandant($_GET['mandantID']);
        $locations = EventDatabaseManager::getAllLocations();
        $mandantLocations = EventDatabaseManager::getMandantLocations($_GET['mandantID']);
        View::mandantFormOutput($mandant['mandantID'], $mandant['company'], $locations, $mandantLocations);
    } else if (isset($_GET['mandantID']))
        echo MANDANT_ID_INCORRECT_MESSAGE;
}

/**
 * Outputs the form for editing existing location
 */
function edit_locations() {
    View::loadScripts();
    $locations = EventDatabaseManager::getAllLocations();
    View::outputAllLocations('edit_locations', $locations);
    View::linkToAddLocation();

    if (isset($_GET['locationID']) && is_numeric($_GET['locationID'])) {
        $location = EventDatabaseManager::getLocation($_GET['locationID']);
        View::locationFormOutput($location['locationID'], $location['name']);
    } else if (isset($_GET['locationID']))
        echo MANDANT_ID_INCORRECT_MESSAGE;
}

/**
 * Outputs the form for editing existing topic
 */
function edit_topics() {
    View::loadScripts();
    $topics = EventDatabaseManager::getAllTopics(true);
    View::outputAllTopics('edit_topics', $topics);
    View::linkToAddTopic();
    if (isset($_GET['topicID']) && is_numeric($_GET['topicID'])) {
        $topic = EventDatabaseManager::getTopic($_GET['topicID']);
        View::topicFormOutput($topic);
    }
}

//var_dump($_POST);

/*
 * Add, delete or edit mandant
 */
if (isset($_POST['company'])) {
    if (isset($_POST['deleteMandant']) && $_POST['deleteMandant'] == 'yes') {
        if (is_numeric($_GET['mandantID'])) {
            if (EventDatabaseManager::deleteMandant($_GET['mandantID']))
                echo MANDANT_DELETED_MESSAGE;
            else {
                echo MANDANT_NOT_DELETED_MESSAGE;
            }
        }
    } else {
        if (isset($_GET['mandantID']) && is_numeric($_GET['mandantID'])) {
            EventDatabaseManager::updateMandant($_GET['mandantID'], mysql_real_escape_string($_POST['company']));
            $locations = EventDatabaseManager::getAllLocations();
            foreach ($locations as $id => $locationName) {
                if (isset($_POST['location_' . $id])) {
                    EventDatabaseManager::addLocationToMandant($_GET['mandantID'], $id);
                } else {
                    EventDatabaseManager::removeLocationFromMandant($_GET['mandantID'], $id);
                }
            }
        } else if (!isset($_GET['mandantID'])) {
            if (EventDatabaseManager::addMandant(mysql_real_escape_string($_POST['company'])))
                echo MANDANT_ADDED_MESSAGE;
        }
    }
}

/*
 * Add, edit or delete topic
 */
if (isset($_POST['Name'])) {
    if (isset($_POST['deleteEvent']) && $_POST['deleteEvent'] == 'yes') {
        if (is_numeric($_GET['topicID'])) {
            if (EventDatabaseManager::deleteTopic($_GET['topicID']))
                echo "TOPIC_DELETED_MESSAGE";
            else {
                echo "TOPIC_NOT_DELETED_MESSAGE";
            }
        }
    } else {
    $topicParams = array();
    foreach ($_POST as $key => $value) {
        if (array_search($key, EventDatabaseManager::$topicParams)) {
            $topicParams[$key] = mysql_real_escape_string($value);
        }
    }

    if (isset($_GET['topicID']) && is_numeric($_GET['topicID'])) {
        if (EventDatabaseManager::updateTopic($_GET['topicID'], $topicParams))
            echo TOPIC_UPDATED_MESSAGE;
    }
    else {

        if (EventDatabaseManager::addTopic($topicParams))
            echo TOPIC_ADDED_MESSAGE;
        else
            echo TOPIC_NOT_ADDED_MESSAGE;
    }
    }
}

/*
 * Add, edit or delete topic
 */
if (isset($_POST['locationName'])) {
    if (isset($_POST['deleteLocation']) && $_POST['deleteLocation'] == 'yes') {
        if (is_numeric($_GET['locationID'])) {
            if (EventDatabaseManager::deleteLocation($_GET['locationID']))
                echo MANDANT_DELETED_MESSAGE;
            else {
                echo MANDANT_NOT_DELETED_MESSAGE;
            }
        }
    } else {
        if (isset($_GET['locationID']) && is_numeric($_GET['locationID'])) {
            EventDatabaseManager::updateLocation($_GET['locationID'], mysql_real_escape_string($_POST['name']));
            
        } else if (!isset($_GET['locationID'])) {
            if (EventDatabaseManager::addLocation(mysql_real_escape_string($_POST['locationName'])))
                echo MANDANT_ADDED_MESSAGE;
        }
    }
}
?>
