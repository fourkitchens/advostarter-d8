<?php
require_once 'slack_api.inc';
require_once 'get_secrets.inc';

// Important constants :)
$pantheon_yellow = '#EFD01B';

// Default values for parameters - this will assume the channel you define the webhook for.
// The full Slack Message API allows you to specify other channels and enhance the messagge further
// if you like: https://api.slack.com/docs/messages/builder
$defaults = array(
  'slack_username' => 'Pantheon-Quicksilver',
  'always_show_text' => false,
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('slack_url'), $defaults);

// Don't be so chatty.  Only notify about deploys to the live env.
if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
  echo "Don't be so chatty, only notify about deploys on the live env.\n";
  exit(0);
}

// Build an array of fields to be rendered with Slack Attachments as a table
// attachment-style formatting:
// https://api.slack.com/docs/attachments
$fields = array();

// Customize the message based on the workflow type.  Note that slack_notification.php
// must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch($_POST['wf_type']) {
  case 'deploy':
    // Find out what tag we are on and get the annotation.
    $deploy_tag = `git describe --tags`;

    // Prepare the slack payload as per:
    // https://api.slack.com/incoming-webhooks
    $text = 'Deploy to the '. $_ENV['PANTHEON_ENVIRONMENT'];
    $text .= ' environment of '. $_ENV['PANTHEON_SITE_NAME'] .' by '. $_POST['user_email'] .' complete!';
    $text .= ' <https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>';
    // Build an array of fields to be rendered with Slack Attachments as a table
    // attachment-style formatting:
    // https://api.slack.com/docs/attachments
    break;

  case 'sync_code':
    // Get the committer, hash, and message for the most recent commit.
    $committer = `git log -1 --pretty=%cn`;
    $email = `git log -1 --pretty=%ce`;
    $message = `git log -1 --pretty=%B`;
    $hash = `git log -1 --pretty=%h`;

    // Prepare the slack payload as per:
    // https://api.slack.com/incoming-webhooks
    $text = 'Code sync to the ' . $_ENV['PANTHEON_ENVIRONMENT'] . ' environment of ' . $_ENV['PANTHEON_SITE_NAME'] . ' by ' . $_POST['user_email'] . "!\n";
    $text .= 'Most recent commit: ' . rtrim($hash) . ' by ' . rtrim($committer) . ': ' . $message;
    // Build an array of fields to be rendered with Slack Attachments as a table
    // attachment-style formatting:
    // https://api.slack.com/docs/attachments
    $fields += array(
      array(
        'title' => 'Commit',
        'value' => rtrim($hash),
        'short' => 'true'
      ),
      array(
        'title' => 'Commit Message',
        'value' => $message,
        'short' => 'false'
      )
    );
    break;

  case 'clear_cache':
    $fields[] = array(
      'title' => 'Cleared caches',
      'value' => 'Cleared caches on the ' . $_ENV['PANTHEON_ENVIRONMENT'] . ' environment of ' . $_ENV['PANTHEON_SITE_NAME'] . "!\n",
      'short' => 'false'
    );
    break;

  default:
    $text = $_POST['qs_description'];
    break;
}

$email = $_POST['user_email'];
$email_name = array_shift(explode('@', $email));
$author = $email ? ":$email_name: $email" : "";

$attachment = array(
  'fallback' => $text,
  'pretext' => ($_POST['wf_type'] == 'clear_cache') ? 'Caches cleared :construction:' : 'Deploying :rocket:',
  'color' => $pantheon_yellow, // Can either be one of 'good', 'warning', 'danger', or any hex color code
  'author_name' => $author,
  'title' => $_ENV['PANTHEON_SITE_NAME'],
  'title_link' => 'http://' . PANTHEON_ENVIRONMENT . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io',
  'text' => $_POST['deploy_message'] ."\n<https://dashboard.pantheon.io/sites/" . PANTHEON_SITE . '#' . PANTHEON_ENVIRONMENT . "/deploys|Dashboard>",
  'footer' => 'Pantheon',
  'footer_icon' => 'https://pantheon.io/sites/default/files/favicon_2.ico',
  'fields' => $fields,
);

_slack_notification($secrets['slack_url'], $secrets['slack_channel'], $secrets['slack_username'], $text, $attachment, $secrets['always_show_text']);
