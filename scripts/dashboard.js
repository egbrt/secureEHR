import {getAPI} from "./api.js";
import {Contact} from "./contact.js";
import {ICPC3} from "./icpc3.js";
import {Notes} from "./notes.js";
import {Drugs} from "./drugs.js";
import {Visits} from "./visits.js";
import {Episodes} from "./episodes.js";
import {Functioning} from "./functioning.js";
import {Laboratory} from "./laboratory.js";

export class Dashboard {
    constructor()
    {
        this.icpc3 = new ICPC3(this);
        this.contact = new Contact(this);
        this.notes = new Notes();
        this.drugs = new Drugs(this);
        this.visits = new Visits(this);
        this.episodes = new Episodes(this);
        this.functioning = new Functioning(this);
        this.laboratory = new Laboratory(this);
    }
    
    show(patientId, episodeId)
    {
        this.episodes.show(patientId, episodeId);
        this.drugs.show(patientId);
        this.laboratory.show(patientId);
        this.functioning.show(patientId);
    }
    
    select(episodeId)
    {
        this.doSelect(episodeId, "#visitPatientDrugs");
        this.doSelect(episodeId, "#visitPatientFunctioning");
        this.doSelect(episodeId, "#visitPatientLaboratory");
    }
    
    doSelect(episodeId, div)
    {
        let current = $(div + " .contents ul li.selected");
        if (current.attr("episode") != episodeId) {
            current.removeClass('selected');
            $(div + " .contents ul li.episodeName").each(function() {
                if ($(this).attr("episode") == episodeId) {
                    $(this).addClass('selected');
                }
            });
        }    
    }
    

    
    refresh(episodeId)
    {
        let patientId = $('#visitPatientId').attr('patientId');
        if (patientId) {
            if (episodeId == 0) episodeId = $('#visitEpisodes ul li.selected').attr('id');
            this.show(patientId, episodeId);
            this.notes.show(patientId);
        }
    }


}

