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
import apiclient from "./libs/apiclient";

document.addEventListener("DOMContentLoaded", function () {
    let presetField = document.getElementById('event_attendee_preset');
    if (presetField) {
        buildAttendanceForm(presetField.value);
        presetField.addEventListener('change', function () {
            buildAttendanceForm(presetField.value);
        })
    }
    let checkboxElems = document.getElementsByClassName('guild-calendar-checkbox');
    for (var i = 0; i < checkboxElems.length; i++) {
        checkboxElems[i].addEventListener("click", function () {
            updateCalendarSettings(this.checked, this.dataset.guild);
        });
    }
    let colourElems = document.getElementsByClassName('guild-calendar-colour');
    for (var j = 0; j < colourElems.length; j++) {
        colourElems[j].addEventListener("change", function () {
            updateColourSettings(this.value, this.dataset.guild);
        });
    }
    let characterNotes = document.getElementsByClassName('character-preset-notes-button');
    for (var k = 0; k < characterNotes.length; k++) {
        characterNotes[k].addEventListener("click", function () {
            changeCharacterNotesVisibility(this.dataset.user);
        });
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

async function updateCalendarSettings(value, guildId) {
    const response = await apiclient.get('/user/guilds/'+guildId+'/calendarvisibility?show=' + (value ? '1' : '0'));
}

async function updateColourSettings(value, guildId) {
    const response = await apiclient.get('/user/guilds/'+guildId+'/calendarcolour?colour=' + encodeURI(value.replace('#', '')));
}
function changeCharacterNotesVisibility(userId) {
    let elem = document.getElementById('notes-'+userId);
    if (elem.style.display === 'table-row') {
        elem.style.display = 'none';
    } else {
        elem.style.display = 'table-row';
    }
}
