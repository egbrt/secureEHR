import {getAPI} from "./api.js";
import {MEDICATION} from "./visits.js";

export class Drugs {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
    }
    
    show(patientId)
    {
        let drugs = this;
        $.ajax ({
            url: getAPI("getPatientDrugs"),
                data: {
                    patient: patientId
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let listOfDrugs = drugs.collect(true, json) + "<hr/>" + drugs.collect(false, json);
            $("#visitPatientDrugs .contents").html(listOfDrugs);
            
            $("#visitPatientDrugs .contents ul li").click(function() {
                drugs.dashboard.episodes.select($(this).attr("episode"));
                if (drugs.dashboard.icpc3.getMode() == MEDICATION) {
                    let drug = $(this).html();
                    let i = drug.indexOf(' ');
                    if (i > 0) drug = drug.substr(i+1);
                    $("#visitAllFinding").val(drug);
                }
            });
        })
    }
    
    
    collect(active, json)
    {
        let i = 0;
        let episodeId = 0;
        let drugs = "<ul class=\"";
        if (active) {
            drugs += "activeEpisode";
        }
        else {
            drugs += "inActiveEpisode";
        }
        drugs += "\">";
        while (i < json.length) {
            if (json[i].active == active) {
                if (json[i].episodeId != episodeId) {
                    if (episodeId != 0) {
                        drugs += "<li><hr></li>";
                    }
                    episodeId = json[i].episodeId;
                    drugs += "<li class=\"episodeName\" episode=\"" + json[i].episodeId + "\">" + json[i].episodeName + "</li>";
                }
                drugs += "<li style=\"text-indent:10px\" episode=\"" + json[i].episodeId + "\">" + json[i].date + " " + json[i].text + "</li>";
            }
            i++;
        }
        drugs += "</ul>";
        return drugs;
    }
    
    
}

