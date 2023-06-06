import {getAPI} from './api.js';

export class Notes {
    constructor()
    {
        let notes = this;
        this.id = 0;
        this.patientId = 0;
    }
    
    show(patientId)
    {
        let notes = this;
        this.id = 0;
        this.patientId = patientId;
        $('#patientsNotes').show(250);
        $('#patientsNotesInfo').val("");
        $('#patientsNotesSave').attr('disabled', true);
        $('#patientsNotesClear').attr('disabled', true);
        $.ajax ({
            url: getAPI("getNotes"),
                data: {
                    patient: patientId
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            if (json) {
                notes.id = json.statementId;
                $('#patientsNotesInfo').val(json.text);
                $('#patientsNotesClear').attr('disabled', false);
            }
        })
    }

    save()
    {
        if (this.patientId != 0) {
            let notes = this;
            var statements = Array();
            var numberOfStatement = 0;
            var statement = {kind:"Notes", label:""};
            statement.label = $("#patientsNotesInfo").val();
            statements[0] = statement;
            
            $.ajax ({
                url: getAPI("saveNotes"),
                    data: {
                        patient: notes.patientId,
                        notesId: notes.id,
                        statements: statements
                    },
                    type: "POST",
                    dataType: "json",
            })
            .done (function(json) {
                notes.id = json.statementId;
            })
        }
    }
    
    reset()
    {
        this.id = 0;
    }
    

}
