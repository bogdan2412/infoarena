<?php
require_once(IA_ROOT_DIR.'common/tags.php');

// Add this to every form that has the autocomplete input box
function tag_form_event() {
    return 'onsubmit="return checkForm();"';
}

// Format a tag input box
// FIXME: Width parameter does not work, I hate CSS
function tag_format_input_box($field, $value = null, $width = "50", $name = "tags") {
    $esc_name = html_escape($field['name']);
    $esc_width = html_escape($width);
    // presume $value is html-escaped

    $output = '<li><label for="form_'.$esc_name.'">'.$field['label'].'</label>';
    $output .= ferr_span($name);
    $output .= '<input class="wickEnabled:wick_'.$name.'" type="text" name="'.$esc_name.
               '" id="form_'.$esc_name.'"';
    if (!is_null($width)) {
        $output .= ' size="'.$esc_width.'"';
    }
    if (!is_null($value)) {
        $output .= ' value="'.$value.'"';
    }
    $output .= ' autocomplete="off" />';
    $output .= '<table id="wick_'.$name.'" class="floater"><tr><td><div class="wick_content"></div></td></tr></table>';
    $output .= "</li>";
    return $output;
}
?>

<link rel="stylesheet" type="text/css" href="<?= html_escape(url_static("css/wick.css")) ?> " />
<script type="text/javascript" language="JavaScript">
/* <![CDATA[ */
function checkForm() {
    answer = true;
    if (siw && siw.selectingSomething) {
        answer = false;
    }
    return answer;
}
<?php
$tag_names = tag_get_all();
echo  "collection = [\n";
foreach ($tag_names as $tag) {
    echo "'".html_escape($tag['name'])."',\n";
}
echo "];\n";
?>
/* ]]> */
</script>
<script type="text/javascript" language="JavaScript" src="<?php echo html_escape(url_static("js/wick.js")) ?>"></script>
