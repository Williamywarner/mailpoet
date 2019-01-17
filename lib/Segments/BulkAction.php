<?php

namespace MailPoet\Segments;

use MailPoet\Listing\Handler;
use MailPoet\Models\Segment;
use MailPoet\WP\Hooks;

class BulkAction {

  private $data = null;

  function __construct($data) {
    $this->data = $data;
  }

  /**
   * @return array
   * @throws \Exception
   */
  function apply() {
    if(!isset($this->data['listing']['filter']['segment'])) {
      throw new \InvalidArgumentException('Missing segment id');
    }
    $segment = Segment::findOne($this->data['listing']['filter']['segment']);
    if($segment) {
      $segment = $segment->asArray();
    }
    return $this->applySegment($segment);
  }

  /**
   * @param array $segment
   *
   * @return array
   * @throws \Exception
   */
  private function applySegment($segment) {
    if(!$segment
      || in_array($segment['type'], [Segment::TYPE_DEFAULT, Segment::TYPE_WP_USERS, Segment::TYPE_WC_USERS], true)
    ) {
      $bulk_action = new \MailPoet\Listing\BulkActionController(new Handler());
      return $bulk_action->apply('\MailPoet\Models\Subscriber', $this->data);
    } else {
      $handlers = Hooks::applyFilters('mailpoet_subscribers_in_segment_apply_bulk_action_handlers', array());
      foreach($handlers as $handler) {
        $meta = $handler->apply($segment, $this->data);
        if($meta) {
          return $meta;
        }
      }
      throw new \InvalidArgumentException('No handler found for segment');
    }
  }

}
