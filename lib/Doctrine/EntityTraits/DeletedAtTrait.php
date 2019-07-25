<?php

namespace MailPoet\Doctrine\EntityTraits;

use DateTimeInterface;

trait DeletedAtTrait {
  /**
   * @Column(type="datetimetz", nullable=true)
   * @var DateTimeInterface|null
   */
  private $deleted_at;

  /** @return DateTimeInterface */
  public function getDeletedAt() {
    return $this->deleted_at;
  }

  public function setDeletedAt(DateTimeInterface $deleted_at) {
    $this->deleted_at = $deleted_at;
  }
}
