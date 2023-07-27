{extends "layout.tpl"}

{block "title"}Retrimit formularul...{/block}

{block "content"}
  <h1>Retrimit formularul...</h1>

  <form id="form-repost" action="{$referrer|escape}" method="post">
    {foreach $postData as $key => $value}
      {if is_array($value)}
        {foreach $value as $v}
          <input type="hidden" name="{$key|escape}[]" value="{$v|escape}">
        {/foreach}
      {else}
        <input type="hidden" name="{$key|escape}" value="{$value|escape}">
      {/if}
    {/foreach}

    <button type="submit" class="button important">
      retrimite
    </button>
  </form>

  <script type="text/javascript">
    document.getElementById('form-repost').submit();
  </script>
{/block}
