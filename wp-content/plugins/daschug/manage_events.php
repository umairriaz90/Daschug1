<?php

/*
 * Functions that manage the events in system, adding, editing and deleting them, as well as managing the event participants
 */

require_once 'Event.php';
require_once 'EventDatabaseManager.php';
require_once 'View.php';
require_once 'create-tables.php';

/**
 * Outputs the form for adding event
 */
function add_event() {
    View::linkToBack('manage_events');
    View::loadScripts();
    if (isset($_POST['event_action']) && $_POST['event_action'] == 'load_templates') {
        View::eventFormOutput(null, $_POST['topicID']);
    }
    else
        View::eventFormOutput();
}

/**
 * Outputs the list of all the events in system
 */
function edit_events() {

    if (isset($_GET['order']))
        $orderAttribute = mysql_real_escape_string($_GET['order']);
    else {
        $orderAttribute = "";
    }
    $attributes = array();
    if (isset($_GET['mandant']) && is_numeric($_GET['mandant'])) {
        $attributes['mandantID'] = $_GET['mandant'];
    }
    if (isset($_GET['topic']) && is_numeric($_GET['topic'])) {
        $attributes['topicID'] = $_GET['topic'];
    }
    $events = EventDatabaseManager::getEventsByAttributes($attributes, $orderAttribute);
    $mandants = EventDatabaseManager::getAllMandants();
    $topics = EventDatabaseManager::getAllTopics();
    View::outputAllEvents('manage_events', 'edit_event', 'edit_participants', $events, $mandants, $topics, $attributes);
    View::linkToAddEvent();
}

/**
 * Outputs the form for editing event participants, by setting their participant status
 */
function edit_participants() {
    View::linkToBack('manage_events');
    if (isset($_GET['eventID']) && is_numeric($_GET['eventID'])) {
        $eventID = mysql_real_escape_string($_GET['eventID']);
        $event = EventDatabaseManager::getEvent($eventID);

        $participants = EventDatabaseManager::getParticipantsList($eventID);
        View::outputParticipantsList($participants, $event);
    }
}

/**
 * Outputs the form for editing events
 */
function edit_event() {
    View::linkToBack('manage_events');
    View::loadScripts();
    if (isset($_GET['eventID'])) {
        $eventID = mysql_real_escape_string($_GET['eventID']);
        $event = EventDatabaseManager::getEvent($eventID);
        View::eventFormOutput($event);
    }
}

/**
 * Editing the status of the participants of the event with corresponding ID
 */
if (isset($_GET['eventID'])) {
    if (is_numeric($_GET['eventID']) || !empty($_POST)) {
        $eventID = $_GET['eventID'];
        $participants = EventDatabaseManager::getParticipantsList($eventID);
        foreach ($participants as $participant) {
            $userID = $participant['userID'];
            if (isset($_POST['status_user' . $userID])) {
                $status = $_POST['status_user' . $userID];
                if ($status == STATUS_SIGN_IN || $status == STATUS_COMPLETED || $status == STATUS_MISSED)
                    EventDatabaseManager::setParticipantStatus($userID, $eventID, $status);
            }
        }
    }
}

/**
 * Adding, editing or deleting the event
 */
if (isset($_POST['eventType'])) {
    daschug_load_user_settings();


    /*
     * Deleting the event
     */
    if (isset($_POST['deleteEvent']) && $_POST['deleteEvent'] == 'yes') {
        $eventID = mysql_real_escape_string($_GET['eventID']);
        if (EventDatabaseManager::deleteEvent($eventID))
            MessageHandling::m(EVENT_DELETED_MESSAGE);
        else {
            MessageHandling::m(EVENT_NOT_DELETED_MESSAGE);
        }
    } 
    /*
     * Adding the event
     */
    else if ($_POST['event_action'] == 'add_event') { 
//        daschug_load_user_settings();

        $eventParams = array();
        foreach ($_POST as $key => $value) {
            if (array_search($key, Event::$eventParamsNames)) {
                $eventParams[$key] = mysql_real_escape_string($value);
            }
        }
        $eventParams['date_time'] = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day']
                . " " . $_POST['hour'] . ':' . $_POST['min'];

        /**
         * If no existing address is selected, it is assumed that a user creates a new address
         */
        if ($_POST['addressID'] == '') {
            $addressValues = array();
            foreach (Address::$addressParams as $param) {
                $addressValues[$param] = $_POST[$param];
            }

            if ($_POST['locationID'] == '' && $_POST['location'] != "") {
                $addressValues['locationID'] = EventDatabaseManager::addLocation($_POST['location']);
            }
            $address = new Address($addressValues);
            EventDatabaseManager::addAddress($address);
            $eventParams['addressID'] = $address->getID();
        }

        if (!isset($_POST['event_visible']))
            $eventParams['event_visible'] = 0;

        $event = new Event($eventParams);
        if (isset($_GET['eventID'])) {
            $event->setEventID(mysql_real_escape_string($_GET['eventID']));
            $result = EventDatabaseManager::updateEvent($event);
        }
        else
            $result = EventDatabaseManager::addEvent($event);

        if ($result)
            MessageHandling::m(EVENT_ADDED_MESSAGE);
        else
            MessageHandling::m(EVENT_NOT_ADDED_MESSAGE);
    }
}

function show_progress() {
    /* this function is a copy of edit_event() */

	/*
	$mandants = EventDatabaseManager::getAllMandants();
	View::outputAllMandants('edit_mandants', $mandants);
	echo "<br><br>";


	echo "Mandants:<br><pre>";var_dump($mandants); echo "</pre>";
	echo "<br><br>";

		$topics = EventDatabaseManager::getAllTopics();

		$users = EventDatabaseManager::getAllTopics();
		echo "Topics:<br><pre>";var_dump($topics); echo "</pre>";
	*/

	$users = EventDatabaseManager::getAllUsers();

	foreach ($users as $key => $row) 
		{
		$company[$key]    = $row['company'];
		$last_name[$key] = $row['last_name'];
		}

// Die Daten mit 'company' absteigend, die mit 'last_name' aufsteigend sortieren.
// Geben Sie $data als letzten Parameter an, um nach dem gemeinsamen
// Schlüssel zu sortieren.
array_multisort($company, SORT_ASC, $last_name, SORT_ASC, $users);

	//echo "Users:<br><pre>";var_dump($users); echo "</pre>";

	$output_str  = "<h2>" . HEADER_PROGRESS . "</h2>";
    $output_str .= "<table style=\"width: 100%;\">";
	$output_str .= "<tr>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Mandant-ID</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Company</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">User-ID</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Username</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Name, Vorname</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">email</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Course</td>";
	$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Progress</td>";
	$output_str .= "</tr>";

	foreach($users as $user => $value) 
		{
		$output_str .= "<tr>";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['mandant_id']."</td>";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['company']."</td>";

		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['id']."</td>";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['display_name']."</td>";
		$lfn = "";
		if ( (isset($value['last_name'])) && (isset($value['first_name'])) ) $lfn=$value['last_name'].", ".$value['first_name'];
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$lfn."</td>";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['user_email']."</td>";
        $c = "";
        if ( (isset($value['course_title'])) && (isset($value['course_id'])) ) $c = $value['course_title']." (".$value['course_id'].")";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$c."</td>";
        $p = "";
        if (isset($value['course_progress'])) $p = $value['course_progress']." %";
		$output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$p."</td>";
		$output_str .= "</tr>";
		}	

	$output_str .= "</table>";

    $users_per_mandant = EventDatabaseManager::getUsersPerMandant();

    //echo "users_per_mandant:<br><pre>";var_dump($users_per_mandant); echo "</pre>";

    $output_str .= "<h2>" . HEADER_USERS_PER_MANDANT . "</h2>";
    $output_str .= "<table>";
    $output_str .= "<tr>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Mandant-ID</td>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Company</td>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Users</td>";
    $output_str .= "</tr>";

    foreach($users_per_mandant as $upm => $value) 
        {
        $output_str .= "<tr>";
        $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['mandantID']."</td>";
        $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['company']."</td>";
        $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$value['number_users']."</td>";
        $output_str .= "</tr>";
        }   

    $output_str .= "</table>";
	echo $output_str;

    

}

function show_courses_topic()
    {

    include_once '../wp-content/plugins/wp-courseware/lib/common.inc.php';
    include_once '../wp-content/plugins/wp-courseware/lib/constants.inc.php'; 
    include_once '../wp-content/plugins/wp-courseware/lib/email_defaults.inc.php';
    include_once '../wp-content/plugins/wp-courseware/lib/class_user_progress.inc.php';

    
    $was_saved=0;
    if ($_POST["topic_for_course"]) 
        {
        foreach ($_POST["topic_for_course"] AS $tfc_id => $tfc_value) 
            {
            $tfc_value = explode("-",$tfc_value);
            //echo $tfc_value[0]."---".$tfc_value[1]."<br>";
            $set_topic = EventDatabaseManager::setTopicForCourse($tfc_value[0], $tfc_value[1]);
            $was_saved=1;
            }
        }
    
    $output_str = "";

    $output_str .= "<form name=\"form1\" method=\"post\" action=\"".$_SERVER['REQUEST_URI']."\">";

    $topic_per_course = EventDatabaseManager::getTopicPerCourse();

   // $topic_per_course_test_cert_date = EventDatabaseManager::getTopicPerCourse_test_CertDate();

    //echo "<pre>";var_dump($topic_per_course); echo "</pre><br><br>";
    
    $output_str .= "<h2>" . HEADER_TOPICS_PER_COURSE . "</h2>";
    if ($was_saved==1) $output_str .= "<p>Gespeichert!</p>";
    $output_str .= "<table>";
    $output_str .= "<tr>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Course-ID</td>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Course-Title</td>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">Topic-Title</td>";
    $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black; background:black; color:white;\">User</td>";
    $output_str .= "</tr>";

    $topics = EventDatabaseManager::getAllTopics();

    $output_str .= "<tr>";
    $output_str .= "<td colspan=4 style=\"padding: 1px 2px; border: 1px solid black;\">Variante 1: Datum ist Topic Expiry Date</td>";
    $output_str .= "</tr>";

    foreach($topic_per_course as $tpc => $course_value) 
        {
        $topic_select = "<select name=\"topic_for_course[".$course_value['course_id']."]\">";
        $topic_select .= "<option value=\"".$course_value['course_id']."-0\">---</option>";
            foreach($topics AS $topic_id => $topic_value)
                {
                $selected = ""; 
                if ($topic_id == $course_value['topic_id']) $selected =" selected "; 
                $topic_select .= "<option ".$selected." value=\"".$course_value['course_id']."-".$topic_id."\">".$topic_value." (".$topic_id.")</option>";
                }  
            $topic_select .= "</select>";
            $user_string = "";
            //echo "tpc2:<br><pre>";var_dump($course_value['users']); echo "</pre>"; 
            foreach($course_value['users'] AS $users => $user) {
                      
                $user_string .= $user['name']."&nbsp;(";
                $user_string .= $user['id']."):&nbsp;";
                // $user_string .= date("d.m.Y",strtotime($user['topic_expiry_date'])); // wird doch gar nicht benutzt!!!

				$bla = new UserProgress($course_value['course_id'], $user['id']) ;
                //echo "<pre>";var_dump($bla); echo "</pre><br><br>";
                if ($bla->isCourseCompleted()) 
                    {
                    $user_string .= " <span style=\"background: orange; color: white;\">[completed (certificate)]</span>";
                    }

// Datum in Weiß auf Orange ist cert_generated
                    
				$active = EventDatabaseManager::isTopicForUserActive($user['id'], $course_value['topic_id']);
                $user_string .= $active;

                // $user_string .= "+" . $user['repetition_frequency_days']."=";
                //$repetition_date = strtotime($user['topic_expiry_date']." +".$user['repetition_frequency_days']." days")."=";
                //$user_string .= date("d.m.Y",(int)$repetition_date);
                // wenn repetition_frequency_days == 0 -> keine Wiedervorlage
                // topic_expiry_date durch creation_date ersetzen
                // sonst:
                
           

               
               $user_string .= "<br>";
            }
            
            $output_str .= "<tr>";
            $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$course_value['course_id']."</td>";
            $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$course_value['course_title']."</td>";
            $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">".$topic_select."</td>";
            $output_str .= "<td style=\"padding: 1px 2px; border: 1px solid black;\">[".count($course_value['users'])."]<br>".rtrim($user_string,",")."</td>";

            $output_str .= "</tr>";
        }   


            $output_str .= "<tr><td colspan=\"4\">";
            $output_str .= " <span style=\"background: orange; color: white;\"> Course Completed und Datum der Zertifkatsgenerierung </span>";
            $output_str .= " <span style=\"background: red; color: white;\"> V = Rep.Date ist Vergangenheit </span>";
            $output_str .= " <span style=\"background: green; color: white;\"> Z = Rep.Date ist Heute oder Zukunft </span>";
            $output_str .= " <span style=\"background: red; color: white;\"> K = kein Zertifikat </span>";
            $output_str .= " <span style=\"background: #FFCC00; color: black;\"> WV am </span>";
            $output_str .= " <span style=\"background: green; color: white;\"> 0 = keine WV </span>";
            $output_str .= "</td></tr>";

            $output_str .= "<tr>";
            $output_str .= "<td colspan=4 style=\"padding: 1px 2px; border: 1px solid black;\">Variante 2: Datum ist Certificate Creation Date -> weniger Treffer (wieder verworfen)</td>";
            $output_str .= "</tr>";
    
    $output_str .= "</table>";    
    
    $output_str .= "   <br>";
    $output_str .= "   <input type=\"submit\" value=\"Speichern\" />";
    $output_str .= "   <input name=\"action\" value=\"insert\" type=\"hidden\" />";
    $output_str .= " </form>";

    echo $output_str;
    }

/**
* Diese Funktion holt alle Quiz-Ergebnisse aller User. 
* Danach werden alle, die bestanden haben, aber nicht 100% erreicht haben, auf 100% gesetzt, inkl. der richtigen Antworten
* (die on-thy-fly aus der DB geholt werden)
* @author GW
* Die Fremdfunktion WPCW_quizzes_getAllQuizzesForCourse($course_value) wird benutzt, sie gehört zu Courseware 
*/

function erase_progress()
    {

    $strk = "";
    $strk = "<h2>Alle bestandenen Quiz-Ergebnisse auf 100% ändern</h2>";	

	require_once( '../wp-config.php' );

	include_once '../wp-content/plugins/wp-courseware/lib/common.inc.php';
	include_once '../wp-content/plugins/wp-courseware/lib/constants.inc.php'; 
	include_once '../wp-content/plugins/wp-courseware/lib/email_defaults.inc.php';

	if ( !defined('ABSPATH') )
	   define('ABSPATH', dirname(__FILE__) . '/');

	$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
	
	// "Richtige" Tabelle mit der Backup-Tabelle überschreiben, wenn Haken gesetzt

	if ($_REQUEST['restore_backup']==1) 
		{
        require_once( '../wp-admin/includes/upgrade.php' );
        $sql="DROP TABLE wp13_wpcw_user_progress_quizzes";
        $wpdb->query($sql);
        $sql="CREATE TABLE wp13_wpcw_user_progress_quizzes LIKE wp13_wpcw_user_progress_quizzes_backup";
        $wpdb->query($sql);     
        $sql="INSERT INTO wp13_wpcw_user_progress_quizzes SELECT * FROM wp13_wpcw_user_progress_quizzes_backup";
        $wpdb->query($sql);
		echo "wp13_wpcw_user_progress_quizzes_backup aus Backup wiederhergestellt";        
		}
	$strk .= "<form name=\"form1\" method=\"post\" action=\"".$_SERVER['REQUEST_URI']."\">";

	$str  .= "<p>1.) Welche Courses gibt es?</p>";
	//$strk .= "<p>1.) Welche Courses gibt es?</p>";

	$courses = $wpdb->get_col("
	            SELECT * 
	            FROM wp13_wpcw_courses
	            ORDER BY course_id;
	        ");


	foreach ($courses As $course_key => $course_value)
	    {
	    $str .= "Course ". $course_value."<br>";
	    }

	
	$str  .= "<p>2.) Alle Courses durchgehe und seine Quizze holen</p>";
	//$strk .= "<p>2.) Alle Courses durchgehe und seine Quizze holen</p>";

	foreach ($courses As $course_key => $course_value)
	    {
	    $str .= "<h3>Course ". $course_value."</h3>";
	    
	    $str .= "<table class = \"quiz\">";
	    $str .= "<tr class=\"quiz\"><td class=\"separator_course\" class=\"quiz\">Start Course ".$course_value."</td></tr>";    
	    $quizzes = WPCW_quizzes_getAllQuizzesForCourse($course_value);
	    $fez1 = 0;
	    foreach ($quizzes As $quiz_key => $quiz_value)
	        {
	        $fez1 ++;   
	        $str .= "<tr><td class=\"separator_quiz\">Start Quiz ID ".$quiz_value->quiz_id."</td></tr>";    
	        $str .= "<tr><td class=\"separator_quiz\" colspan=2 style=\"font-weight: bold;\">".$fez1.".) Quiz ID: ". $quiz_value->quiz_id . " - " . $quiz_value->quiz_title . "</td></tr>"; 
	        $str .= "<tr><td class=\"separator_quiz\" colspan=2>QuizPassMark: ". $quiz_value->quiz_pass_mark . "</td></tr>";
	        $str .= "<tr><td>  </td></tr>"; 
	        
	        $quizIDListForSQL = "(".$quiz_value->quiz_id.")";

	        $sql = "SELECT * 
	                FROM wp13_wpcw_user_progress_quizzes
	                WHERE user_id = ".$userID." 
	                AND quiz_id = ".$quiz_value->quiz_id." 
	                ORDER BY quiz_completed_date;
	                ";

	        $sql = "SELECT * 
	                FROM wp13_wpcw_user_progress_quizzes
	                WHERE quiz_id = ".$quiz_value->quiz_id." 
	                ORDER BY quiz_completed_date;
	                ";      
	        //$str .= $sql;
	        $results = $wpdb->get_results($sql);

	        //echo "<pre>";var_dump($results);echo "</pre>";
	        
	        $str .= "<tr><td><table><tr><td>Results for: ";

	        if ($results == NULL) $str .= "keine</td></tr></table></td></tr>";
	        //$courses = WPCW_quizzes_getQuizResultsForUser($userID, $quizIDListForSQL);
	        else 
	            {
	            $str .= "<tr><td><table><tr><td>";
	            $fez2 = 0;

	            foreach ($results As $result_key => $result_value)
	                {
	                $update="";     
	                $fez2 ++;   
	                $strk .= "Kurs ".$course_value." - Quiz ".$quiz_value->quiz_id. " - User ".$result_value->user_id;  
	                //var_dump($result_value);
	                $str .= "<tr><td><br></td></tr>";   
	                $str .= "<tr><td class=\"separator_attempt\">Start User ".$result_value->user_id." Attempt ".$result_value->quiz_attempt_id."</td></tr>";
	                $strk .= " - Attempt ".$result_value->quiz_attempt_id;  
	                $str .= "<tr><td class=\"separator_attempt\" colspan=2 style=\"font-weight: bold;\">".$fez2.".) Attempt on : ". $result_value->quiz_completed_date . " - " . $quiz_value->quiz_title . "</td></tr>";    
	                $str .= "<tr><td class=\"separator_attempt\" colspan=2>Grade: ".$result_value->quiz_grade."</td></tr>";
	                $str .= "<tr><td>  </td></tr>"; 
	        
	                //$str .= "Data: ".$result_value->quiz_data."<br>";
	                $data_seri = $result_value->quiz_data;
	                $data_unseri = unserialize($data_seri);

	                $strk .= " - Braucht ".$quiz_value->quiz_pass_mark." % - Hat: ". $result_value->quiz_grade."% ";    
	                if ($result_value->quiz_grade == 100)
	                    {
	                    $str .= "<tr><td style=\"background: #aca;\">Bestanden mit 100%  - keine Änderung nötig-> Braucht ".$quiz_value->quiz_pass_mark." % - Hat erreicht: ". $result_value->quiz_grade."%</td></tr>";
	                    $strk .= "<span style=\"background: #aca;\">Bestanden mit 100% - keine Änderung nötig</span>";
	                    }
	                elseif ($result_value->quiz_grade >= $quiz_value->quiz_pass_mark) 
	                    {
	                    $str .= "<tr><td style=\"background: #caa;\">Bestanden, aber nicht mit 100% - Änderung nötig -> Braucht ".$quiz_value->quiz_pass_mark." % - Hat erreicht: ". $result_value->quiz_grade."%</td></tr>";
	                    $strk .= "<span style=\"background: #caa;\">Bestanden, aber nicht mit 100% - Änderung nötig</span>";

	                    // Jetzt Änderung

	                    foreach($data_unseri AS $data_unseri_key => $data_unseri_value)
	                        {
	                        //$str .= "data_unseri_value ". $data_unseri_key . " - " . $data_unseri_value . "<br>"; 
	                        //$str .= "<pre>";var_dump($data_unseri_key); $str .= "</pre>";
	                        $str .= "<tr><td style=\"background: #eee;\">their_answer: ".$data_unseri[$data_unseri_key]["their_answer"]. " - correct: " .$data_unseri[$data_unseri_key]["correct"] ;
	                            if ($data_unseri[$data_unseri_key]["their_answer"] != $data_unseri[$data_unseri_key]["correct"])
	                                {
	                                $str .= " <span style=\"background:red; color:white;\">FALSCH. Also kopieren</span> ";
	                                $data_unseri[$data_unseri_key]["their_answer"] = $data_unseri[$data_unseri_key]["correct"];
	                                $str .= "their_answer: ".$data_unseri[$data_unseri_key]["their_answer"]. " - correct: " .$data_unseri[$data_unseri_key]["correct"];
	                                $str.= "<br>";

	                                /*
	                                $sql = "SELECT question_data_answers
	                                        FROM wp13_wpcw_quizzes_questions
	                                        WHERE question_id = ".$data_unseri_key;     

	                                        $str .= $sql;

	                                $tar_result = $wpdb->get_results($sql);
	                                $tar_result = $tar_result[0];
	                                //$tar_result = unserialize($tar_result[0]);
	                                $tar_result = $tar_result->question_data_answers;
	                                $tar_result = unserialize($tar_result);
	                                foreach ($tar_result AS $tar_result_key => $tar_result_value)   
	                                    {
	                                    $tar_result_value = $tar_result_value["answer"];
	                                    $tar_result_value = md5($tar_result_value);
	                                    echo "<pre>";var_dump ($tar_result_value);echo "</pre>";
	                                    }
	                                */
	                                $sql = "SELECT question_correct_answer
	                                        FROM wp13_wpcw_quizzes_questions
	                                        WHERE question_id = ".$data_unseri_key;     

	                                $qca_result = $wpdb->get_row($sql);
	                                //$qca_result = $qca_result[0];
	                                //$qca_result = unserialize($qca_result[0]);
	                                //$qca_result = $qca_result->question_data_answers;
	                                //$qca_result = unserialize($qca_result);
	                                //echo "<pre>";var_dump ($qca_result);echo "</pre>";
	                                /*
	                                foreach ($tar_result AS $qca_result_key => $qca_result_value)   
	                                    {
	                                    $qca_result_value = $qca_result_value["answer"];
	                                    $qca_result_value = md5($qca_result_value);
	                                    echo "<pre>";var_dump ($qca_result_value);echo "</pre>";
	                                    }
	                                */
	                                //$str .= $sql;
	                                $data_unseri[$data_unseri_key]["question_correct_answer"] = $qca_result->question_correct_answer;
	                                $str .=  "question_correct_answer: ".$data_unseri[$data_unseri_key]["question_correct_answer"]. " - correct: " .$qca_result->question_correct_answer;
	                                $str .="<br>";

	                                $data_unseri[$data_unseri_key]["got_right"] = "yes";
	                                $str .=  "got_right: ".$data_unseri[$data_unseri_key]["got_right"];

	                                $echo_reseri = 1;
	                                $data_reseri = serialize($data_unseri);

	                                // Hier jetzt Schreibvorgang in DB
                                    // Herkömmliches UPDATE:
                                    

	                                $update = "UPDATE wp13_wpcw_user_progress_quizzes SET 
	                                           quiz_data = '".$data_reseri."',
	                                           quiz_correct_questions = ".$result_value->quiz_question_total.", 
	                                           quiz_grade = 100.00 
	                                           WHERE quiz_id = ".$quiz_value->quiz_id."  
	                                           AND user_id = ".$result_value->user_id." 
	                                           AND quiz_attempt_id = ".$result_value->quiz_attempt_id;
                                    
                                    $update_action=0;
	                                if (($_POST['cleanup_now']=="yes") && ($_REQUEST['restore_backup']!=1))
                                		{
                                		//$strk .= "<br><tr><td>".$update."</td></tr>"; 

                                	    $wpdb->update( 
												'wp13_wpcw_user_progress_quizzes', 
												array( 
													'quiz_data' => $data_reseri,	
													'quiz_correct_questions' => $result_value->quiz_question_total,
													'quiz_grade' => '100.00'	
												), 
												array( 
													'quiz_id' => $quiz_value->quiz_id,
													'user_id' => $result_value->user_id,
													'quiz_attempt_id' => $result_value->quiz_attempt_id
												)
											);

                                        // quiz_completion_time_seconds = 0 ??? notwendig?
										$update_action=1;	
			                            }

	                                // Ende Schreibvorgang         
	                                }
	                            else
	                                {
	                                $str .= " <span style=\"background:green; color:white;\">RICHTIG</span>.";
	                                $echo_reseri = 0;
	                                $data_reseri = serialize($data_unseri);
	                                }   
	                            $str .= "</td></tr>";
	                        }

	                    // Ende Änderung 
                       if ($update_action==1) $strk .= " <span style=\"background: #f5c34c;\">OK, geändert</span>";
	                    }
	                else
	                    {
	                    $str .= "<tr><td style=\"background: #aca;\">Nicht bestanden - keine Änderung nötig. Braucht ".$quiz_value->quiz_pass_mark." % - Hat erreicht: ". $result_value->quiz_grade."%</td></tr>";
	                    $strk .= "<span style=\"background: #aca;\">Nicht bestanden - keine Änderung nötig</span>";
	                    }

	                $str .= "<tr><td> </td></tr>";
	                
	               //if ($echo_reseri==1) $str .= "<tr><td>".$data_reseri."</td></tr>";

	                $strk .= "<br>";
	                $str .= "<tr><td class=\"separator_attempt\">Ende User ".$result_value->user_id." Attempt ".$fez2."</td></tr>"; 
	                }
	            $str .= "</td></tr></table></td></tr>"; 
	            }
	        $str .= "<tr><td class=\"separator_quiz\">Ende Quiz ID ".$course_value."</td></tr><tr><td><br></td></tr>";  
	        }
	    $str .= "<tr><td class=\"separator_course\">Ende Course ".$course_value."</td></tr>"; // Ende Course        
	    $str .= "</table><br>";
	    
	    }

	 $strk .= "<input type=\"hidden\" id=\"cleanup_now\" name=\"cleanup_now\" value=\"yes\" />";
     $strk .= "<input type=\"checkbox\" id=\"restore_backup\" name=\"restore_backup\" value=\"1\" />Backup wiederherstellen (zum Testen)";
	 $strk .= "<input type=\"submit\" value=\"Jetzt bereinigen\" />";
     $strk .= "<input name=\"action\" value=\"insert\" type=\"hidden\" />";
     $strk .= "</form>";

	echo $strk."<br><br>";

	//echo $str; 

    }
?>
