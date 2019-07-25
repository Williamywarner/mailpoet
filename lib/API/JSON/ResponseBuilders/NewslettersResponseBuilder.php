<?php

namespace MailPoet\API\JSON\ResponseBuilders;

use MailPoet\Entities\NewsletterEntity;
use MailPoet\Entities\NewsletterSegmentEntity;
use MailPoet\Entities\SegmentEntity;
use MailPoet\Entities\SendingQueueEntity;

class NewslettersResponseBuilder {
  const DATE_FORMAT = 'Y-m-d H:i:s';

  function build(NewsletterEntity $newsletter) {
    return [
      'id' => (string)$newsletter->getId(), // (string) for BC
      'hash' => $newsletter->getHash(),
      'subject' => $newsletter->getSubject(),
      'type' => $newsletter->getType(),
      'sender_address' => $newsletter->getSenderAddress(),
      'sender_name' => $newsletter->getSenderName(),
      'status' => $newsletter->getStatus(),
      'reply_to_address' => $newsletter->getReplyToAddress(),
      'reply_to_name' => $newsletter->getReplyToName(),
      'preheader' => $newsletter->getPreheader(),
      'body' => json_decode($newsletter->getBody()), // TODO: JSON Doctrine type?
      'sent_at' => ($sent_at = $newsletter->getSentAt()) ? $sent_at->format(self::DATE_FORMAT) : null,
      'created_at' => $newsletter->getCreatedAt()->format(self::DATE_FORMAT),
      'updated_at' => $newsletter->getUpdatedAt()->format(self::DATE_FORMAT),
      'deleted_at' => ($deleted_at = $newsletter->getDeletedAt()) ? $deleted_at->format(self::DATE_FORMAT) : null,
      'parent_id' => ($parent = $newsletter->getParent()) ? $parent->getId() : null,
      'segments' => $this->buildSegments($newsletter),
      'options' => $this->buildOptions($newsletter),
      'queue' => ($queue = $newsletter->getQueue()) ? $this->buildQueue($queue) : false, // false for BC
    ];
  }

  private function buildSegments(NewsletterEntity $newsletter) {
    return array_map(function (NewsletterSegmentEntity $newsletter_segment) {
      return $this->buildSegment($newsletter_segment->getSegment());
    }, $newsletter->getNewsletterSegments()->toArray());
  }

  private function buildOptions(NewsletterEntity $newsletter) {
    $output = [];
    foreach ($newsletter->getOptions() as $option) {
      $output[$option->getOptionField()->getName()] = $option->getValue();
    }
    return $output;
  }

  private function buildSegment(SegmentEntity $segment) {
    return [
      'id' => (string)$segment->getId(), // (string) for BC
      'name' => $segment->getName(),
      'type' => $segment->getType(),
      'description' => $segment->getDescription(),
      'created_at' => $segment->getCreatedAt()->format(self::DATE_FORMAT),
      'updated_at' => $segment->getUpdatedAt()->format(self::DATE_FORMAT),
      'deleted_at' => ($deleted_at = $segment->getDeletedAt()) ? $deleted_at->format(self::DATE_FORMAT) : null,
    ];
  }

  private function buildQueue(SendingQueueEntity $queue) {
    $task = $queue->getTask();
    return [
      'id' => (string)$queue->getId(), // (string) for BC
      'type' => $task->getType(),
      'status' => $task->getStatus(),
      'priority' => (string)$task->getPriority(), // (string) for BC
      'scheduled_at' => ($scheduled_at = $task->getScheduledAt()) ? $scheduled_at->format(self::DATE_FORMAT) : null,
      'processed_at' => ($processed_at = $task->getProcessedAt()) ? $processed_at->format(self::DATE_FORMAT) : null,
      'created_at' => $task->getCreatedAt()->format(self::DATE_FORMAT),
      'updated_at' => $task->getUpdatedAt()->format(self::DATE_FORMAT),
      'deleted_at' => ($deleted_at = $task->getDeletedAt()) ? $deleted_at->format(self::DATE_FORMAT) : null,
      'meta' => $queue->getMeta(), // TODO: meta from taks or queue ????????????
      'task_id' => (string)$queue->getTask()->getId(), // (string) for BC
      'newsletter_id' => (string)$queue->getNewsletter()->getId(), // (string) for BC
      'newsletter_rendered_body' => json_decode($queue->getNewsletterRenderedBody()), // TODO: JSON Doctrine type?
      'newsletter_rendered_subject' => $queue->getNewsletterRenderedSubject(),
      'count_total' => (string)$queue->getCountTotal(), // (string) for BC
      'count_processed' => (string)$queue->getCountProcessed(), // (string) for BC
      'count_to_process' => (string)$queue->getCountToProcess(), // (string) for BC
    ];
  }
}
