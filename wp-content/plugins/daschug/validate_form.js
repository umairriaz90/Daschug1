function validateForm()
{
form = document.forms['event'];
result = notNull(form['title_long'].value) 
        && notNull(form['title_lshort'].value) 
        && notNull(form['mandantID'].value);
if (form['eventType'].value !== 'elearning')
    result = result && notNull(form['max_participants'].value) && notNull(form['date_time'].value);
return result;
}

function notNull(attributeName) {
    return (attributeName !== null) && (attributeName !== "");
}

function validate() {
    if (validateForm)
        alert('correct!');
    else
        alert('not correct!');
}

