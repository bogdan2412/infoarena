{$pageType=$pageType|default:'other'}
<!DOCTYPE html>
<html lang="ro">

  <head>
    <title>
      {block "title"}{/block}
      {if $pageType != 'home'}
        | {$smarty.const.SITE_NAME}
      {/if}
    </title>

    <meta charset="utf-8">
    {include "bits/phpConstants.tpl"}
    {include "bits/metaDescription.tpl"}
    {include "bits/googleSiteVerification.tpl"}
    {include "layout/fonts.tpl"}
    {include "layout/css.tpl"}
    {include "layout/favicon.tpl"}
    {include "bits/js.tpl"}
  </head>

  <body id="infoarena">
    <div id="page">
      {include "bits/header.tpl"}
      <div class="clear" id="content_small">
        {include "bits/sidebar.tpl"}

        <div id="main">
          {include "bits/recentPages.tpl"}
          {include "bits/flashMessages.tpl"}

          {block "content"}{/block}
        </div>
      </div>
    </div>

    {include "bits/footer.tpl"}
  </body>
</html>
