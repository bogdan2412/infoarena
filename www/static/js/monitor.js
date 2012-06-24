var Monitor_Url;
var Monitor_Timeout = 5000; // 5 seconds
var Monitor_AutoRefresh = true; // enabled by default

function Monitor_Init() {
    $('#autorefresh').prop('checked', Monitor_AutoRefresh);

    Monitor_ToggleRefresh(Monitor_AutoRefresh);
}

function Monitor_Refresh(){
    $("#monitor-table").load(Monitor_Url, {},
        function(responseText, statusText, req) {
            if (Monitor_AutoRefresh) {
                setTimeout(Monitor_Refresh, Monitor_Timeout);
            }
        });
}

function Monitor_ToggleRefresh(selected) {
    Monitor_AutoRefresh = selected;
    if (Monitor_AutoRefresh) {
        Monitor_Refresh();
    }
}

$(document).ready(Monitor_Init);
