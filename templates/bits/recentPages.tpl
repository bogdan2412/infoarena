<p id="breadcrumbs">
  Pagini recente »

  {foreach from=RecentPage::getAll() key=i item=rp}
    {if $i}
      <span class="separator">|</span>
    {/if}

    {if $rp->isActive()}
      <strong>{$rp->getTitle()|escape}</strong>
    {else}
      <a href="{$rp->getUrl()}">{$rp->getTitle()}</a>
    {/if}
  {/foreach}
</p>
