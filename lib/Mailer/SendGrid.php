<?php
namespace MailPoet\Mailer;

if(!defined('ABSPATH')) exit;

class SendGrid {
  function __construct($api_key, $from_email, $from_name, $newsletter,
    $subscribers) {
    $this->url = 'https://api.sendgrid.com/api/mail.send.json';
    $this->api_key = $api_key;
    $this->newsletter = $newsletter;
    $this->subscribers = $subscribers;
    $this->from = sprintf('%s <%s>', $from_name, $from_email);
  }

  function send() {
    $result = wp_remote_post(
      $this->url,
      $this->request()
    );
    if(is_object($result) && get_class($result) === 'WP_Error') return false;
    $result = json_decode($result['body'], true);
    return (isset($result['errors']) === false);
  }

  function getSubscribers() {
    $subscribers = array_map(function ($subscriber) {
      if(!isset($subscriber['email'])) return;
      $first_name = (isset($subscriber['first_name']))
        ? $subscriber['first_name'] : '';
      $last_name = (isset($subscriber['last_name']))
        ? $subscriber['last_name'] : '';
      $subscriber = sprintf(
        '%s %s <%s>', $first_name, $last_name, $subscriber['email']
      );
      $subscriber = trim(preg_replace('!\s\s+!', ' ', $subscriber));
      return $subscriber;
    }, $this->subscribers);
    return array_filter($subscribers);
  }

  function getBody() {
    $parameters = array(
      'to' => $this->from,
      'from' => $this->from,
      'x-smtpapi' => json_encode(array('to' => $this->getSubscribers())),
      'subject' => $this->newsletter['subject'],
      'html' => $this->newsletter['body']
    );
    return urldecode(http_build_query($parameters));
  }

  function auth() {
    return 'Bearer ' . $this->api_key;
  }

  function request() {
    return array(
      'timeout' => 10,
      'httpversion' => '1.1',
      'method' => 'POST',
      'headers' => array(
        'Authorization' => $this->auth()
      ),
      'body' => $this->getBody()
    );
  }
}