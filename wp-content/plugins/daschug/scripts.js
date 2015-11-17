function validateEventForm()
{
form = document.forms['event'];

if (form['event_action'].value == 'load_templates') {
	return form['topicID'] != null;
}


result = notNull(form['title_long'].value) 
        && notNull(form['title_short'].value) 
        && notNull(form['mandantID'].value);

       if (form['eventType'].value == 'elearning') {
       	result = result && notNull(form['year'].value) && notNull(form['month'].value) && notNull(form['day'].value) && notNull(form['hour'].value);
       };
return result;
}

function notNull(attributeName) {
    return (attributeName != null) && (attributeName != '') && (attributeName != 'none');
}

function validate() {
    if (validateEventForm())
        document.getElementById('eventButton').disabled = false;
    else {
        document.getElementById('eventButton').disabled = true;
        }
}

function eventFormSubmit() {
    form = document.forms['event'];
    var deleteButton = document.getElementById('deleteEvent');
    if (deleteButton == null || !deleteButton.checked) {
        form.submit();
    }
    else {
        var retVal = confirm('" . EVENT_DELETE_WARNING . "');
           if( retVal == true ){
                  form.submit();
           }else{
                  return false;
           }
    }
}

function mandantFormSubmit() {
    form = document.forms['mandant'];
    var deleteButton = document.getElementById('deleteMandant');
    if (deleteButton == null || !deleteButton.checked) {
        form.submit();
    }
    else {
        var retVal = confirm('" . MANDANT_DELETE_WARNING . "');
           if( retVal == true ){
                  form.submit();
           }else{
                  return false;
           }
    }
}

function topicFormSubmit() {
    form = document.forms['topic'];
    var deleteButton = document.getElementById('deleteTopic');
    if (deleteButton == null || !deleteButton.checked) {
        form.submit();
    }
    else {
        var retVal = confirm('" . TOPIC_DELETE_WARNING . "');
           if( retVal == true ){
                  form.submit();
           }else{
                  return false;
           }
    }
}

function locationFormSubmit() {
    form = document.forms['location'];
    var deleteButton = document.getElementById('deleteLocation');
    if (deleteButton == null || !deleteButton.checked) {
        form.submit();
    }
    else {
        var retVal = confirm('" . LOCATION_DELETE_WARNING . "');
           if( retVal == true ){
                  form.submit();
           }else{
                  return false;
           }
    }
}

aler ("hello!");