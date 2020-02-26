import {Tooltip} from "bootstrap";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import bootstrapPlugin from '@fullcalendar/bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    let calendarEl = document.getElementById('calendar-holder');
    let calendarPropsEl = document.getElementById('calendar-props');

    let calendar = new Calendar(calendarEl, {
        defaultView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        firstDay: parseInt(calendarPropsEl.getAttribute('data-first-day-of-week')),
        contentHeight: 'auto',
        displayEventTime: false,
        eventSources: [
            {
                url: "/fc-load-events",
                method: "POST",
                extraParams: {
                    filters: JSON.stringify({})
                },
                failure: () => {
                    // alert("There was an error while fetching FullCalendar!");
                },
            },
        ],
        header: {
            left: 'prev,next',
            center: 'title',
            right: '',
        },
        plugins: [ bootstrapPlugin, dayGridPlugin ], // https://fullcalendar.io/docs/plugin-index
        timeZone: calendarPropsEl.getAttribute('timezone'),
        eventRender: function(info) {
            var tooltip = new Tooltip(info.el, {
                title: '<strong>Guild: </strong>'+info.event.extendedProps.guild+'<br>'
                    +info.event.extendedProps['start-time']
                    +(info.event.extendedProps['end-time'] !== undefined ? ' - '+info.event.extendedProps['end-time'] : '')+'<br>'
                    +info.event.extendedProps.attending
                    +' people attending',
                placement: 'top',
                trigger: 'hover',
                container: 'body',
                html: true
            });
        }
    });
    calendar.render();
});