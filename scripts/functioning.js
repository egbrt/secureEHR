import {getAPI} from './api.js';

export class Functioning {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
        this.currentEpisode = 0;
        this.currentStatement = 0;
        this.currentStatementMark = null;
        this.timer = null;
        
        let functioning = this;
        $(document).mousemove(function(e) {
            functioning.cursorX = e.pageX-20;
            functioning.cursorY = e.pageY-80;
        });

        $("#functioningImportance ul li").click(function() {
            clearTimeout(functioning.timer);
            $("#functioningImportance").hide(250);
            functioning.setImportance($(this).attr("value"));
        });
    }
    
    setImportance(value)
    {
        let functioning = this;
        $.ajax ({
            url: getAPI("setImportance"),
            data: {
                    statement: functioning.currentStatement,
                    importance: value
                  },
            type: "POST",
            dataType: "json",
        })
        .done (function(json) {
            let mark = "";
            let value = json.importance;
            while (value > 0) {
                mark += "*";
                value--;
            }
            functioning.currentStatementMark.html(mark);
        });
    }
    
    show(patientId)
    {
        let functioning = this;
        $.ajax ({
            url: getAPI("getPatientFunctioning"),
                data: {
                    patient: patientId
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            let listOfFunc = functioning.collect(true, json) + "<hr/>" + functioning.collect(false, json);
            $("#visitPatientFunctioning .contents").html(listOfFunc);
            
            $("#visitPatientFunctioning .contents ul li").click(function() {
                functioning.currentEpisode = $(this).attr("episode");
                functioning.currentStatement = $(this).attr("statement");
                functioning.currentStatementMark = $(this).find("span");
                functioning.dashboard.episodes.select(functioning.currentEpisode);
                if ($(this).hasClass("functioningName")) {
                    functioning.showImportanceMenu();
                }
            });
        })
    }
    
    collect(active, json)
    {
        let i = 0;
        let episodeId = 0;
        let listOfFunc = "<ul class=\"";
        if (active) {
            listOfFunc += "activeEpisode";
        }
        else {
            listOfFunc += "inActiveEpisode";
        }
        listOfFunc += "\">";
        while (i < json.length) {
            if (json[i].active == active) {
                let text = json[i].text;
                if (text != "") {
                    let value = "";
                    let j = text.indexOf('&');
                    if (j == 4) {
                        let k = text.indexOf(' ', j);
                        if (k > j) {
                            let code = text.substring(0, j);
                            let label = text.substring(k);
                            let n = label.indexOf(':');
                            if (n > 0) {
                                value = label.substring(n+1).trim();
                                label = label.substring(0, n+1);
                                if (json[i].episodeId != episodeId) {
                                    if (episodeId != 0) {
                                        listOfFunc += "<li><hr></li>";
                                    }
                                    episodeId = json[i].episodeId;
                                    listOfFunc += "<li class=\"episodeName\" episode=\"" + json[i].episodeId + "\">"  + json[i].episodeName + "</li>";
                                }
                                let values = "";
                                let importance = json[i].importance;
                                listOfFunc += "<li class=\"functioningName\" episode=\"" + json[i].episodeId;
                                listOfFunc += "\" statement =\"" + json[i].id + "\">";
                                values += "<li class=\"functioningValue\" episode=\"" + json[i].episodeId + "\">" + json[i].date + " " + value + "</li>";
                                
                                let ii = i+1;
                                while (ii < json.length) {
                                    if ((json[ii].episodeId == episodeId) && (json[ii].text.indexOf(code) == 0)) {
                                        j = json[ii].text.indexOf('&');
                                        if (j == 4) {
                                            n = json[ii].text.indexOf(':', j);
                                            if (n > 0) {
                                                value = json[ii].text.substring(n+1).trim();
                                                values += "<li  class=\"functioningValue\" episode=\"" + json[i].episodeId + "\">";
                                                values += json[ii].date + " " + value + "</li>";
                                                json[ii].text = "";
                                                if (importance == 0) {
                                                    importance = json[ii].importance;
                                                }
                                                if (ii = i+1) i = ii-1;
                                            }
                                        }
                                    }
                                    ii++;
                                }
                                listOfFunc += code + label + " " + this.addImportance(importance) + "</li>";
                                listOfFunc += values;
                            }
                        }
                    }
                }
            }
            i++;
        }
        listOfFunc += "</ul>";
        return listOfFunc;
    }
    
    addImportance(value)
    {
        let mark = "<span class=\"importanceMark\">";
        while (value > 0) {
            mark += "*";
            value--;
        }
        mark += "</span>";
        return mark;
    }
    
    showImportanceMenu()
    {
        if (this.timer) clearTimeout(this.timer);
        let menu = $("#functioningImportance");
        menu.css("left", this.cursorX);
        menu.css("top", this.cursorY);
        menu.show(250);
        this.timer = setTimeout(function(){menu.hide();}, 8000); // close after 8 seconds
    }
    
}

