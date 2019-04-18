<?php

// The environment the current site is running on.
// Possible values: local, dev, test, live.
// Configuration further in this file sets different settings for different
// environments.
if (defined('PANTHEON_ENVIRONMENT')) {
  if (isset($_SERVER['PRESSFLOW_SETTINGS'])) {
    // This is only set for web, not CLI PHP.
    // This can only happen at the top of settings.php because it
    // recreates $conf.
    extract(json_decode($_SERVER['PRESSFLOW_SETTINGS'], TRUE));
  }
  switch (PANTHEON_ENVIRONMENT) {
    case 'kalabox':
    case 'lando':
      $config['server_environment'] = 'local';
      break;
    case 'dev':
    case 'test':
    case 'live':
      $config['server_environment'] = PANTHEON_ENVIRONMENT;
      break;
    // Multidevs.
    default:
      $config['server_environment'] = 'dev';
  }
}
else {
  $config['server_environment'] = 'local';
}

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';
if ($config['server_environment'] == 'local') {
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
  $settings['skip_permissions_hardening'] = TRUE;
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
  // Front-end devs will want to include the following line in a settings.local.php
  // Back-end devs should _not_ disable render caching because it will hide
  // bugs with cache contexts/tags.
  # $settings['cache']['bins']['render'] = 'cache.backend.null';
}

if ($config['server_environment'] != 'local') {
  $settings['trusted_host_patterns'] = array(
    '^www\.example\.com$',
    'example\.com$',
    '^[^.]+\.pantheonsite\.io$',
  );
}

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Live site redirects.
if ($config['server_environment'] == 'live' && php_sapi_name() != "cli") {
  $host_parts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

  // Require www.
  if (!isset($host_parts[2])) {
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
  }

  // ...
}

// Get rid of the thousands of attempted WP hacks from the logs.
if (
  php_sapi_name() != 'cli'
  && !empty($_SERVER["REQUEST_URI"])
  && ($request_9 = substr($_SERVER["REQUEST_URI"], 0, 9))
  && in_array($request_9, ['/wp-conte', '/wp-admin', '/wp-login', '/wp-post.', '/wp-inclu'])
) {
  header($_SERVER["SERVER_PROTOCOL"]." 418 I'm a teapot");
  echo 'I\'m a teapot.';
  exit();
}

// Require HTTPS.
if (
  $config['server_environment'] != 'local' &&
  php_sapi_name() != "cli"
) {
  if (
    empty($_SERVER['HTTPS']) && empty($_SERVER['HTTP_X_SSL']) ||
    isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) != 'ON' ||
    isset($_SERVER['HTTP_X_SSL']) && strtoupper($_SERVER['HTTP_X_SSL']) != 'ON'
  ) {
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
  }
}

// Error settings.
switch($config['server_environment']) {
  case 'live':
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    $config['system.logging']['error_level'] = 'hide';
    ini_set('display_errors', FALSE);
    break;

  case 'test':
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    $config['system.logging']['error_level'] = 'some';
    ini_set('display_errors', TRUE);
    break;

  case 'local':
    ini_set('error_reporting', E_ALL | E_STRICT);
    $config['system.logging']['error_level'] = 'verbose';
    ini_set('display_errors', TRUE);
    break;

  case 'dev':
  default:
    ini_set('error_reporting', E_ALL | E_STRICT);
    $config['system.logging']['error_level'] = 'all';
    ini_set('display_errors', TRUE);
    break;
}

// Redis.
if (class_exists('Redis')) {

  // Include the Redis services.yml file.
  $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

  // PhpRedis is built into the Pantheon application container.
  $settings['redis.connection']['interface'] = 'PhpRedis';
  // These are dynamic variables handled by Pantheon.
  $settings['redis.connection']['host'] = '127.0.0.1';
  if (!empty($_ENV['CACHE_HOST'])) {
    $settings['redis.connection']['host'] = $_ENV['CACHE_HOST'];
  }
  $settings['redis.connection']['port'] = 6379;
  if (!empty($_ENV['CACHE_PORT'])) {
    $settings['redis.connection']['port'] = $_ENV['CACHE_PORT'];
  }
  if (!empty($_ENV['CACHE_PASSWORD'])) {
    $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];
  }

  // The default prefix is based on path to the webroot, which on Pantheon
  // changes from container to container.  So hard-code it, but use the version
  // prefix as in the default, so that upgrades cause new cache keys.  But the
  // autoloader isn't set up yet, so manually include the right file.
  require_once(DRUPAL_ROOT . '/core/lib/Drupal.php');
  $settings['cache_prefix']['default'] = DRUPAL::VERSION . ':';

  $settings['cache']['default'] = 'cache.backend.redis'; // Use Redis as the default cache.

  // Never use Redis for the form bin.  Otherwise people will loose form
  // submissions when the cache is cleared from the Pantheon Dashboard, or with
  // `terminus env:clear-cache`
  $settings['cache']['bins']['form'] = 'cache.backend.database';

  // Always set the fast backend for bootstrap, discover and config, otherwise this gets lost when redis is enabled.
  $settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
  $settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
  $settings['cache']['bins']['config']    = 'cache.backend.chainedfast';
}

// Stage File Proxy origin.
if ($config['server_environment'] == 'live') {
  $config['stage_file_proxy.settings']['origin'] = '';
}
elseif ($config['server_environment'] == 'local') {
  $config['stage_file_proxy.settings']['origin'] = 'https://dev-example.pantheonsite.io';
}
else {
  $config['stage_file_proxy.settings']['origin'] = 'https://www.example.com';
}

// Don't send e-mails from dev or staging sites.
// Feel free to comment this out if the site does not send out any email 
// reminders.
if ($config['server_environment'] != 'live') {
  $config['mailsystem.settings']['defaults']['sender'] = 'devel_mail_log';
  $config['mailsystem.settings']['defaults']['formatter'] = 'devel_mail_log';
}

// Ignore CLI Commands (usually drush cron) from New Relic.
$current_path = explode('/', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
if (function_exists('newrelic_ignore_transaction')) {
  if (
    php_sapi_name() == "cli"
    || isset($current_path[3]) && $current_path[3] == 'run-cron' && $current_path[2] = 'status' && $current_path[1] == 'reports'
    || isset($current_path[0]) && $current_path[0] == 'cron'
    || isset($_SERVER["SCRIPT_NAME"]) && strpos($_SERVER["SCRIPT_NAME"], 'cron.php') !== FALSE
  ) {
    newrelic_ignore_transaction(TRUE);
  }
}

// Use development config in dev environments.
if (in_array($config['server_environment'], ['live', 'test'])) {
  $config['config_split.config_split.config_dev']['status'] = FALSE;
}
else {
  $config['config_split.config_split.config_dev']['status'] = TRUE;
}

// Get rid of the thousands of attempted WP hacks from the logs.
if (
  php_sapi_name() != 'cli'
  && !empty($_SERVER["REQUEST_URI"])
  && ($request_9 = substr($_SERVER["REQUEST_URI"], 0, 9))
  && in_array($request_9, array('/wp-conte', '/wp-admin', '/wp-login', '/wp-post.'))
) {
  header($_SERVER["SERVER_PROTOCOL"]." 418 I'm a teapot");
  echo 'I\'m a teapot.';
  exit();
}

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
