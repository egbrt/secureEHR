import {Notes} from './notes.js';
import {getAPI, openDashboard} from './api.js';

var notes = new Notes();

export function showAppointments()
{
    $.ajax ({
        url: getAPI("getAppointments"),
        data: {
            date: $('#appointmentDate').val()
        },
        type: "POST",
        dataType: "json",
    })
    .done (function(json) {
        displayAppointments(json);
    })
}


export function setAppointment(patientId)
{
    $.ajax ({
        url: getAPI("addAppointment"),
        data: {
            patient: patientId,
            date: $('#appointmentDate').val(),
            starttime: $('#appointmentTime').val(),
            endtime: $('#appointmentTime').val()
        },
        type: "POST",
        dataType: "json",
    })
    .done (function(json) {
        displayAppointments(json);
    })
}


export function displayAppointments(json)
{
    var i = 0;
    var appointments = "";
    while (i < json.length) {
        appointments += "<p><span class=\"patientName\" id=\"" +json[i].patientId + "\">";
        appointments += json[i].starttime + " : " + json[i].patientLabel + "</span>";
        appointments += "<button class=\"gotoDashboard\" patient=\"" + json[i].patientId + "\">Dashboard</button>";
        appointments += "<button class=\"deleteAppointment\" id=\"" + json[i].id + "\">Done</button></p>";
        i++;
    }
    appointments += "";
    $('#patientsBooked').html(appointments);
    
    $('.gotoDashboard').click(function() {
        openDashboard($(this).attr('patient'));
    });
    $('.deleteAppointment').click(function() {
        deleteAppointment($(this).attr('id'));
    });
    
    $('.patientName').click(function() {
        $('.patientName').removeClass('selected');
        $(this).addClass('selected');
        notes.show(this.id);
    });
}


function deleteAppointment(id)
{
    $.ajax ({
        url: getAPI("deleteAppointment"),
        data: {
            appointment: id,
            date: $('#appointmentDate').val()
        },
        type: "POST",
        dataType: "json",
    })
    .done (function(json) {
        displayAppointments(json);
    })
}

