{foreach $jsFiles as $js}
  <script src="{$js}"></script>
{/foreach}

<script>
  hljs.initHighlightingOnLoad();
  hljs.initLineNumbersOnLoad();
</script>

{if Wiki::hasMathJax()}
  <script
    async
    id="MathJax-script"
    src="{url_static('js/third-party/mathjax-3.2.2/tex-chtml.js')}">
  </script>
{/if}

{if !Config::DEVELOPMENT_MODE && $smarty.const.GOOGLE_ANALYTICS_TRACKING_ID}
  <script src="http://www.google-analytics.com/urchin.js"></script>
  <script>
    _uacct = "{$smarty.const.GOOGLE_ANALYTICS_TRACKING_ID}";
    _udn = "infoarena.ro";
    urchinTracker();
  </script>
{/if}
