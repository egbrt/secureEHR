import {getAPI} from './api.js';

export const RFE = "Reason";
export const SUBJECTIVE = "Subjective";
export const OBJECTIVE = "Objective";
export const ASSESSMENT = "Assessment";
export const PLAN = "Plan";
export const MEDICATION = "Medication";
export const LABORATORY = "Laboratory";
export const SURGERY = "Surgery";

export class Visits {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
    }
    
    show(episodeId)
    {
        if (episodeId == 0) {
            $('#visitEpisodeVisits').hide(250);
        }
        else {
            let visits = this;
            $.ajax ({
                url: getAPI("getVisits"),
                    data: {
                        episode: episodeId
                    },
                    type: "POST",
                    dataType: "json",
            })
            .done (function(json) {
                let i = 0;
                let kind = "";
                let visitId = 0;
                let listOfVisits = "";
                let onlyNotes = ((json.visits.length == 1) && (json.visits[0].name == "Notes"));
                while (i < json.visits.length) {
                    if (json.visits[i].id != visitId) {
                        if (visitId != 0) listOfVisits += "</div>";
                        listOfVisits += "<div style=\"display:flex;margin-top:8px\">";
                        listOfVisits += "<button class=\"toggleVisit\" visit=\"" + json.visits[i].id + "\">";
                        if (visitId != 0) {listOfVisits += "Show"} else {listOfVisits += "Hide"};
                        listOfVisits += "</button>";
                        listOfVisits += "<p id=\"" + json.visits[i].id + "\" class=\"visitDate\">";
                        listOfVisits += json.visits[i].date + "</p></div>";
                        listOfVisits += "<div id=\"visit_" + json.visits[i].id + "\"";
                        if (visitId != 0) listOfVisits += " style=\"display:none\"";
                        listOfVisits += ">";
                        visitId = json.visits[i].id;
                        kind = "";
                    }
                    if (json.visits[i].name != kind) {
                        listOfVisits += "<p class=\"historyHeading\">" + json.visits[i].name + "</p>";
                        kind = json.visits[i].name;
                    }
                    if (kind == "Notes") {
                        listOfVisits += "<p class=\"historyStatement\">";
                        let note = 0;
                        let notes = json.visits[i].text.split("\n");
                        notes.forEach(function(note) {
                            listOfVisits += "<span class=\"note\">" + note + "</span><br/>";
                        });
                        listOfVisits += "</p>";
                    }
                    else {
                        listOfVisits += "<p class=\"historyStatement\">" + json.visits[i].text + "</p>";
                    }
                    i++;
                }
                if (visitId != 0) listOfVisits += "</div>";
                $("#episodeName").html(json.episode.name);
                $('#visitEpisodeVisits .contents').html(listOfVisits);
                
                i = 0;
                while (i < json.length) {
                    if (json.visits[i].name == "Reason") {
                        $("p.visitDate").each(function() {
                            if (this.id == json.visits[i].id) {
                                let text = $(this).html();
                                if (text.length > 10) {
                                    text += ", "
                                }
                                else {
                                    text += " : ";
                                }
                                text += json.visits[i].text;                            
                                $(this).html(text);
                            }
                        });
                    }
                    i++;
                }
                
                $('#visitEpisodeVisits').show(250);
                $(".toggleVisit").click(function() {
                    let list = '#visit_' + $(this).attr('visit');
                    if ($(list).is(":visible")) {
                        $(this).html("Show");
                    }
                    else {
                        $(this).html("Hide");
                    }
                    $(list).toggle();
                });
                
                $('#visitEpisodeVisits p.visitDate').click(function() {
                    if (event.ctrlKey) {
                        let clickedVisit = $(this).html();
                        let clickedVisitId = $(this).attr('id');
                        let selected = $('#visitEpisodeVisits p.selected');
                        if (selected.length > 0) {
                            visits.askQuestion(clickedVisit);
                            let timer = setTimeout(function(){
                                $('#question').hide();
                            }, 8000); // close after 5 seconds
                            $('#questionYes').click(function() {
                                $('#question').hide();
                                visits.merge(clickedVisitId);
                                clearTimeout(timer);
                            });
                            $('#questionNo').click(function() {
                                $('#question').hide();
                                clearTimeout(timer);
                            });
                        }
                    }
                    else {
                        $(this).toggleClass('selected');
                    }
                });
                
                $('#visitEpisodeVisits p span').click(function() {
                    let note = $(this).html();
                    if (note.startsWith("R ")) {
                        contact.moveTo(RFE);
                        note = note.substr(2);
                    }
                    else if (note.startsWith("S ")) {
                        contact.moveTo(SUBJECTIVE);
                        note = note.substr(2);
                    }
                    else if (note.startsWith("O ")) {
                        contact.moveTo(OBJECTIVE);
                        note = note.substr(2);
                    }
                    else if (note.startsWith("A ")) {
                        contact.moveTo(ASSESSMENT);
                        note = note.substr(2);
                    }
                    else if (note.startsWith("P ")) {
                        contact.moveTo(PLAN);
                        note = note.substr(2);
                    }
                    $('#visitAllFinding').val(note).keyup();
                });
            })
        }
    }
    
    move(patientId, toEpisode)
    {
        let visits = this;
        let i = 0;
        let listOfVisits = new Array();
        $('#visitEpisodeVisits p.selected').each(function() {
            listOfVisits[i] = $(this).attr('id');
            i++;
        });
        $.ajax ({
            url: getAPI("moveVisits"),
                data: {
                    patient: patientId,
                episode: toEpisode,
                visits: listOfVisits
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let patientId = $('#visitPatientId').attr('patientId');
            visits.dashboard.episodes.show(patientId, json.id);
        });
    }
    
    
    askQuestion(visitText)
    {
        let question = "Merge the information in the selected contacts into one?";
        
        question += "<div id=\"questionButtons\">";
        question += "<button id=\"questionYes\"";
        question += ">Yes</button> ";
        question += "<button id=\"questionNo\">Cancel</button>";
        question += "<br/><span id=\"questionInfo\">If you don't answer, this window will automatically disappear.</span></div>";
        $('#question').html(question);
        $('#question').show();
    }
    
    
    merge(toVisit)
    {
        let visits = this;
        let i = 0;
        let listOfVisits = new Array();
        $('#visitEpisodeVisits p.selected').each(function() {
            listOfVisits[i] = $(this).attr('id');
            i++;
        });
        $.ajax ({
            url: getAPI("mergeVisits"),
                data: {
                    visit: toVisit,
                visits: listOfVisits
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            visits.show($('#visitEpisodes ul li.selected').attr('id'));
        });
    }
    
    
    
    
}
