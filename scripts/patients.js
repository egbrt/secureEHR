import {getAPI, openDashboard} from './api.js';
import {Dashboard} from "./dashboard.js";
import {showAppointments, setAppointment, displayAppointments} from './calendar.js';


var idCurrentPatient = 0;
const PatientSummary = "Personal Information\n\n\nContact Information\n\n\nInsurance Number\n\n\nAllergies\n\n\nMedical Alerts\n\n\nVaccination\n";

export function Patients(dashboard)
{
    $('#patientsDetailsVisit').attr('disabled', true);
    $('#patientsNotes').hide();
    $('#patientsAppointments').hide();

    $('#patientsDetailsLabel').keyup(function(e) {
        if (e.key == "Enter") {
            if (idCurrentPatient == 0) {
                searchPatients(dashboard);
                checkButtons();
            }
            else {
                openDashboard(idCurrentPatient);
            }
        }
        else {
            idCurrentPatient = 0;
            $('#patientsDetailsEmail').val('');
            $('#patientsDetailsInfo').val('');
        }
    });

    $('#patientsDetailsEmail').focus(function() {
        if (idCurrentPatient == 0) {
            searchPatients();
            checkButtons();
        }
    });
    
    $('#patientsDetailsEmail').keyup(function() {
        checkButtons();
    });

    $('#patientsDetailsInfo').keyup(function() {
        if (($(this).val() == "") && (checkButtons())) {
            $(this).val(PatientSummary);
            $(this)[0].selectionStart = $(this)[0].selectionEnd = 21; // check first line of PatientSummary
        }
        else {
            $('#patientsDetailsChange').attr('disabled', (idCurrentPatient == 0));
        }
    });
    
    $('#patientsDetailsChange').click(function() {
        savePatientInfo(dashboard, idCurrentPatient);
    });
    
    $('#patientsDetailsAdd').click(function() {
        savePatientInfo(dashboard, 0);
    });
    
    $('#patientsDetailsVisit').click(function() {
        openDashboard(idCurrentPatient);
    });
    

    $('#patientsNotesInfo').click(function() {
        if ($(this).val() == "") {
            $(this).val("R \nS\nO\nA\nP");
            $(this)[0].selectionStart = $(this)[0].selectionEnd = 2;
        }
    });
    $('#patientsNotesInfo').keyup(function() {
        $('#patientsNotesSave').attr('disabled', false);
        $('#patientsNotesClear').attr('disabled', false);
    });
    $('#patientsNotesSave').click(function() {
        dashboard.notes.save();
        $("#patientsNotesSave").attr("disabled", true);
        let patientId = $('#visitPatientId').attr('patientId');
        if (patientId) {
            visitPatientEpisodes(patientId);
        }
    });
    $('#patientsNotesClear').click(function() {
        dashboard.notes.reset();
        $('#patientsNotesInfo').val("R \nS\nO\nA\nP");
        $('#patientsNotesInfo')[0].selectionStart = $('#patientsNotesInfo')[0].selectionEnd = 2;
        $("#patientsNotesSave").attr("disabled", true);
        $("#patientsNotesClear").attr("disabled", true);
    });
    

    $('#appointmentSet').click(function() {
        setAppointment(idCurrentPatient);
    });
    
    $('#appointmentDate').change(function() {
        showAppointments();
    });
    
    $('#closeVisits').click(function() {
        $('#visitEpisodeVisits').hide(250);
    });
    
    
    dashboard.refresh(0);
    showAppointments();
}


function checkButtons()
{
    let label = $('#patientsDetailsLabel').val();
    let email = $('#patientsDetailsEmail').val();
    let newPatient = false;
    if (idCurrentPatient == 0) {
        let atSign = email.indexOf('@')
        let colon = label.indexOf(':');
        if ((atSign > 0) && (colon > 7)) {
            let emailDomain = email.indexOf('.', atSign);
            if ((emailDomain > 0) && (email.length > emailDomain+1) && (label.length > colon+8)) {
                newPatient = true;
            }
        }
    }
    $('#patientsDetailsAdd').attr('disabled', !newPatient);
    $('#patientsDetailsChange').attr('disabled', (idCurrentPatient == 0));
    return newPatient;
}


function savePatientInfo(dashboard, patientId)
{
    $.ajax ({
        url: getAPI("setPatientInfo"),
        data: {
            patient: patientId,
            label: $('#patientsDetailsLabel').val(),
            email: $('#patientsDetailsEmail').val(),
            info: $('#patientsDetailsInfo').val()
        },
        type: "POST",
        dataType: "json",
    })
    .done (function(json) {
        if (json.id) {
            idCurrentPatient = json.id;
            $('#patientsDetailsAdd').attr('disabled', true);
            $('#patientsDetailsChange').attr('disabled', true);
            //$('#patientsDetailsLabel').val(json.label); donot change
            $('#patientsDetailsEmail').val(json.email);
            $('#patientsDetailsInfo').val(json.info);
            $('#patientsDetailsVisit').attr('disabled', false);
            dashboard.notes.show(idCurrentPatient);
        }
    })
}


function searchPatients(dashboard)
{
    idCurrentPatient = 0;
    $('#patientsDetailsEmail').val('');
    $('#patientsDetailsInfo').val('');
    $('#patientsDetailsVisit').attr('disabled', true);
    $('#patientsNotes').hide(250);
    $('#patientsAppointments').hide(250);
    
    $.ajax ({
        url: getAPI("searchPatients"),
        data: {
            label: $('#patientsDetailsLabel').val()
        },
        type: "POST",
        dataType: "json",
    })
    .done (function(json) {
        if (json.id) {
            idCurrentPatient = json.id;
            $('#patientsDetailsEmail').val(json.email);
            $('#patientsDetailsInfo').val(json.info);
            dashboard.notes.show(idCurrentPatient);
            $('#patientsAppointments').show(250);
            $('#patientsDetailsVisit').attr('disabled', false);
        }
    })
}


