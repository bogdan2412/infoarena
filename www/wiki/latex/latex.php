<?php
/**
 * LaTeX Rendering Class - Calling function
 * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * @version v0.8
 * @package latexrender
 * Revised by Steve Mayer
 * This file can be included in many PHP programs by using something like (see example.php to see how it can be used)
 * 		include_once('/full_path_here_to/latexrender/latex.php');
 * 		$text_to_be_converted=latex_content($text_to_be_converted);
 * $text_to_be_converted will then contain the link to the appropriate image
 * or an error code as follows (the values can be altered in class.latexrender.php):
 * 	0 OK
 * 	1 Formula longer than 10000 characters
 * 	2 Includes a blacklisted tag
 * 	3 Latex rendering failed
 * 	4 Cannot create DVI file
 * 	5 Picture larger than 800 x 600 followed by x x y dimensions
 * 	6 Cannot copy image to latex directory
 * 
 * This version includes Mike Boyle's modifications to allow vertical offset of LaTeX formulae
 */

 function latex_content($text) {
    // adjust this to match your system configuration
    include_once(IA_ROOT_DIR."www/wiki/latex/class.latexrender.php");
    $latexrender_path = IA_ROOT_DIR."www/static/images";
    $latexrender_path_http = "static/images";

    $latex = new LatexRender($latexrender_path."/latex",$latexrender_path_http."/latex",$latexrender_path."/tmp");

    $latex_formula = $text;

    $url = $latex->getFormulaURL($latex_formula);
    // offset: get depth information from filename
    $filename = basename($url);
    $filename = str_replace("_",".",$filename);
    $farray = explode(".",$filename);
    if (count($farray)>2){
        $style_css = ' style="vertical-align:-'.$farray[1].".".$farray[2].';" ';
    } else {
        $style_css = " align=absmiddle";
    }

    $alt_latex_formula = htmlentities($latex_formula, ENT_QUOTES);
    $alt_latex_formula = str_replace("\r","&#13;",$alt_latex_formula);
    $alt_latex_formula = str_replace("\n","&#10;",$alt_latex_formula);

    if ($url != false) {
    // offset: add vertical alignment in $style_css
        $text = "<img src='".$url."' title='".$alt_latex_formula."' alt='".$alt_latex_formula."'.$style_css>";
    } else {
        $text = "[Unparseable or potentially dangerous LaTeX formula! Error $latex->_errorcode $latex->_errorextra]";
    }
    return $text;
}
?>
