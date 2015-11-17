<html>
<html>
<head>
<title></title>
<style type="text/css">

table, tr, td, body
{ 
font-family:verdana; font-size: 11px;
}

table, tr, td
{
background: #dddddd;
margin: 0em;
}	 

table.quiz,
tr.quiz,
td.quiz,
table.attempt,
tr.attempt,
td.attempt
{
margin: 2em;
border-collapse: collapse;
border: 1px dotted white;
}  

td.separator_course {background: #999999;}
td.separator_quiz {background: #ccf;}
td.separator_attempt {background: #cfc;}

</style>
</head>  

<body>


<?php

require_once( '../../../wp-config.php' );

include_once '../wp-courseware/lib/common.inc.php';
include_once '../wp-courseware/lib/constants.inc.php'; 
include_once '../wp-courseware/lib/email_defaults.inc.php';

if ( !defined('ABSPATH') )
   define('ABSPATH', dirname(__FILE__) . '/');


require_once( '../../../wp-includes/load.php' );
require_once( '../../../wp-includes/wp-db.php' );

$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

/*
$userID = 72;
$quizID = 9;
$courseID = 3;
$unitID = 680;
*/

/* check:

WPCW_quizzes_getAllQuizzesForCourse($courseID);
WPCW_quizzes_getQuizResultsForUser($userID, $quizIDListForSQL);
WPCW_quizzes_updateQuizResults($quizResultsSoFar)
WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizID)

*/

// $bla = WPCW_quizzes_getAllQuizzesForCourse($courseID); 
// geht

$str .= "<p>1.) Welche Courses gibt es?</p>";

$courses = $wpdb->get_col("
	    	SELECT * 
	    	FROM $wpcwdb->courses
	    	ORDER BY course_id;
	    ");

foreach ($courses As $course_key => $course_value)
	{
	$str .= "Course ". $course_value."<br>";
	}

//var_dump($courses);

$strk = "";
$str .= "<p>2.) Alle Courses durchgehe und seine Quizze holen</p>";


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

								$update = "UPDATE wp13_wpcw_user_progress_quizzes SET 
										   quiz_data = '".$data_reseri."',
										   quiz_correct_questions = ".$result_value->quiz_question_total.", 
										   quiz_grade = 100.00 
						    			   WHERE quiz_id = ".$quiz_value->quiz_id."  
						    			   AND user_id = ".$result_value->user_id." 
						    			   AND quiz_attempt_id = ".$result_value->quiz_attempt_id;

								$strk .= "<br><tr><td>".$update."</td></tr>"; 

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

					}
				else
					{
					$str .= "<tr><td style=\"background: #aca;\">Nicht bestanden - Änderung nötig-> Braucht ".$quiz_value->quiz_pass_mark." % - Hat erreicht: ". $result_value->quiz_grade."%</td></tr>";
					$strk .= "<span style=\"background: #aca;\">Nicht bestanden - keine Änderung nötig</span>";
					}

					
				


				$str .= "<tr><td> </td></tr>";
				
				
				if ($echo_reseri==1) $str .= "<tr><td>".$data_reseri."</td></tr>";

				// TODO: update wp13_wpcw_user_progress_quizzes where user_id = ... and quiz_id = ...
				// quiz_data = data_reseri
				// quiz_grade = '100.00'
				// quiz_completion_time_seconds = 0
				// quiz_correct_questions =	quiz_question_total	

				// ... aber nur die bestandenen! Die anderen nicht!

				$strk .= "<br>";
				$str .= "<tr><td class=\"separator_attempt\">Ende User ".$result_value->user_id." Attempt ".$fez2."</td></tr>";	
				}
			$str .= "</td></tr></table></td></tr>";	
			}
		$str .= "<tr><td class=\"separator_quiz\">Ende Quiz ID ".$course_value."</td></tr><tr><td><br></td></tr>";	
		}
	$str .= "<tr><td class=\"separator_course\">Ende Course ".$course_value."</td></tr>"; // Ende Course		
	$str .= "</table><br>";
	//$str .= "<br>------<br>";	
	}

//$quizzes = WPCW_quizzes_getAllQuizzesForCourse($courseID);
//$bla = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizID);

$strk = utf8_decode($strk);
echo $strk."<br><br>";

$str = utf8_decode($str);
echo $str; 
?>
</body>
</html>