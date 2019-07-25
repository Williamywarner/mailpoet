<?php

namespace MailPoet\Entities;

use DateTimeInterface;
use MailPoet\Doctrine\EntityTraits\AutoincrementedIdTrait;
use MailPoet\Doctrine\EntityTraits\CreatedAtTrait;
use MailPoet\Doctrine\EntityTraits\DeletedAtTrait;
use MailPoet\Doctrine\EntityTraits\UpdatedAtTrait;

/**
 * @Entity()
 * @Table(name="scheduled_tasks")
 */
class ScheduledTaskEntity {
  use AutoincrementedIdTrait;
  use CreatedAtTrait;
  use UpdatedAtTrait;
  use DeletedAtTrait;

  const STATUS_COMPLETED = 'completed';
  const STATUS_SCHEDULED = 'scheduled';
  const STATUS_PAUSED = 'paused';
  const VIRTUAL_STATUS_RUNNING = 'running'; // For historical reasons this is stored as null in DB
  const PRIORITY_HIGH = 1;
  const PRIORITY_MEDIUM = 5;
  const PRIORITY_LOW = 10;

  /**
   * @Column(type="string", nullable=true)
   * @var string|null
   */
  private $type;

  /**
   * @Column(type="string", nullable=true)
   * @var string|null
   */
  private $status;

  /**
   * @Column(type="integer")
   * @var int
   */
  private $priority = 0;

  /**
   * @Column(type="datetimetz", nullable=true)
   * @var DateTimeInterface|null
   */
  private $scheduled_at;

  /**
   * @Column(type="datetimetz", nullable=true)
   * @var DateTimeInterface|null
   */
  private $processed_at;

  /**
   * @Column(type="text", nullable=true)
   * @var string|null
   */
  private $meta;

  /**
   * @return string|null
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param string|null $type
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * @return string|null
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param string|null $status
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * @return int
   */
  public function getPriority() {
    return $this->priority;
  }

  /**
   * @param int $priority
   */
  public function setPriority($priority) {
    $this->priority = $priority;
  }

  /**
   * @return DateTimeInterface|null
   */
  public function getScheduledAt() {
    return $this->scheduled_at;
  }

  /**
   * @param DateTimeInterface|null $scheduled_at
   */
  public function setScheduledAt($scheduled_at) {
    $this->scheduled_at = $scheduled_at;
  }

  /**
   * @return DateTimeInterface|null
   */
  public function getProcessedAt() {
    return $this->processed_at;
  }

  /**
   * @param DateTimeInterface|null $processed_at
   */
  public function setProcessedAt($processed_at) {
    $this->processed_at = $processed_at;
  }

  /**
   * @return string|null
   */
  public function getMeta() {
    return $this->meta;
  }

  /**
   * @param string|null $meta
   */
  public function setMeta($meta) {
    $this->meta = $meta;
  }
}
