<?php

abstract class Report {

  abstract function getDescription(): string;
  abstract function getVariable(): string;
  abstract function getTemplateName(): string;
  abstract function getSupportedActions(): array;
  abstract function getLiveCount(): int;

  function getCachedCount(): int {
    return Variable::peek($this->getVariable(), 0);
  }

  function updateCount(): void {
    $count = $this->getLiveCount();
    Variable::poke($this->getVariable(), $count);
  }

  function getLinkName(): string {
    $className = get_class($this);
    return ReportUtil::classNameToUrlName($className);
  }

  function cleanup(): void {
  }

  function action(): void {
    $action = Request::get('report_action');

    if (!in_array($action, $this->getSupportedActions())) {
      FlashMessage::addError('Acțiune imposibilă pentru raportul curent.');
      Util::redirectToSelf();
    }

    switch ($action) {
      case 'attachment_delete':
        $attachmentId = Request::get('attachment_id');
        Attachment::deleteById($attachmentId);
        FlashMessage::addSuccess('Am șters fișierul.');
        Util::redirectToSelf();

      case 'cleanup':
        $this->cleanup();
        Util::redirectToSelf();

      case 'job_delete':
        $jobId = Request::get('job_id');
        Job::deleteById($jobId);
        FlashMessage::addSuccess(sprintf('Am șters jobul #%d.', $jobId));
        Util::redirectToSelf();

      case 'round_delete':
        $roundId = Request::get('round_id');
        Round::deleteById($roundId);
        FlashMessage::addSuccess(sprintf('Am șters runda [%s].', $roundId));
        Util::redirectToSelf();
    }
  }

}
