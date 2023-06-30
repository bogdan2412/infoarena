{if FlashMessage::hasMessage()}
  <div
    class="flash {FlashMessage::getCssClass()}"
    id="flash">
    {FlashMessage::getMessage()|escape}
  </div>
{/if}
