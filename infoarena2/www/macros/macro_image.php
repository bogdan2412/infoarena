<?php
// src = file name, link = [optional] address, 
function macro_image($args) {
    $image = getattr($args, 'src');
    if (is_null($image)) {
        return null;
    }
    $float = getattr($args, 'float');
    $caption = getattr($args, 'caption');
    $link = getattr($args, 'link');

    $ret = "";
    $ret .= '<div class="imagecaption"';
    if (!is_null($float)) {
        $ret .= ' style="float: '.$float.'">';
    }
    else {
        $ret .= '>';
    }
    if (!is_null($link)) {
        $ret .= '<a href="'.url($link).'">';
    }
    $ret .= '<img src="'.url($args['page_name'].'?action=download&file='.$image).'" alt="'.htmlentities($caption).'"/>';
    if (!is_null($link)) {
        $ret .= '</a>';
    }
    $ret .= '<div class="caption">'.htmlentities($caption).'</div>';
    $ret .= '</div>';

    return $ret;
}

?>