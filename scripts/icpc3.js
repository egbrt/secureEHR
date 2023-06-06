/*
    HIS
    Copyright (c) 2022, Egbert van der Haring en Kees van Boven

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

import {RFE, SUBJECTIVE, OBJECTIVE, ASSESSMENT, PLAN} from './visits.js';

export class ICPC3 {
    constructor(dashboard)
    {
        this.dashboard = dashboard;
        this.urlICPC3 = "https://browser.icpc-3.info/browse.php";
        this.currentMode = RFE;
        this.currentCode = "";
        this.previousSearchText = "";
    }
    
    close()
    {
        $('#icpc3-browser').hide();
    }


    setMode(mode)
    {
        this.currentMode = mode;
    }


    getMode()
    {
        return this.currentMode;
    }


    getAllModes()
    {
        var findings = "<li class=\"heading selected\" title=\"The reason for encounter\" >" + RFE + "</li>";
        findings += "<li class=\"separator\"><hr></li>";
        findings += "<li class=\"heading\">" + SUBJECTIVE + "</li>";
        findings += "<li class=\"heading\" title=\"Use ! as first character to add a measurement\">" + OBJECTIVE + "</li>";
        findings += "<li class=\"heading\">" + ASSESSMENT + "</li>";
        findings += "<li class=\"heading\">" + PLAN + "</li>";
        this.setMode(RFE);
        return findings;
    }

    
    search(text)
    {
        if (text == '') {
            this.close();
        }
        else {
            let icpc3 = this;
            $("#icpc3-epdTexts").html("");
            $("#icpc3-results").html("Searching for \"" + text + "\", please wait....");
            $.ajax ({
                url: icpc3.urlICPC3 + "?operation=search1",
                data: {str:text},
                type: "GET",
                dataType: "json",
            })
            .done (function(json) {
                icpc3.show(json.children);
            })
        }
    }

    searchCodes(text)
    {
        if (text == '') {
            this.close();
        }
        else {
            let icpc3 = this;
            $("#icpc3-epdTexts").html("");
            $("#icpc3-results").html("Searching for \"" + text + "\", please wait....");
            $.ajax ({
                url: icpc3.urlICPC3 + "?operation=search1",
                data: {codes:text},
                type: "GET",
                dataType: "json",
            })
            .done (function(json) {
                icpc3.show(json.children, false);
            })
        }
    }

    searchChildren(id)
    {
        let icpc3 = this;
        $("#icpc3-epdTexts").html("");
        $("#icpc3-results").html("");
        $.ajax ({
            url: icpc3.urlICPC3 + "?operation=getClasses",
            data: {id:id},
            type: "GET",
            dataType: "json",
        })
        .done (function(json) {
            icpc3.show(json, false);
        })
    }

    show(json, filter=true)
    {
        if (json.length == 0) {
            $("#icpc3-results").html("<p>No ICPC-3 code(s) found.</p>");
            $("#icpc3-browser").show();
        }
        else {
            var i = 0;
            var list = "<ul>";
            
            json.sort(function(a,b) {
                if (a.text < b.text) return -1;
                if (a.text > b.text) return 1;
                return 0;
            });
            
            while (i < json.length) {
                if (json[i].type == "extensionCode") {
                    // ignore
                }
                else if (filter) {
                    if (this.filter(json[i].text)) {
                        list += "<li id=\"" + json[i].id + "\" " + this.isSubCode(json[i].text) + ">" + json[i].text + "</li>";
                    }
                }
                else {
                    list += "<li id=\"" + json[i].id + "\">" + json[i].text + "</li>";
                }
                i++;
            }

            list += "</ul>";
            $("#icpc3-results").html("<p class=\"subHeader\">Found in ICPC-3, click code for description and/or inclusions</p>" + list);
            $("#icpc3-browser").show();
            
            let icpc3 = this;
            console.log(icpc3);
            $("#icpc3-results ul li").click(function() {
                $('#icpc3-results ul li.selected').removeClass('selected');
                $(this).addClass('selected');
                var parts = $(this).html().split(" ");
                if (parts[0].startsWith('-') && (parts[0].length < 4)) {
                    icpc3.searchChildren(this.id);
                }
                else {
                    icpc3.showDetails(this.id);
                    $('#visitAllFinding').val($(this).html());
                }
            });
            $("#icpc3-results ul li").dblclick(function() {
                icpc3.dashboard.contact.add($(this).html());
                $('#visitAllFinding').val('').focus();
            });
        }
    }
    
    isSubCode(label)
    {
        let subcode = "";
        let i = label.indexOf(" ");
        if (i > 4) subcode = "subcode";
        return subcode;
    }

    showDetails(id)
    {
        let icpc3 = this;
        $.ajax ({
            url: this.urlICPC3 + "?operation=getRubrics",
            data: {id:id},
            type: "GET",
            dataType: "json",
        })
        .done (function(json) {
            icpc3.format(json);
        });
    }


    format(json)
    {
        var i = 1;
        var kind = "";
        var rubrics = "";
        var comments = "";
        var codinghint = "";
        var extCode = "";
        var epdTexts = "<ul>";
        var subClasses = "";
        var description = "";
        var epdTextsULclosed = false;
        var snomedCodes = false;
        $("#icpc3-codingHint").hide();
    
        while (i<json.length) {
            if (json[i].kind == "comment") {
                // ignore
            }
            else {
                if (json[i].kind != kind) {
                    if (kind == "inclusion") description += "</ul>";
                    if (kind == "exclusion") rubrics += "</ul>";
                    if ((json[i].kind == "indexwords") ||
                        (json[i].kind == "shortTitle") ||
                        (json[i].kind == "codinghint") ||
                        (json[i].kind == "note") ||
                        (json[i].kind == "preferred") ||
                        (json[i].kind.startsWith("extension"))) {
                        // ignore
                        }
                        else if (json[i].kind == "inclusion") {
                            // ignore
                        }
                        else if (json[i].kind == "exclusion") {
                            rubrics += "<br/><em>" + json[i].display + "</em><ul>";
                        }
                        else {
                            rubrics += "<br/><em>" + json[i].display + "</em><br/>";
                        }
                        kind = json[i].kind;
                }
                if (json[i].kind == "preferred") {
                    rubrics += "<strong>" + json[0].code + " " + json[i].label + "</strong><br/>";
                    epdTexts += "<li id=\"" + i + "\">" + json[i].label + "</li>";
                }
                else if (json[i].kind == "description") {
                    description += "<p>" + json[i].label + "</p>";
                }
                else if (json[i].kind == "inclusion") {
                    subClasses += this.getSubclass(json[i].label);
                }
                else if (json[i].kind == "indexwords") {
                    //epdTexts += "<li id=\"" + i + "\">" + json[i].label + "</li>";
                }
                else if (json[i].kind == "exclusion") {
                    rubrics += "<li>" + json[i].label + "</li>";
                }
                else if (json[i].kind == "snomed-CT") {
                    var label = this.removeButton(json[i].label);
                    if (label != "") {
                        if (!snomedCodes) {
                            snomedCodes = true;
                        }
                        epdTexts += "<li id=\"" + i + "\">" + label + "</li>";
                    }
                }
                else if ((json[i].kind == "note") || (json[i].kind == "codinghint")) {
                    codinghint += this.removeButton(json[i].label, "<span>", "</span>") + "<br/>";
                    $("#icpc3-codingHint").show(500);
                }
                else if (json[i].kind == "hasExtension") {
                    if (!epdTextsULclosed) {
                        epdTexts += "</ul>";
                        epdTextsULclosed = true;
                    }
                    epdTexts += json[i].label + "<br/>";
                    extCode = json[i].code;
                }
                else if (json[i].kind == "extension_preferred") {
                    epdTexts += "<p class='tab1'>";
                    epdTexts += "<input class=\"possibleExtension\" type=\"radio\" name=\"" + extCode + "\" value=\"" + json[i].code + "\"";
                    epdTexts += " label=\"" + json[i].label + "\">";
                    epdTexts += json[i].label + "</input></p>";
                }
                else if (json[i].kind.startsWith("extension_")) {
                    epdTexts += "<p class='tab2'>" + json[i].label + "</p>";
                }
                else if (json[i].kind == "shortTitle") {
                    // ignore
                }
                else {
                    rubrics += json[i].label + "<br/>";
                }
            }
            i++;
        }
        if (!epdTextsULclosed) epdTexts += "</ul>";
        if (subClasses.length > 0) {
            epdTexts += "<ul>" + subClasses + "</ul>";
        }
        
        let icpc3 = this;
        this.currentCode = json[0].code;
        $("#icpc3-details").html(rubrics);
        $("#icpc3-details").scrollTop(0);
        $("#icpc3-codingHint").html(codinghint);
        $("#icpc3-codingHint span").click(function() {
            let code = $(this).html();
            $("#icpc3-codingHint").hide();
            $('#visitAllFinding').val(code);
            if (code.startsWith('2')) icpc3.dashboard.contact.moveTo(SUBJECTIVE);
            icpc3.search(code);
        });
        
        $("#icpc3-epdTexts").html("<p class=\"subHeader\">Description and/or inclusions (double-click to add to contact)</p>" + description + epdTexts);
        $("#icpc3-epdTexts ul li[id=1]").addClass('selected');
        $("#visitAllFinding").val(this.currentCode + ' ' + $("#icpc3-epdTexts ul li.selected").html());
        
        $('#icpc3-epdTexts ul li').click(function() {
            if (this.id) {
                $('#icpc3-epdTexts ul li.selected').removeClass('selected');
                $(this).addClass('selected');
                $("#visitAllFinding").val(icpc3.getSelectedEpdText());
            }
        });
        
        $('#icpc3-epdTexts ul li').dblclick(function() {
            if (this.id) {
                icpc3.dashboard.contact.add(icpc3.getSelectedEpdText());
                $('#visitAllFinding').val('').focus();
            }
        });
        
        $('.possibleExtension').click(function() {
            $("#visitAllFinding").val(icpc3.getSelectedEpdText());
        });
    }
    
    
    getSubclass(label)
    {
        let text = "";
        let i = label.indexOf("<button");
        if (i >= 0) {
            text =  "<li id=\"" + i + "\">" + this.removeButton(label, "", "", true) + "</li>";
        }
        return text;
    }
    
    
    removeButton(label, pre="[", post="]", reorder=false)
    {
        var newLabel = "";
        var i = label.indexOf("<button");
        if (i >= 0) {
            var j = label.indexOf(">", i);
            if (j > i) {
                var k = label.indexOf("<", j);
                if (k > j) {
                    var m = label.indexOf("</button>", k);
                    var before = label.substring(0, i);
                    var n = before.indexOf(" Id ");
                    if (n >= 0) {
                        before = before.substring(0, n) + " " + before.substring(n+4);
                    }
                    var between = label.substring(j+1, k);
                    var after = label.substring(m+9);
                    if (reorder) {
                        newLabel = between + " " + before + after;
                    }
                    else {
                        newLabel = before + pre + between + post + after;
                    }
                }
            }
        }
        return newLabel;
    }
    
    
    getSelectedEpdText()
    {
        let code = this.currentCode;
        let label = $("#icpc3-epdTexts ul li.selected").html();
        if (label.startsWith(this.currentCode)) {
            let i = label.indexOf(" ");
            code = label.substring(0, i);
            label = label.substring(i+1);
        }
        
        let text = code;
        $(".possibleExtension").each(function() {
            if (this.checked) text += "&" + $(this).attr("value");
        })
        text += " " + label;
        $(".possibleExtension").each(function() {
            if (this.checked) text += ": " + $(this).attr("label");
        })
        return text;
    }
    
    
    /*
     *    1. In het veld van de RFE: alle klassen toegestaan behalve 2F71 t/m 2F99 en 2 R. De klassen uit hoofdstuk uit V zijn niet toegestaan.
     * 
     *    2. In het veld van de anamnese: toegestaan in de hoofdstukken A t/m W alle klassen uit de S component behalve de abnormal finding codes (50 t/m 89). Tevens de klassen uit hoofdstuk Z en hoofdstuk II. De klassen uit hoofdstuk V zijn ook niet toegestaan.
     * 
     *    3. In het veld examination / lichamelijk onderzoek alleen – 101 en -102.
     *    4. In het veld intermediate interventions, dus onderzoek wat in de praktijk plaats vindt: alleen - 103 t/ - 199.
     * 
     *    5. In het veld assessment / diagnose /probleem: Hoofdstukken A1 t/m Z en 2F01 t / m 2F69. De klassen 2F71 t/m 2F99 en 2 R worden uitgesloten. De klassen uit hoofdstuk V zijn ook niet toegestaan.
     * 
     *    6. In het veld policy / plan / beleid: alle klassen -103 t/m X399, -501 t/m -599 en -601 en -602
     */
    filter(text)
    {
        var valid = true;
        var i = text.indexOf(' ');
        if (i > 0) {
            var code = text.substr(0, i);
            if (code.length < 4) {
                valid = false;
            }
            else if (code[0] == 'e') { // extension chapter
                valid = false;
            }
            else if (code.startsWith("CAU")) {
                valid = false;
            }
            else if (this.currentMode == RFE) {
                if ((code >= "2F71") && (code <= "2F99")) {
                    valid = false;
                }
                else if (code.startsWith("2R")) {
                    valid = false;
                }
            }
            else if (this.currentMode == SUBJECTIVE) {
                if (code[0] == '2') { // chapter 2
                    valid = true;
                }
                else if (code[0] == 'Z') { // chapter Z
                    valid = true;
                }
                else if (code[1] != 'S') { // also prevents codes from chapters Z and II
                    valid = false;
                }
                else if ((code.substr(2, 2) >= "50") && (code.substr(2, 2) <= "89")) {
                    valid = false;
                }
            }
            else if (this.currentMode == OBJECTIVE) {
                if ((code >= "2F71") && (code <= "2F99")) {
                    valid = true;
                }
                else if (code[0] != '-') {
                    valid = false;
                }
                else if ((code.substr(1, 3) < "101") || (code.substr(1, 3) > "199")) {
                    valid = false;
                }
            }
            else if (this.currentMode == ASSESSMENT) {
                if ((code >= "2F71") && (code <= "2F99")) {
                    valid = false;
                }
                else if (code.startsWith("2R")) {
                    valid = false;
                }
                else if ((code[1] >= '0') && (code[1] <= '9')) {
                    valid = false;
                }
            }
            else if (this.currentMode == PLAN) {
                if (code[1] == '3') { // codes from -3
                    valid = true;
                }
                else if (code[1] == '4') { // codes from -4
                    valid = false;
                }
                else if (code[0] != '-') {
                    valid = false;
                }
                else if (code.substr(1, 3) < "103") {
                    valid = false;
                }
            }
        }
        return valid;
    }
    
}
