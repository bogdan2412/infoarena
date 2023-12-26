<div class="gallery">
  {foreach $attachments as $a}
    <a href="{$a->getUrl()}">
      <img
        alt="imagine galerie {$a->name}"
        src="{$a->getGalleryThumbUrl()}">
    </a>
  {/foreach}
</div>
