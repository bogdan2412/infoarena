/**
 * DHTML for round editing page
 * (c) infoarena
 */
function RoundEdit_Init() {
    // transform tasks select multiple box into a shuttle box
    DlbInit('form_tasks');
}

$(window).one("load", RoundEdit_Init);
