<?php

require_once(IA_ROOT_DIR."common/db/db.php");

function macro_preoni_tasks($args) {
    if (!identity_can('macro-grep')) {
        return macro_permission_error();
    }
    
    $board = getattr($args, 'board', 54);
    $query = sprintf("SELECT msg_in.subject FROM ia_smf_topics top_out 
                      LEFT JOIN ia_smf_messages msg_in ON
                      msg_in.ID_MSG = (SELECT MIN(ID_MSG) FROM ia_smf_messages msg_in WHERE msg_in.ID_TOPIC = top_out.ID_TOPIC) 
                      WHERE top_out.ID_BOARD = %d AND locked = 0", db_escape($board));
    $tasks = db_fetch_all($query);

    $cnt = array('?' => 0);
    for ($i = 1; $i <= 10; ++$i) {
        $cnt[$i] = 0;
    }
    $total_cnt = 0;
    foreach ($tasks as $task) {
        if (preg_match('/\[([0-9?])\]/', $task['subject'], $matches)) {
           $cnt[$matches[1]]++;
           $total_cnt++;
        }
    }

    $html = '<table  style="width: 0%; border-collapse: separate;"  cellspacing="0" cellpadding="0">';
    $html .= '<tbody>';
    $html .= '<tr><td colspan="11" style="border-style: none;">';
    $html .= '<sub>Total: '.$total_cnt.'</sub>';
    $html .= '</td></tr>';
    $html .= '<tr>';
    foreach ($cnt as $k => $v) {
        $html .= '<td style="border: 1px solid #FFFFFF;" valign="bottom"  align="center">';
        if ($v > 0) {
            $html .= '<sub>'.$v.'</sub>';
        }
        $html .= '<img src="http://infoarena.ro/planificare/preoni-2008?action=download&file=bardot.gif" width="20" height="'.($v*10).'">';
        $html .= '</td>';
    }
    $html .= '</tr>';
    $html .= '<tr>';
    foreach ($cnt as $k => $v) {
        $html .= '<td style="border-color: #000000 #FFFFFF #FFFFFF; border-style: solid; border-width:1px;" width="160" valign="top" align="center">';
        $html .= '<strong><sup>'.$k.'</sup></strong>';
        $html .= '</td>';
    }
    $html .= '</tr>';
    $html .= '</tbody>';
    $html .= '</table>';

    return $html;
}

?>
