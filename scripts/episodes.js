import {getAPI} from './api.js';

export class Episodes {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
        this.patient = 0;
        this.current = 0;
        this.active = false;
        this.timer = null;

        let episodes = this;
        $(document).mousemove(function(e) {
            episodes.cursorX = e.pageX-20;
            episodes.cursorY = e.pageY-80;
        });

        $("#episodesMenu ul li").click(function() {
            clearTimeout(episodes.timer);
            $("#episodesMenu").hide(250);
            switch ($(this).attr("value")) {
                case "visits":
                    episodes.dashboard.visits.show(episodes.current);
                    break;
                case "toggle":
                    episodes.toggleActivity();
                    break;
            }
        });
    }
    
    select(id)
    {
        let current = $("#visitEpisodes .contents ul li.selected");
        if ($(current).attr("id") != id) {
            current.removeClass('selected');
            $("#visitEpisodes .contents ul li").each(function() {
                if (this.id == id) {
                    $(this).addClass('selected');
                }
            });
            if ($('#visitEpisodeVisits').is(':visible')) this.dashboard.visits.show(id);
            this.dashboard.select(id);
        }    
    }
    
    show(patientId, episodeId)
    {
        this.patient = patientId;
        this.current = episodeId;
        this.showAll()
    }
        
    showAll()
    {
        let episodes = this;
        $.ajax ({
            url: getAPI("getPatientEpisodes"),
                data: {
                    patient: episodes.patient
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let listOfEpisodes = "<ul><li id=\"0\">Create a new episode</li></ul><hr/><ul>";
            if ((!episodes.current) || (episodes.current == 0)) {
                if ((json.length > 0) && (json[0].name == "Notes")) {
                    episodes.current = json[0].id;
                }
                else {
                    episodes.current = 0;
                }
            }
            // first active episodes
            let i = 0;
            while (i < json.length) {
                if (json[i].active) {
                    listOfEpisodes += "<li id=\"" + json[i].id + "\" class=\"activeEpisode\">";
                    listOfEpisodes += json[i].enddate + " " + json[i].name + "</li>";
                }
                i++;
            }
            listOfEpisodes += "</ul><hr/><ul>";
            
            // then inactive episodes
            i = 0;
            while (i < json.length) {
                if (!json[i].active) {
                    listOfEpisodes += "<li id=\"" + json[i].id + "\" class=\"inActiveEpisode\">";
                    listOfEpisodes += json[i].enddate + " " + json[i].name + "</li>";
                }
                i++;
            }
            listOfEpisodes += "</ul>";
            
            $('#visitEpisodes .contents').html(listOfEpisodes);
            $('#visitEpisodes .contents ul li').each(function() {
                if (this.id == episodes.current) {
                    $(this).addClass("selected");
                }
            })
            if ((episodes.current > 0) && ($('#visitEpisodeVisits').is(':visible'))) {
                episodes.dashboard.visits.show(episodes.current);
            }
            
            $("#visitEpisodes .contents ul li").click(function(event) {
                episodes.current = $(this).attr('id');
                episodes.active = $(this).hasClass("activeEpisode");
                let selectedEpisodeId = $('#visitEpisodes ul li.selected').attr('id');
                if (event.ctrlKey) {
                    if ((selectedEpisodeId > 0) && (episodes.current != selectedEpisodeId)) {
                        episodes.askQuestion(episodes.current, $(this).html());
                        let timer = setTimeout(function(){$('#question').hide();}, 8000); // close after 5 seconds
                        $('#questionYes').click(function() {
                            $('#question').hide();
                            episodes.merge(episodes.current, selectedEpisodeId);
                            clearTimeout(timer);
                        });
                        $('#questionSome').click(function() {
                            $('#question').hide();
                            moveVisits(patientId, episodes.current);
                        });
                        $('#questionNo').click(function() {
                            $('#question').hide();
                            clearTimeout(timer);
                        });
                    }
                }
                else {
                    $('#visitEpisodes ul li.selected').removeClass('selected');
                    $('#visitEpisodes ul li.sameEpisode').removeClass('sameEpisode');
                    $('#warningExistingEpisode').hide(250);
                    $(this).addClass('selected');
                    if (episodes.current == 0) {
                        episodes.dashboard.contact.currentEpisode = "";
                    }
                    else {
                        episodes.dashboard.contact.currentEpisode = $(this).html();
                    }
                    episodes.dashboard.select(episodes.current);
                    episodes.showMenu();
                }
            });
        })
    }
    
    showMenu()
    {
        let episodes = this;
        if (this.timer) clearTimeout(this.timer);
        let menu = $("#episodesMenu");
        menu.css("left", this.cursorX);
        menu.css("top", this.cursorY);
        $("#episodesMenu ul li").each(function() {
            switch ($(this).attr("value")) {
                case "toggle":
                    if (episodes.active) {
                        $(this).html("Close");
                    }
                    else {
                        $(this).html("Re-Open");
                    }
                    break;
            }
        });
        
        menu.show(250);
        this.timer = setTimeout(function(){menu.hide();}, 8000); // close after 8 seconds
    }
    
    
    toggleActivity()
    {
        let episodes = this;
        $.ajax ({
            url: getAPI("toggleEpisodeActive"),
                data: {episode: episodes.current},
                type: "GET",
                dataType: "json"
        })
        .done (function(json) {
            episodes.dashboard.show(episodes.patient, episodes.current);
        });
    }
    
    
    askQuestion(episodeId, episodeText)
    {
        let question = "Move ";
        let selected = $('#visitEpisodeVisits p.selected');
        if (episodeId == 0) question = "Create a new episode and move selected ";
        question += "contacts from episode:<br/>";
        question += "&nbsp;&nbsp;" + $('#visitEpisodes ul li.selected').html() + "<br/>to ";
        if (episodeId == 0)  {
            question += "the new episode?";
            if ((!selected) || (selected.length == 0)) question += "<br/><br/>First select the contacts that you want to move and try again.";
        }
        else {
            question += "episode:<br/>";
            question += "&nbsp;&nbsp;" + episodeText + "?";
        }
        
        question += "<div id=\"questionButtons\">";
        question += "<button id=\"questionYes\"";
        if (episodeId == 0) question += " disabled=disabled";
        question += ">All contacts</button> ";
        question += "<button id=\"questionSome\"";
        if ((!selected) || (selected.length == 0))  question += " disabled=disabled";
        question += ">Selected contacts</button> ";
        question += "<button id=\"questionNo\">Cancel</button>";
        question += "<br/><span id=\"questionInfo\">If you don't answer, this window will automatically disappear.</span></div>";
        $('#question').html(question);
        $('#question').show();
    }
    
    
    merge(mainEpisode, subEpisode)
    {
        let episodes = this;
        $.ajax ({
            url: getAPI("mergeEpisodes"),
                data: {
                    main: mainEpisode,
                sub: subEpisode
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let patientId = $('#visitPatientId').attr('patientId');
            episodes.show(patientId, mainEpisode);
            episodes.dashboard.notes.show(patientId);
        });
    }
    
    
    
    
}
