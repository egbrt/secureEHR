export class Info {
    constructor()
    {
        this.urlAPI = "//" + location.hostname;
        if (this.urlAPI.indexOf("localhost") > 0) {
            this.urlAPI += "/secureEHR";
        }
        this.urlAPI += "/portal/api.php";
        
        let stored = window.localStorage.getItem("info");
        if (stored) {
            let json = JSON.parse(stored);
            this.showPatientInfo(json.patient);
            this.showDiagnoses(json.diagnoses);
            this.showMedication(json.medication);
            this.showLaboratory(json.laboratory);
        }
        else {
            this.getInfo();
        }
    }
    
    update()
    {
        window.localStorage.clear();
        this.getInfo();
    }
    
    clear()
    {
        let info = this;
        $.ajax ({
            url: info.urlAPI,
            data: {reset:true},
            type: "POST",
            dataType: "json",
        })
        .done (function() {
            info.update();
        })
    }
    
    getInfo()
    {
        let info = this;
        $.ajax ({
            url: info.urlAPI,
            type: "POST",
            dataType: "json",
        })
        .done (function(json) {
            if (json) {
                window.localStorage.setItem("info", JSON.stringify(json));
                info.showPatientInfo(json.patient);
                info.showDiagnoses(json.diagnoses);
                info.showMedication(json.medication);
                info.showLaboratory(json.laboratory);
            }
        })
        .fail(function(jqXHR, status) {
            window.location.href = "./login.php";
        })
    }
    
    showPatientInfo(patient)
    {
        $("#patientInfo").val(patient.info);
    }
    
    showDiagnoses(diagnoses)
    {
        let list = "<ul>";
        diagnoses.forEach(function(diagnosis) {
            list += "<li>" + diagnosis.startdate + " " + diagnosis.name + "</li>";
        });
        list += "</ul>";
        $("#diagnoses").html(list);
    }
    
    showMedication(medication)
    {
        let list = "<ul>";
        let episodeId = 0;
        medication.forEach(function(medicine) {
            if (medicine.episodeId != episodeId) {
                if (episodeId != 0) list += "<li><hr></li>";
                episodeId = medicine.episodeId;
                list += "<li>" + medicine.episodeName + "</li>";
            }
            list += "<li style=\"text-indent:10px\">" + medicine.date + " " + medicine.text + "</li>";
        });
        list += "</ul>";
        $("#medication").html(list);
    }
    
    showLaboratory(laboratory)
    {
        let list = "<ul>";
        laboratory.forEach(function(lab) {
            list += "<li>" + lab.date + " " + lab.text + "</li>";
        });
        list += "</ul>";
        $("#laboratory").html(list);
    }
    
}


