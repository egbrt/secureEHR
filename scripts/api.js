var url = "";
var urlAPI = "";

export function API() {
    var url = "//" + location.hostname;
    if (url.indexOf("localhost") > 0) {
        url += "/secureEHR";
    }
    urlAPI = url + "/api.php";
}

export function getAPI(operation)
{
    return urlAPI + "?operation=" + operation;
}

export function openPatients()
{
    url = url + "?page=patients";
    window.location.replace(url).focus();
}

export function openDashboard(patientId)
{
    url = url + "?page=dashboard&patient=" + patientId;
    window.location.replace(url).focus();
}
