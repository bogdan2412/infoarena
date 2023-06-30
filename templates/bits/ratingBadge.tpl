{$rb=$rb|default:null}
{if $rb}
  <a
    class="rating-badge-{$rb->getRatingClass()}"
    href="{User::getRatingUrl($rb->getUsername())}"
    title="Rating {$rb->getUsername()}: {$rb->getRating()}">
    &bull;
  </a>
{/if}
