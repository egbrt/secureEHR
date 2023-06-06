/*
    Classification Workbench
    Copyright (c) 2020-2021, WONCA ICPC-3 Foundation

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

import {TouchMenu, closeMenu} from './touchMenu.js';
import {API, openPatients} from './api.js';
import {RFE, SUBJECTIVE, OBJECTIVE, ASSESSMENT, PLAN, MEDICATION, LABORATORY, SURGERY} from './visits.js';
import {Patients} from './patients.js';
import {Dashboard} from "./dashboard.js";

let dashboard = new Dashboard();

$(function() {
    TouchMenu('#menu', '#menuButton');
    API();

    Patients(dashboard);

    $('.switch').click(function() {
        var list = '#' + $(this).attr('for');
        $(list).toggle();
    });
    
    $('#closeICPC3').click(function() {
        dashboard.icpc3.close();
    });
    
    var to = false;
    let modes = [RFE, SUBJECTIVE, OBJECTIVE, ASSESSMENT, PLAN];
    $("#visitAllFinding").keyup(function(e) {
        dashboard.icpc3.close();
        if (e.key == "Enter") {
            let finding = $('#visitAllFinding').val().trim();
            if (finding != "") {
                dashboard.contact.add(finding);
                $('#visitAllFinding').val('').focus();
            }
        }
        else {
            if (to) {clearTimeout(to)};
            let searchText = $('#visitAllFinding').val().toUpperCase();
            switch (searchText) {
                case "R ":
                    dashboard.contact.moveTo(RFE);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    break;
                case "S ":
                    dashboard.contact.moveTo(SUBJECTIVE);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    break;
                case "O ":
                    dashboard.contact.moveTo(OBJECTIVE);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    break;
                case "A ":
                    dashboard.contact.moveTo(ASSESSMENT);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    break;
                case "P ":
                    dashboard.contact.moveTo(PLAN);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    break;
                case "M ":
                    if (dashboard.contact.currentDiagnosis == "") {
                        dashboard.contact.moveTo(ASSESSMENT);
                        $('#visitAllFinding').attr("placeholder", "Search term for ICPC-3, or free text");
                    }
                    else {
                        dashboard.contact.moveTo(MEDICATION);
                        $('#visitAllFinding').attr("placeholder", "Drug name, 3x daily, 10 days");
                    }
                    $('#visitAllFinding').val("");
                    break;
                case "L ":
                    dashboard.contact.moveTo(LABORATORY);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Enter laboratory result");
                    break;
                case "SU ":
                    dashboard.contact.moveTo(SURGERY);
                    $('#visitAllFinding').val("");
                    $('#visitAllFinding').attr("placeholder", "Enter (previous) surgery as free text");
                    break;
                default:
                    if (modes.includes(dashboard.icpc3.getMode())) {
                        let searchText = $('#visitAllFinding').val().trim();
                        if (searchText != dashboard.icpc3.previousSearchText) {
                            dashboard.icpc3.previousSearchText = searchText;
                            if (searchText.length > 2) {
                                to = setTimeout(function(){dashboard.icpc3.search(searchText)}, 500);
                            }
                        }
                    }
            }
        }
    });
    
    $('#addAllFinding').click(function() {
        let finding = $('#visitAllFinding').val().trim();
        if (finding != "") {
            dashboard.contact.add(finding);
            $('#visitAllFinding').val('').focus();
        }            
    });

    $('#visitSave').click(function() {
        $(this).attr('disabled', true);
        dashboard.contact.save();
    });
    
    $('#visitDone').click(function() {
        openPatients();
    });

    dashboard.contact.show('');
})
