/**
 * DHTML for round editing page
 * (c) info-arena
 */ 
function RoundEdit_Init() {
    // transform tasks select multiple box into a shuttle box
    DlbInit('form_tasks');
}

connect(window, 'onload', RoundEdit_Init);

