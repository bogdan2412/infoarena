<?php
require_once(IA_ROOT_DIR.'common/tags.php');

// Add this to every form that has the autocomplete input box
function tag_form_event() {
    return 'onsubmit="return checkForm();"';
}

// Format a tag input box
// FIXME: Width parameter does not work, I hate CSS
function tag_format_input_box($value = null, $width = "50", $name = "tags") {
    $esc_name = htmlentities($name);
    $esc_width = htmlentities($width); 
    $esc_value = htmlentities($value);

    $output = '<li><label for="form_'.$esc_name.'">Tag-uri</label>';
    $output .= ferr_span($name);
    $output .= '<input class="wickEnabled" type="text" name="'.$esc_name.
               '" id="form_'.$esc_name.'"';
    if (!is_null($width)) {
        $output .= ' width="'.$esc_width.'"';
    }
    if (!is_null($value)) {
        $output .= ' value="'.$esc_value.'"';
    }
    $output .= ' />';
    $output .= '<script type="text/javascript" language="JavaScript" src="'.
                htmlentities(url_static("js/wick.js")).'" />';
    $output .= "</label></li>";
    return $output;
}
?>

<link rel="stylesheet" type="text/css" href="<?= htmlentities(url_static("css/wick.css")) ?> " />
<script type="text/javascript" language="JavaScript">
function checkForm() {
    answer = true;
    if (siw && siw.selectingSomething) {
        answer = false;
    }
    return answer;
}
<?php
$tag_names = tag_get_all_names();
echo  "collection = [\n";
foreach ($tag_names as $tag) {
    echo "'".htmlentities($tag['name'])."',\n";
}
echo "];\n";
?>
</script>
