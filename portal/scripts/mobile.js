import {Info} from "./info.js";

var info = new Info();

$(function() {
    if('serviceWorker' in navigator) {
        navigator.serviceWorker.register('worker.js');
    }

    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', function(e) {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        deferredPrompt = e;
        $('#installAsApp').show();
    });

    $('#installAsApp').click(function(e) {
        $('#installAsApp').hide();
        deferredPrompt.prompt();
    });
    
    $('#updateInfo').click(function() {
        info.update();
    });
    
    $('#clearInfo').click(function() {
        info.clear();
    });
    
    $('#showIntro').click(function() {
        $('#mainPage').hide();
        $('#introPage').show();
    });
    
    $('#closeIntro').click(function() {
        $('#introPage').hide();
        $('#mainPage').show();
    });
    
    $('#buttons ul li').click(function() {
        if (this.id == 'buttonPatient') {
            $('#buttonPatient').attr('selected',true);
            $('#buttonDiagnoses').attr('selected', false);
            $('#buttonMedication').attr('selected', false);
            $('#buttonLaboratory').attr('selected', false);
            $('#patient').show();
            $('#diagnoses').hide();
            $('#medication').hide();
            $('#laboratory').hide();
        }
        else if (this.id == 'buttonDiagnoses') {
            $('#buttonPatient').attr('selected',false);
            $('#buttonDiagnoses').attr('selected', true);
            $('#buttonMedication').attr('selected', false);
            $('#buttonLaboratory').attr('selected', false);
            $('#patient').hide();
            $('#diagnoses').show();
            $('#medication').hide();
            $('#laboratory').hide();
        }
        else if (this.id == 'buttonMedication') {
            $('#buttonPatient').attr('selected',false);
            $('#buttonDiagnoses').attr('selected', false);
            $('#buttonMedication').attr('selected', true);
            $('#buttonLaboratory').attr('selected', false);
            $('#patient').hide();
            $('#diagnoses').hide();
            $('#medication').show();
            $('#laboratory').hide();
        }
        else { // 'buttonLaboratory'
            $('#buttonPatient').attr('selected',false);
            $('#buttonDiagnoses').attr('selected', false);
            $('#buttonMedication').attr('selected', false);
            $('#buttonLaboratory').attr('selected', true);
            $('#patient').hide();
            $('#diagnoses').hide();
            $('#medication').hide();
            $('#laboratory').show();
        }
    });
})
