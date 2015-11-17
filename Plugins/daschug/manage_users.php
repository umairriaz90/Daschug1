<?php
/**
 * Add additional custom fields
 */
add_action('show_user_profile', 'new_fields');
add_action('edit_user_profile', 'new_fields');

add_action('show_user_profile', 'new_cw_courses'); // Wenn ich mein eigenes Profil betrachte
add_action('edit_user_profile', 'new_cw_courses'); // Wenn ich anderer Leute Profil betrachte

add_action('user_new_form', 'new_fields');
add_action('user_new_form', 'new_cw_courses');

//
function new_cw_courses ( $user_id ) {
    global $table_prefix;
    global $wpdb;
    global $user_id;

// User muß Admin sein, sonst wird die Funktion nicht ausgeführt
// GW 150730
if (!current_user_can('manage_options')) 
    return false;

$output = "<h3>CourseWare-Zugriffsberechtigungen updaten </h3>";
$query   = "SELECT 
            c.course_id AS course_id,
            c.course_title AS course_title,
            c.course_desc AS course_desc ";
if (isset($user_id)) 
    $query .= ", uc.user_id AS user_id ";

$query .= "FROM ".$table_prefix."wpcw_courses c ";
if (isset($user_id)) 
    {
    $query .=  "LEFT JOIN ".$table_prefix."wpcw_user_courses uc 
                ON c.course_id = uc.course_id 
                AND uc.user_id = ".$user_id." ";      
    } 
$query .= " ORDER BY c.course_title ASC"; 

$cw_courses =  $wpdb->get_results($query, ARRAY_A);

//var_dump ($cw_courses);
$output .= "<table class=\"form-table\">";

foreach ($cw_courses as $key => $value) 
    {
    $output .= "<tr>";
        
    $output .= "<td>";
    $output .= "<input type=\"checkbox\" name=\"wpcw_course_".$value['course_id']."\" value=\"".$value['course_id']."\"";
    if (isset($value['user_id'])) $output .= " checked=checked";
    $output .= ">";
    
    $output .= "</td>";

    $output .= "<td colspan=6>";
    $output .= $value['course_title'];
    $output .= "</td>";

    $output .= "<td>";
    $output .= $value['course_desc'];
    $output .= "</td>";

    $output .= "</tr>";      
    // $output .= $value['course_id']."-".$value['course_title']."<br>";
    // var_dump($value);
    }
 
$output .= "</table>"; 
echo $output;
 

}

function new_fields( $user_id ) {

    // User muß Admin sein, sonst wird die Funktion nicht ausgeführt
    // GW 150730
    if (!current_user_can('manage_options')) 
    return false;

    $editAllowed = current_user_can('manage_options');
    
    $fields = array(USER_GENDER => 'datr_gender', USER_TITLE => 'datr_title', USER_MANDANT => 'datr_mandantID', USER_DEPARTMENT => 'datr_department');
    $output = '<h3>'.USER_PROFILE_EXTRA_FIELDS.'</h3>
    <table class="form-table">';
    $output .= "<tr>
        <th>".LANGUAGE."</th>
            <td>".View::outputOptions("datr_language", DaschugLocalisation::$languages, get_user_meta($user_id->ID, 'datr_plugin_language', true))
            ."</td></tr>";
    foreach ($fields as $key => $value) {
        //View::userFormFieldsOutput($value, get_user_meta($user_id->ID, $value, true), $editAllowed);
        $output .= "<tr>
            <th>$key</th>
            <td>"
                .View::userFormFieldsOutput($value, get_user_meta($user_id->ID, $value, true), $editAllowed).
            "</td>
        </tr>";
    }
    $userLocations = EventDatabaseManager::getUserLocations($user_id->ID);
    $locations = EventDatabaseManager::getMandantLocations(get_user_meta($user_id->ID, 'datr_mandantID', true));
    $output .= View::outputUserLocations($locations, $userLocations, $editAllowed);
    $output .= '</table>';

    $output .= '<h3>'.USER_PROFILE_YOUR_ASSIGNMENTS.'</h3>
    <table class="form-table">';
    $output .= '<tr><td></td><td>'.ASSIGNMENTS_TITLE.'</td>';
    
    foreach (EventDatabaseManager::$userHasTopicsParams as $title => $key) {
        if ( ($key=='repetition_frequency_days') OR ($key=='topic_expiry_date') ) // GW 140611
            $output .= "<td>".$title."</td>";
    }
    $output .= '</tr>';
    
    echo $output;
    
    $topics = EventDatabaseManager::getAllTopics();
    foreach ($topics as $id => $title) {
        View::userHasTopicForm($id, $title, EventDatabaseManager::getAssignedTopicParams($user_id->ID, $id), $editAllowed);
    }
    echo "</table>";
    echo "<script src=\"/wp-content/plugins/daschug/check_user_meta.js\" type=\"text/javascript\"></script>";


}
//
add_action('personal_options_update', 'save_new_fields'); // only for Users on their own profile
add_action('edit_user_profile_update', 'save_new_fields'); // only for Users on somebody else's profile
add_action('edit_user_profile_update', 'save_new_cw_fields');
add_action('user_register', 'save_new_fields');
add_action('user_register', 'save_new_cw_fields');

    

function save_new_cw_fields ($user_id) {
	global $table_prefix;
    global $wpdb;
    //global $user_id;

    if (!current_user_can('edit_user', $user_id))
        return false;
    if(isset($user_id))
        {
        $query = "DELETE FROM ".$table_prefix."wpcw_user_courses 
                  WHERE user_id = ".$user_id;
        $wpdb->query($wpdb->prepare($query));
        // echo $query;
        }   

    $query="";
    foreach($_POST as $key => $value) 
        {
          
        $query="";
        $pos = strpos($key , "wpcw_course_");
        if ($pos === 0)
            {
            $query = "INSERT INTO ".$table_prefix."wpcw_user_courses (
            user_id,
            course_id,
            course_progress,
            course_final_grade_sent
            ) VALUES (
            ".$user_id.",
            ".$value.",
            0,
            ''
            )";
            $wpdb->query($wpdb->prepare($query));    
            }
          
            //echo $query;
            
           
        }
        $user = get_userdata($user_id);
        // Generate something random for a password reset key.
        	$key = wp_generate_password( 20, false );

        	/** This action is documented in wp-login.php */
        	do_action( 'retrieve_password_key', $user->user_login, $key );

        	// Now insert the key, hashed, into the DB.
        	if ( empty( $wp_hasher ) ) {
        		require_once ABSPATH . WPINC . '/class-phpass.php';
        		$wp_hasher = new PasswordHash( 8, true );
        	}
        	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
        	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
        $message = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
        	$message .= __('Bitte klicken folgen sie diesem Link, um Ihr Passwort einstellen:') . "\r\n\r\n";
        	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";

        	$message .= wp_login_url() . "\r\n\r\n";
            $message .= sprintf( __('Sollten Sie noch weitere Fragen haben, kontaktieren Sie uns bitte unter  %s.'), '<a href="mailto:info@daschung.de">info@daschung.de</a>' ) . "\r\n\r\n";
        	

        	wp_mail($user->user_email, sprintf(__('[%s] Ihr Benutzername und Passwort'), $blogname), $message);
//die();
}




function save_new_fields($user_id) {
    if (!current_user_can('edit_user', $user_id))
        return false;
    
    update_user_meta($user_id, 'datr_plugin_language', $_POST['datr_language']);
    
    if (!current_user_can('manage_options'))
        return false;
    
    $topics = EventDatabaseManager::getAllTopics();
    foreach ($topics as $id => $title) {
        if (isset($_POST['user_has_topic_'.$id])) {
            $topicParams = array();
            foreach(EventDatabaseManager::$userHasTopicsParams as $param) {
                $topicParams[$param] = $_POST[$param.'_'.$id];
            }
            EventDatabaseManager::addOrUpdateAssignedTopic($user_id, $id, $topicParams);
        }
        else {
            EventDatabaseManager::deleteAssignedTopic($user_id, $id);
        }
    }
    $locations = EventDatabaseManager::getMandantLocations(get_user_meta($user_id, 'datr_mandantID', true));
    foreach ($locations as $id=>$locationName) {
        if (isset($_POST['location_'.$id])) {
            EventDatabaseManager::addLocationToUser($user_id, $id);
        }
        else {
            EventDatabaseManager::removeLocationFromUser($user_id, $id);
        }
    }
    
    update_user_meta($user_id, 'datr_gender', $_POST['datr_gender']);
    update_user_meta($user_id, 'datr_title', $_POST['datr_title']);
    update_user_meta($user_id, 'datr_mandantID', $_POST['datr_mandantID']);
    update_user_meta($user_id, 'datr_department', $_POST['datr_department']);
}

?>
