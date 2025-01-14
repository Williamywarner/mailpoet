<?php

namespace MailPoet\Test\Subscribers\ImportExport\Export;

use MailPoet\WP\Functions as WPFunctions;
use MailPoet\Models\CustomField;
use MailPoet\Models\Segment;
use MailPoet\Models\Subscriber;
use MailPoet\Models\SubscriberCustomField;
use MailPoet\Subscribers\ImportExport\Export\DynamicSubscribersGetter;

class DynamicSubscribersGetterTest extends \MailPoetTest {
  function _before() {
    parent::_before();
    $this->subscriber_fields = [
      'first_name' => 'First name',
      'last_name' => 'Last name',
      'email' => 'Email',
      1 => 'Country',
    ];

    $this->subscribers_data = [
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
      ],
      [
        'first_name' => 'Mary',
        'last_name' => 'Jane',
        'email' => 'mary@jane.com',
        'status' => Subscriber::STATUS_SUBSCRIBED,
        1 => 'Brazil',
      ],
      [
        'first_name' => 'John',
        'last_name' => 'Kookoo',
        'email' => 'john@kookoo.com',
      ],
      [
        'first_name' => 'Paul',
        'last_name' => 'Newman',
        'email' => 'paul@newman.com',
      ],
    ];

    $this->custom_fields_data = [
      [
        'name' => 'Country',
        'type' => 'text',
      ],
    ];

    $this->segments_data = [
      [
        'name' => 'Newspapers',
      ],
      [
        'name' => 'Journals',
      ],
    ];

    foreach ($this->subscribers_data as $subscriber) {
      if (isset($subscriber[1])) {
        unset($subscriber[1]);
      }
      $entity = Subscriber::create();
      $entity->hydrate($subscriber);
      $entity->save();
    }

    foreach ($this->custom_fields_data as $custom_field) {
      $entity = CustomField::create();
      $entity->hydrate($custom_field);
      $entity->save();
    }

    foreach ($this->segments_data as $segment) {
      $entity = Segment::create();
      $entity->hydrate($segment);
      $entity->save();
    }

    $entity = SubscriberCustomField::create();
    $entity->subscriber_id = 2;
    $entity->custom_field_id = 1;
    $entity->value = $this->subscribers_data[1][1];
    $entity->save();
    $wp = new WPFunctions;
    $wp->removeAllFilters('mailpoet_get_segment_filters');
    $wp->addAction(
      'mailpoet_get_segment_filters',
      function($segment_id) {
        if ($segment_id == 1) {
          return [new \DynamicSegmentFilter([1, 2])];
        } else if ($segment_id == 2) {
          return [new \DynamicSegmentFilter([1, 3, 4])];
        }
        return [];
      }
    );
  }

  protected function filterSubscribersData($subscribers) {
    return array_map(function($subscriber) {
      $data = [];
      foreach ($subscriber as $key => $value) {
        if (in_array($key, [
          'first_name', 'last_name', 'email', 'global_status',
          'status', 'list_status', 'segment_name', 1,
        ]))
          $data[$key] = $value;
      }
      return $data;
    }, $subscribers);
  }

  function testItGetsSubscribersInOneSegment() {
    $getter = new DynamicSubscribersGetter([1], 10);
    $subscribers = $getter->get();
    expect($this->filterSubscribersData($subscribers))->equals([
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Newspapers',
        1 => null,
      ],
      [
        'first_name' => 'Mary',
        'last_name' => 'Jane',
        'email' => 'mary@jane.com',
        'status' => Subscriber::STATUS_SUBSCRIBED,
        'global_status' => Subscriber::STATUS_SUBSCRIBED,
        'list_status' => Subscriber::STATUS_SUBSCRIBED,
        'segment_name' => 'Newspapers',
        1 => 'Brazil',
      ],
    ]);

    expect($getter->get())->equals(false);
  }

  function testItGetsSubscribersInMultipleSegments() {
    $getter = new DynamicSubscribersGetter([1, 2], 10);
    expect($this->filterSubscribersData($getter->get()))->equals([
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Newspapers',
        1 => null,
      ],
      [
        'first_name' => 'Mary',
        'last_name' => 'Jane',
        'email' => 'mary@jane.com',
        'status' => Subscriber::STATUS_SUBSCRIBED,
        'global_status' => Subscriber::STATUS_SUBSCRIBED,
        'list_status' => Subscriber::STATUS_SUBSCRIBED,
        'segment_name' => 'Newspapers',
        1 => 'Brazil',
      ],
    ]);

    expect($this->filterSubscribersData($getter->get()))->equals([
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
      [
        'first_name' => 'John',
        'last_name' => 'Kookoo',
        'email' => 'john@kookoo.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
      [
        'first_name' => 'Paul',
        'last_name' => 'Newman',
        'email' => 'paul@newman.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
    ]);

    expect($getter->get())->equals(false);
  }

  function testItGetsSubscribersInBatches() {
    $getter = new DynamicSubscribersGetter([1, 2], 2);
    expect($this->filterSubscribersData($getter->get()))->equals([
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Newspapers',
        1 => null,
      ],
      [
        'first_name' => 'Mary',
        'last_name' => 'Jane',
        'email' => 'mary@jane.com',
        'status' => Subscriber::STATUS_SUBSCRIBED,
        'global_status' => Subscriber::STATUS_SUBSCRIBED,
        'list_status' => Subscriber::STATUS_SUBSCRIBED,
        'segment_name' => 'Newspapers',
        1 => 'Brazil',
      ],
    ]);

    expect($this->filterSubscribersData($getter->get()))->equals([]);

    expect($this->filterSubscribersData($getter->get()))->equals([
      [
        'first_name' => 'Adam',
        'last_name' => 'Smith',
        'email' => 'adam@smith.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
      [
        'first_name' => 'John',
        'last_name' => 'Kookoo',
        'email' => 'john@kookoo.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
    ]);

    expect($this->filterSubscribersData($getter->get()))->equals([
      [
        'first_name' => 'Paul',
        'last_name' => 'Newman',
        'email' => 'paul@newman.com',
        'status' => Subscriber::STATUS_UNCONFIRMED,
        'global_status' => Subscriber::STATUS_UNCONFIRMED,
        'list_status' => Subscriber::STATUS_UNCONFIRMED,
        'segment_name' => 'Journals',
        1 => null,
      ],
    ]);

    expect($getter->get())->equals(false);
  }

  function _after() {
    \ORM::raw_execute('TRUNCATE ' . Subscriber::$_table);
    \ORM::raw_execute('TRUNCATE ' . Segment::$_table);
    \ORM::raw_execute('TRUNCATE ' . CustomField::$_table);
    \ORM::raw_execute('TRUNCATE ' . SubscriberCustomField::$_table);
  }
}
