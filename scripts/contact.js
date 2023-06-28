import {OBJECTIVE, ASSESSMENT, PLAN, MEDICATION, LABORATORY, SURGERY} from './visits.js';
import {getAPI} from './api.js';

export class Contact {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
        this.currentEpisode = "";
        this.currentDiagnosis = "";
    }
    
    moveTo(mode)
    {
        $('#visitAllFindings ul li.selected').removeClass("selected");
        $('#visitAllFindings ul li').each(function() {
            if ($(this).hasClass("heading")) {
                if ($(this).html() == mode) {
                    $(this).addClass("selected");
                }
            }
        })
        this.dashboard.icpc3.setMode(mode);
    }
    
    add(finding)
    {
        let contact = this;
        this.findings = "";
        let found = false;
        let assessment = false;
        let courseExtension = finding.startsWith('COU');
        this.currentDiagnosis = "";
        let i = 0;
        let list = Array();
        $('#visitAllFindings ul li').each(function() {
            if (($(this).hasClass("heading")) || ($(this).hasClass("separator"))) {
                if (found) {
                    if ((courseExtension) && (list.length > 0)) {
                        list[list.length-1] = contact.addExtensionCode(list[list.length-1], finding);
                    }
                    else {
                        list[i] = finding;
                        i++;
                    }
                }
                let j = 0;
                while (j < list.length) {
                    contact.findings += "<li>" + list[j] + "</li>";
                    j++;
                }
                if ((assessment) && (list.length > 0)) {
                    contact.currentDiagnosis = list[list.length-1];
                }
                i = 0;
                list = Array();
                if ($(this).hasClass("heading")) {
                    contact.findings += "<li class=\"heading";
                    if ($(this).hasClass("selected")) contact.findings += " selected";
                }
                else { // separator
                    contact.findings += "<li class=\"separator";
                }
                let title = $(this).attr("title");
                if (title) contact.findings += "\" title=\"" + title;
                contact.findings += "\">" + $(this).html() + "</li>";
                found = (contact.dashboard.icpc3.getMode() == $(this).html());
                assessment = ($(this).html() == ASSESSMENT);
            }
            else {
                list[i] = $(this).html();
                i++;
            }
        });
        let j = 0;
        while (j < list.length) {
            contact.findings += "<li>" + list[j] + "</li>";
            j++;
        }
        if (found) this.findings += "<li>" + finding + "</li>";
        $('#visitSave').removeAttr('disabled');
        this.show();
        this.dashboard.icpc3.close();

        if ((contact.currentDiagnosis != '') && ( $('#visitEpisodes ul li.selected').attr('id') == 0)) {
            $('#visitEpisodes ul li').each(function() {
                let episode = $(this).html();
                let i = episode.indexOf(' ');
                if (i > 0) {
                    episode = episode.substr(i+1);
                    if (episode == contact.currentDiagnosis) {
                        $(this).addClass("sameEpisode");
                        this.scrollIntoView(false);
                        $('#warningExistingEpisode').show(250).click(function() {
                            $('#visitEpisodes ul li.sameEpisode').removeClass('sameEpisode');
                            $(this).hide(250);
                        });                        
                    }
                }
            });
        }
    }
    
    
    addExtensionCode(main, extension)
    {
        let newText = "";
        let i = main.indexOf(' ');
        let j = extension.indexOf(' ');
        if ((i > 0) && (j > 0)) {
            let mainCode = main.substring(0, i);
            let mainText = main.substring(i);
            let extensionCode = extension.substring(0, j);
            let extensionText = extension.substring(j);
            newText = mainCode + "&" + extensionCode + extensionText + mainText.toLowerCase();
        }
        else {
            newText = main + " AND " + extension;
        }
        return newText;
    }
    
    
    remove(finding)
    {
        let mode = "";
        let contact = this;
        this.findings = "";
        this.currentDiagnosis = "";
        $('#visitAllFindings ul li').each(function() {
            if ($(this).html() != finding) {
                contact.findings += "<li";
                if ($(this).hasClass("heading")) {
                    mode = $(this).html();
                    contact.findings += " class=\"heading";
                    if ($(this).hasClass("selected")) contact.findings += " selected";
                    contact.findings += "\"";
                }
                else if ($(this).hasClass("separator")) {
                    contact.findings += "<li class=\"separator\"";
                }
                else if (mode == ASSESSMENT) {
                    contact.currentDiagnosis = $(this).html();
                }
                let title = $(this).attr("title");
                if (title) contact.findings += "\" title=\"" + title + "\"";
                contact.findings += ">" + $(this).html() + "</li>";
            }
        });
        this.show();
    }
    
    
    show()
    {
        let contact = this;
        if (!this.findings) {
            this.findings = this.dashboard.icpc3.getAllModes();
            this.findings += "<li class=\"separator\"><hr></li>";
            this.findings += "<li class=\"heading\" title=\"The prescribed medication for the specified diagnosis\">" + MEDICATION + "</li>";
            this.findings += "<li class=\"heading\" title=\"The laboratory and other results\">" + LABORATORY + "</li>";
            this.findings += "<li class=\"heading\" title=\"Use this if you want to document previous surgery.\">" + SURGERY + "</li>";
        }
        $('#visitAllFindings ul').html(contact.findings);
        $('#visitDrugs').hide();
        
        $('#visitAllFindings ul li.heading').click(function() {
            $('#visitAllFindings ul li.selected').removeClass('selected');
            $(this).addClass('selected');
            contact.dashboard.icpc3.setMode($(this).html());
            $('#warningMedication').hide(250);
            $('#visitAllFinding').focus();
            if (contact.dashboard.icpc3.getMode() == MEDICATION) {
                if (contact.currentEpisode != "") {
                    let text = "Medication for " + contact.currentEpisode;
                    $('#visitAllFinding').attr("placeholder", text);
                    contact.dashboard.icpc3.close();
                    contact.searchDrugs();
                }
                else if (contact.currentDiagnosis == "") {
                    $('#warningMedication').show(250);
                    contact.dashboard.icpc3.close();
                }
                else {
                    let text = "Medication for " + contact.currentDiagnosis;
                    $('#visitAllFinding').attr("placeholder", text);
                    contact.dashboard.icpc3.close();
                    contact.searchDrugs();
                }
            }
            else if (contact.dashboard.icpc3.getMode() == LABORATORY) {
                $('#visitAllFinding').attr("placeholder", "Enter laboratory result");
                $('#visitDrugs').hide();
                contact.dashboard.icpc3.close();
            }
            else {
                $('#visitDrugs').hide();
                $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                if (contact.dashboard.icpc3.getMode() == PLAN) {
                    contact.getInterventions();
                }
                else if (contact.dashboard.icpc3.getMode() == OBJECTIVE) {
                    contact.dashboard.icpc3.searchCodes("-101 -102 -103 -104 -105 -106 -107 -108 -109 -110 -111 -114 -199");
                }
                else if (contact.dashboard.icpc3.getMode() != SURGERY) {
                    contact.dashboard.icpc3.search(contact.dashboard.icpc3.previousSearchText);
                }
                else {
                    contact.dashboard.icpc3.close();
                }
            }
        });
        
        $('#visitAllFindings ul li').dblclick(function() {
            if (!$(this).hasClass("heading")) {
                contact.remove($(this).html());
            }
        });
    }
    
    
    getInterventions()
    {
        let contact = this;
        if (this.currentDiagnosis != "") {
            let code = contact.currentDiagnosis.split(' ');
            $.ajax ({
                url: getAPI("getDisInterventions"),
                    data: {
                        diagnosis: code[0]
                    },
                    type: "POST",
                    dataType: "json",
            })
            .done (function(json) {
                let i = 0;
                let search = "";
                while (i < json.length) {
                    if (search != "") search += " ";
                   search += json[0].intervention;
                   i++;
                }
                contact.dashboard.icpc3.searchCodes(search + " -1 -2 -3 -5 -6");
            })
        }
        else {
            contact.dashboard.icpc3.searchCodes("-1 -2 -3 -5 -6");
        }
    }
    
    
    save()
    {
        let contact = this;
        let statements = Array();
        let numberOfStatement = 0;
        let statement = {kind:"", label:""};
        
        let heading = "unknown";
        $('#visitAllFindings ul li').each(function() {
            if ($(this).hasClass("heading")) {
                heading = $(this).html();
            }
            else if (!$(this).hasClass("separator")) {
                statement = {kind:"", label:""};
                statement.kind = heading;
                statement.label = $(this).html();
                statements[numberOfStatement] = statement;
                numberOfStatement++;
            }
        });
        
        $.ajax ({
            url: getAPI("saveVisit"),
                data: {
                    patient: $('#visitPatientId').attr('patientId'),
                    episode: $('#visitEpisodes ul li.selected').attr('id'),
                    date: $('#appointmentDate').val(),
                    statements: statements
                },
                type: "POST",
                dataType: "json",
        })
        .done (function(json) {
            contact.dashboard.refresh(json.id);
            contact.findings = "";
            contact.show();
        })
    }
    
    searchDrugs()
    {
        let code = "";
        $('#visitDrugs').show();
        if (this.currentDiagnosis == "") {
            $("#visitDrugs").html("Please add your prescription manually, or select a previous <em>Medication</em>.");
        }
        else {
            let i = this.currentDiagnosis.indexOf(' ');
            if (i > 0) {
                code = this.currentDiagnosis.substr(0, i);
            }
        }
        
        let contact = this;
        if (code != "") {
            $("#visitDrugs").html("Searching for prescription, please wait....");
            $.ajax ({
                url: getAPI("getDrugs"),
                    data: {icpc:code},
                    type: "GET",
                    dataType: "json",
            })
            .done (function(json) {
                if (json.length == 0) {
                    $("#visitDrugs").html("Please add your prescription manually, or select a previous <em>Medication</em>.");
                }
                else {
                    let i = 0;
                    let list = "<ul>";
                    /*
                     *        json.sort(function(a,b) {
                     *            if (a.text < b.text) return -1;
                     *            if (a.text > b.text) return 1;
                     *            return 0; });
                     */
                    while (i < json.length) {
                        list += "<li>" + json[i].prescription + "</li>";
                        i++;
                    }
                    list += "</ul>";
                    $("#visitDrugs").html(list);
                    
                    $("#visitDrugs ul li").click(function() {
                        $('#visitDrugs ul li.selected').removeClass('selected');
                        $(this).addClass('selected');
                        $('#visitAllFinding').val($(this).html());
                    });
                    $("#visitDrugs ul li").dblclick(function() {
                        contact.add($(this).html());
                    });
                }
            })
        }
    }
    
    
}
