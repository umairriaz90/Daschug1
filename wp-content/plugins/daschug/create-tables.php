<?php
/*
  Plugin Name: MediaRise Events
  Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
  Description: A brief description of the Plugin.
  Version: The Plugin's Version Number, e.g.: 1.0
  Author: Name Of The Plugin Author
  Author URI: http://URI_Of_The_Plugin_Author
  License: A "Slug" license name e.g. GPL2
 */

error_reporting(E_ALL);
//ini_set('display_errors', 1);
//include_once '../../wp-config.php';


require_once 'manage_users.php';
require_once 'Localisation.php';

require_once 'manage_other.php';
require_once 'EventDatabaseManager.php';
require_once 'MailNotifications.php';
require_once 'Utils.php';
require_once 'MessageHandling.php';
require_once 'manage_events.php';


require_once( ABSPATH . 'wp-includes/load.php' );

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//require_once(ABSPATH . 'wp-includes/pluggable.php');



function createTables() {
    echo "start";
    $query_users = "CREATE  TABLE IF NOT EXISTS `datr_User` (
  `userID` INT NOT NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `gender` VARCHAR(45) NOT NULL ,
  `outlook-feature` TINYINT(1) NOT NULL ,
  `mandantID` INT NOT NULL ,
  `title` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`userID`) )
ENGINE = InnoDB;
";


    $query_topics = "CREATE  TABLE IF NOT EXISTS `datr_Topics` (
  `topicID` INT NOT NULL AUTO_INCREMENT ,
  `Name` VARCHAR(45) NOT NULL ,
  `template_title_long` VARCHAR(45) NULL ,
  `template_title_short` VARCHAR(45) NULL ,
  `template_description` VARCHAR(45) NULL ,
  `template_duration_min` INT NULL ,
  `content_link` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`topicID`) )
ENGINE = InnoDB;";


    $query_locations = "CREATE  TABLE IF NOT EXISTS `datr_Locations` (
  `locationID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`locationID`) )
ENGINE = InnoDB;";

    $query_addresses = "CREATE  TABLE IF NOT EXISTS `datr_Addresses` (
  `address` VARCHAR(45) NULL ,
  `meeting_room` VARCHAR(45) NULL ,
  `addressID` INT NOT NULL AUTO_INCREMENT,
  `locationID` INT NOT NULL ,
  `instructionLink` VARCHAR(256) NULL ,
  `contentLink` VARCHAR(256) NULL ,
  PRIMARY KEY (`addressID`) ,
  INDEX `fk_Offline_Addresses_Locations1_idx` (`locationID` ASC) ,
  CONSTRAINT `fk_Offline_Addresses_Locations1`
    FOREIGN KEY (`locationID` )
    REFERENCES `datr_Locations` (`locationID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;";

    $query_events = "CREATE TABLE `datr_Events` (  
        `eventID` int(11) NOT NULL AUTO_INCREMENT,  
        `eventType` varchar(45) NOT NULL,  
        `title_long` varchar(256) NOT NULL,  
        `title_short` varchar(45) NOT NULL,  
        `date_time` datetime DEFAULT NULL,  
        `duration_min` int(11) DEFAULT NULL,  
        `invitation_text` text,  
        `lecturer_name` varchar(45) DEFAULT NULL,  
        `booked_participants` int(11) DEFAULT NULL,  
        `max_participants` int(11) DEFAULT NULL,  
        `topicID` int(11) NOT NULL,  
        `mandantID` int(11) NOT NULL,  
        `addressID` int(11) NOT NULL,  
        `event_visible` tinyint(1) DEFAULT NULL,  
        PRIMARY KEY (`eventID`),  
        KEY `fk_Events_Topics1_idx` (`topicID`),  
        KEY `fk_Events_Offline_Addresses_idx` (`addressID`),  
        CONSTRAINT `fk_Events_Offline_Addresses` 
        FOREIGN KEY (`addressID`) 
        REFERENCES `datr_Addresses` (`addressID`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,  
        CONSTRAINT `fk_Events_Topics1` 
        FOREIGN KEY (`topicID`) 
        REFERENCES `datr_Topics` (`topicID`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE) 
        ENGINE=InnoDB";

    $query_User_has_Topics = "CREATE  TABLE IF NOT EXISTS `datr_User_has_Topics` (
  `userID` INT NOT NULL ,
  `topicID` INT NOT NULL ,
  `current_eventID` INT NULL ,
  `last_eventID` INT NULL ,
  `repetition_frequency_days` INT NOT NULL ,
  `topic_expiry_date` DATETIME NULL ,
  `current_deadline` DATETIME NULL ,
  `last_reminded` DATETIME NULL ,
  `remind_frequency_days` INT NULL ,
  PRIMARY KEY (`userID`, `topicID`) ,
  INDEX `fk_User_has_Topics_Topics1_idx` (`topicID` ASC) ,
  INDEX `fk_current_event_idx` (`current_eventID` ASC) ,
  INDEX `fk_last_event_idx` (`last_eventID` ASC) ,
  CONSTRAINT `fk_User_has_Topics_Topics1`
    FOREIGN KEY (`topicID` )
    REFERENCES `datr_Topics` (`topicID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_current_event`
    FOREIGN KEY (`current_eventID` )
    REFERENCES `datr_Events` (`eventID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_last_event`
    FOREIGN KEY (`last_eventID` )
    REFERENCES `datr_Events` (`eventID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;";

    $query_Event_Participant_Status = "CREATE  TABLE IF NOT EXISTS `datr_Event_Participant_Status` (
  `statusID` INT NOT NULL ,
  `Name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`statusID`) )
ENGINE = InnoDB;";

    $query_User_has_Events = "CREATE  TABLE IF NOT EXISTS `datr_User_has_Events` (
  `userID` INT NOT NULL ,
  `eventID` INT NOT NULL ,
  `sign_in_datetime` DATETIME NOT NULL ,
  `sign_out_datetime` DATETIME NULL ,
  `completion_date` DATETIME NULL ,
  `status` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`userID`, `eventID`) ,
  INDEX `fk_User_has_Events_Events1_idx` (`eventID` ASC) ,
  CONSTRAINT `fk_User_has_Events_Events1`
    FOREIGN KEY (`eventID` )
    REFERENCES `datr_Events` (`eventID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB";

    $query_User_has_Locations = "CREATE  TABLE IF NOT EXISTS `datr_User_has_Locations` (
  `userID` INT NOT NULL ,
  `locationID` INT NOT NULL ,
  PRIMARY KEY (`userID`, `locationID`) ,
  INDEX `fk_User_has_Locations_Locations1_idx` (`locationID` ASC) ,
  CONSTRAINT `fk_User_has_Locations_Locations1`
    FOREIGN KEY (`locationID` )
    REFERENCES `datr_Locations` (`locationID` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;";
    
    $query_Mandants = "CREATE  TABLE IF NOT EXISTS `datr_Mandants` (
  `mandantID` INT NOT NULL AUTO_INCREMENT ,
  `company` VARCHAR(45) NULL ,
  PRIMARY KEY (`mandantID`) )
ENGINE = InnoDB";
    
    $query_Mandant_has_Locations = "CREATE  TABLE IF NOT EXISTS `datr_Mandant_has_Locations` (
  `mandantID` INT NOT NULL ,
  `locationID` INT NOT NULL ,
  PRIMARY KEY (`mandantID`, `locationID`) ,
  INDEX `fk_datr_Mandant_has_datr_Locations_datr_Locations1_idx` (`locationID` ASC) ,
  INDEX `fk_datr_Mandant_has_datr_Locations_datr_Mandants1_idx` (`mandantID` ASC) ,
  CONSTRAINT `fk_datr_Mandant_has_datr_Locations_datr_Mandants1`
    FOREIGN KEY (`mandantID` )
    REFERENCES `datr_Mandants` (`mandantID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_datr_Mandant_has_datr_Locations_datr_Locations1`
    FOREIGN KEY (`locationID` )
    REFERENCES `datr_Locations` (`locationID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB";

  $query_Courses_have_Topic = "CREATE TABLE IF NOT EXISTS `datr_Course_has_Topic (
  `courseID` int(11) NOT NULL,
  `topicID` int(11) NOT NULL,
  PRIMARY KEY (`courseID`,`topicID`),
  KEY `courseID` (`courseID`),
  KEY `topicID` (`topicID`)
) ENGINE=InnoDB";


    require_wp_db();
    global $wpdb;
    $queries = array($query_users, $query_topics, $query_locations, $query_addresses, $query_User_has_Locations, $query_Mandants, $query_Mandant_has_Locations, $query_Courses_have_Topic, $query_events, $query_User_has_Topics, $query_Event_Participant_Status, $query_User_has_Events);

    foreach ($queries as $value) {
        $wpdb->query($value);
    }
    echo "done";
}


function add_event_page() {
    add_submenu_page( null, PLUGIN_ADD_EVENT, PLUGIN_ADD_EVENT, "manage_options", "add_event", "add_event" );
}

function manage_events_page() {
    add_submenu_page( "manage_events", PLUGIN_EDIT_EVENTS, PLUGIN_EDIT_EVENTS, "manage_options", "manage_events", "edit_events" );
}

function edit_event_page() {
    add_submenu_page( null, PLUGIN_EDIT_EVENTS, PLUGIN_EDIT_EVENTS, "manage_options", "edit_event", "edit_event" );
}

function edit_participants_page() {
    add_submenu_page( null, PLUGIN_EDIT_EVENTS, PLUGIN_EDIT_EVENTS, "manage_options", "edit_participants", "edit_participants" );
}

function add_mandant_page() {
    add_submenu_page( null, PLUGIN_ADD_MANDANT, PLUGIN_ADD_MANDANT, "manage_options", "add_mandant", "add_mandant" );
}

function edit_mandants_page() {
    add_submenu_page( "manage_events", PLUGIN_EDIT_MANDANTS, PLUGIN_EDIT_MANDANTS, "manage_options", "edit_mandants", "edit_mandants" );
}

function add_topic_page() {
    add_submenu_page( null, PLUGIN_ADD_TOPIC, PLUGIN_ADD_TOPIC, "manage_options", "add_topic", "add_topic" );
}

function edit_topics_page() {
    add_submenu_page( "manage_events", PLUGIN_EDIT_TOPICS, PLUGIN_EDIT_TOPICS, "manage_options", "edit_topics", "edit_topics" );
}

function add_location_page() {
    add_submenu_page( null, PLUGIN_ADD_LOCATION, PLUGIN_ADD_LOCATION, "manage_options", "add_location", "add_location" );
}

function edit_locations_page() {
    add_submenu_page( "manage_events", PLUGIN_EDIT_LOCATIONS, PLUGIN_EDIT_LOCATIONS, "manage_options", "edit_locations", "edit_locations" );
}

function show_progress_page() {
    add_submenu_page( "manage_events", PLUGIN_SHOW_PROGRESS, PLUGIN_SHOW_PROGRESS, "manage_options", "show_progress", "show_progress" );
}

function show_courses_topic_page() {
    add_submenu_page( "manage_events", PLUGIN_SHOW_COURSES_TOPIC, PLUGIN_SHOW_COURSES_TOPIC, "manage_options", "show_courses_topic", 
      "show_courses_topic" );
}

function erase_progress_page() {
    add_submenu_page( "manage_events", PLUGIN_ERASE_PROGRESS, PLUGIN_ERASE_PROGRESS, "manage_options", "erase_progress", 
      "erase_progress" );
}

//add_action('register_post', 'check_fields', 10, 3);
//add_action('user_register', 'register_extra_fields');

function show_first_name_field() {
    echo "Hello!";
}

function plugin_menu() {
    
}

function add_plugin_menu() {
    add_menu_page(PLUGIN_MENU_TITLE, PLUGIN_MENU_TITLE, "manage_options", "manage_events", "plugin_menu");
    
}

function style_and_scripts() {
    wp_register_style( "style", plugins_url( 'daschug/plugin.css' ));
	wp_enqueue_style( 'style');
}


add_action('admin_menu', 'add_plugin_menu');
add_action('admin_menu', 'manage_events_page');
add_action('admin_menu', 'edit_event_page');
add_action('admin_menu', 'edit_participants_page');
add_action('admin_menu', 'add_event_page');
add_action('admin_menu', 'add_mandant_page');
add_action('admin_menu', 'edit_mandants_page');
add_action('admin_menu', 'add_topic_page');
add_action('admin_menu', 'edit_topics_page');
add_action('admin_menu', 'add_location_page');
add_action('admin_menu', 'edit_locations_page');
add_action('admin_menu', 'show_progress_page');
add_action('admin_menu', 'show_courses_topic_page');
add_action('admin_menu', 'erase_progress_page');

add_action( 'wp_enqueue_scripts', 'style_and_scripts' );


add_shortcode("events_list", "eventsList");

register_activation_hook( __FILE__, 'createTables' );

add_action('init','daschug_load_user_settings');
//get_currentuserinfo();


function daschug_load_user_settings() {
    global $localisation;
    $localisation = new DaschugLocalisation(get_current_user_id());
//    echo "Hello, loading user settings";
}


//test();
//createTables();
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>