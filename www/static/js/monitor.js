var Monitor_Url;
var Monitor_Timeout = 5000; // 5 seconds
var Monitor_AutoRefresh = true; // enabled by default

function Monitor_Init() {
    $('autorefresh').checked = Monitor_AutoRefresh;

    Monitor_ToggleRefresh(Monitor_AutoRefresh);
}

function Monitor_Refresh(){
    var d = doSimpleXMLHttpRequest(Monitor_Url);
    var success = function(meta) {
        if (!Monitor_AutoRefresh) {
            return;
        }
        $("monitor-table").innerHTML = meta.responseText;
        setTimeout(Monitor_Refresh, Monitor_Timeout);
    };
    var fail = function(err) {
        if (Monitor_AutoRefresh) {
            setTimeout(Monitor_Refresh, Monitor_Timeout);
        }
    };
    d.addCallbacks(success, fail);
}

function Monitor_ToggleRefresh(selected) {
    Monitor_AutoRefresh = selected;
    if (Monitor_AutoRefresh) {
        Monitor_Refresh();
    }
}

connect(window, 'onload', Monitor_Init);

