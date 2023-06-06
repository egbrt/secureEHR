import {getAPI} from './api.js';

export class Laboratory {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
    }
    
    show(patientId)
    {
        let laboratory = this;
        $.ajax ({
            url: getAPI("getPatientLaboratory"),
                data: {
                    patient: patientId
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let i = 0;
            let labList = "<ul>";
            while (i < json.length) {
                labList += "<li class=\"episodeName\" episode=\"" + json[i].episodeId + "\">" + json[i].date + " " + json[i].text + "</li>";
                i++;
            }
            labList += "</ul>";
            $('#visitPatientLaboratory .contents').html(labList);
            
            $('#visitPatientLaboratory .contents ul li').click(function() {
                laboratory.dashboard.episodes.select($(this).attr("episode"));
            });
        })
    }
    
    
    
}

