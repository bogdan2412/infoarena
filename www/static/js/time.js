var srv = $("#srv_time");
var h, m, s;
var timeInterval;

function displayTime(){
    var hours, minutes, seconds;
    hours = h<10?('0'+h):h;
    minutes = m<10?('0'+m):m;
    seconds = s<10?('0'+s):s;
    srv.html(hours+':'+minutes+':'+seconds);
}

function calcTime(){
    s++;
    if(s>59){
	m++;
	s=0;
    }
    if(m>59){
	h++;
	m=0;
    }
    if(h>23){
	h=0;
    }
    displayTime();
}

function loadTime(hou, min, sec){
    s = sec;
    m = min;
    h = hou;
    timeInterval = setInterval("calcTime()", 1000);
}
