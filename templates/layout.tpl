{$pageType=$pageType|default:'other'}
<!DOCTYPE html>
<html lang="ro">

  <head>
    <title>
      {block "title"}{/block}
      {if $pageType != 'home'}
        | {Config::SITE_NAME}
      {/if}
    </title>

    <meta charset="utf-8">
    {include "layout/phpConstants.tpl"}
    {include "layout/metaDescription.tpl"}
    {include "layout/fonts.tpl"}
    {include "layout/css.tpl"}
    {include "layout/favicon.tpl"}
    {include "layout/js.tpl"}
  </head>

  <body id="infoarena">
    <div id="page">
      {include "layout/header.tpl"}
      <div class="clear" id="content_small">
        {include "layout/sidebar.tpl"}

        <div id="main">
          {include "layout/recentPages.tpl"}
          {include "layout/flashMessages.tpl"}

          {block "content"}{/block}
        </div>
      </div>
    </div>

    {include "layout/footer.tpl"}
  </body>
</html>
