/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

global.$ = global.jQuery = require('jquery');
require('bootstrap');
require('./libs/navbar.js');
require('select2');

$(document).ready(function () {
    let presetField = document.getElementById('event_attendee_preset');
    if (presetField) {
        buildAttendanceForm(presetField.value);
        presetField.addEventListener('change', function () {
            buildAttendanceForm(presetField.value);
        })
    }
});

function buildAttendanceForm(value) {
    if (value !== '') {
        document.getElementById('event_attendee_class').parentElement.style.display = 'none';
        document.getElementById('event_attendee_role').parentElement.style.display = 'none';
        document.getElementById('event_attendee_sets').parentElement.style.display = 'none';
    } else {
        document.getElementById('event_attendee_class').parentElement.style.display = 'block';
        document.getElementById('event_attendee_role').parentElement.style.display = 'block';
        document.getElementById('event_attendee_sets').parentElement.style.display = 'block';
    }
}