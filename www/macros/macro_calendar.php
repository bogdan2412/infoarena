<?php
require_once(IA_ROOT_DIR.'www/url.php');

function macro_calendar($args) {
    $max_events = getattr($args, 'limit', 20);
    $forumurl = url_forum();

    // Find all events which are happening in the near future that the member can see.
    $result = db_fetch_all("
        SELECT
            cal.ID_EVENT, cal.startDate, cal.endDate, cal.title, cal.ID_MEMBER, cal.ID_TOPIC,
            cal.ID_BOARD, t.ID_FIRST_MSG
        FROM ia_smf_calendar AS cal
            LEFT JOIN ia_smf_boards AS b ON (b.ID_BOARD = cal.ID_BOARD)
            LEFT JOIN ia_smf_topics AS t ON (t.ID_TOPIC = cal.ID_TOPIC)
        WHERE cal.endDate >= '" . strftime('%Y-%m-%d', time()) . "' AND
        cal.startDate <= DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY cal.startDate
        LIMIT $max_events");
    $return = array();
    $duplicates = array();
    setlocale(LC_TIME, 'ro_RO.utf8');
    foreach ($result as $row)
    {
        // Check if we've already come by an event linked to this same topic with the same title... and don't display it if we have.
        if (!empty($duplicates[$row['title'] . $row['ID_TOPIC']]))
            continue;

        if ($row['startDate'] < strftime('%Y-%m-%d', time()))
            $date = strftime('%Y-%m-%d', time());
        else
            $date = $row['startDate'];

        $return[$date][] = array(
            'id' => $row['ID_EVENT'],
            'title' => $row['title'],
            'modify_href' => $forumurl . '?action=' . ($row['ID_BOARD'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $row['ID_FIRST_MSG'] . ';topic=' . $row['ID_TOPIC'] . '.0;calendar;') . 'eventid=' . $row['ID_EVENT'],
            'href' => $row['ID_BOARD'] == 0 ? '' : $forumurl . '?topic=' . $row['ID_TOPIC'] . '.0',
            'link' => $row['ID_BOARD'] == 0 ? $row['title'] : '<a href="' . $forumurl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['title'] . '</a>',
            'start_date' => $row['startDate'],
            'end_date' => $row['endDate'],
            'is_last' => false
        );

        // Let's not show this one again, huh?
        $duplicates[$row['title'] . $row['ID_TOPIC']] = true;
    }

    foreach ($return as $mday => $array)
        $return[$mday][count($array) - 1]['is_last'] = true;

    $html = '<div class="calendar">';
    $html .= '<div class="header">';
    $html .= '<a href="'.IA_URL.'forum?action=calendar">În curând...</a></div>';
    foreach ($return as $mday => $array)
    {
        $html .= '<div class="date">'.strftime("%A, %d %b %Y", strtotime($mday)).'</div>';
        foreach ($array as $event)
        {
            $html .= '<div class="event">';
            $html .= '&raquo;';
            $html .=  $event['link'];
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    return $html;
}

?>
