var str = "Vorher: \n\n";

datr_language = document.getElementsByName("datr_language")[0].value;
str += "Sprache: " +datr_language + "\n";

datr_mandantID = document.getElementsByName("datr_mandantID")[0].value;
str += "Mandant: " +datr_mandantID + "\n";

location_elearning = document.getElementsByName("location_8")[0].checked;
str += "eLearning: " +location_elearning + "\n";

user_has_topic_1 = document.getElementsByName("user_has_topic_1")[0].checked;
str += "Topic Datenschutz: " +user_has_topic_1 + "\n";

if (!document.getElementsByName("datr_mandantID")[0].value)
	{

	document.getElementsByName("datr_language")[0].value = "de_DE";
	document.getElementsByName("datr_mandantID")[0].value = 1;
	document.getElementsByName("location_8")[0].checked = true;
	document.getElementsByName("user_has_topic_1")[0].checked = true;

	str += "\n\nNachher: \n\n";

	datr_language = document.getElementsByName("datr_language")[0].value;
	str += "Sprache: " +datr_language + "\n";

	datr_mandantID = document.getElementsByName("datr_mandantID")[0].value;
	str += "Mandant: " +datr_mandantID + "\n";

	location_elearning = document.getElementsByName("location_8")[0].checked;
	str += "eLearning: " +location_elearning + "\n";

	user_has_topic_1 = document.getElementsByName("user_has_topic_1")[0].checked;
	str += "Topic Datenschutz: " +user_has_topic_1 + "\n";
	}

// alert(str);