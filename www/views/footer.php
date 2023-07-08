<?php
require_once(Config::ROOT.'www/views/sitewide.php');
?>
</div>
</div>
</div>

<?php
if (Wiki::hasMathJax()) {
  $url = 'js/third-party/mathjax-3.2.2/tex-chtml.js';
  $url = html_escape(url_static($url));
  print "<script id=\"MathJax-script\" async src=\"{$url}\"></script>\n";
}
?>

<?php ia_template_footer(); ?>

</body>
</html>
