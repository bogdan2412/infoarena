{foreach FlashMessage::getMessages() as $fm}
  <div class="flash flash-{$fm.type}">
    {$fm.text}
  </div>
{/foreach}
