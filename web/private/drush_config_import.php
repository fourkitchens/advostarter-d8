<?php

if (function_exists('newrelic_ignore_transaction')) {
  newrelic_ignore_transaction(TRUE);
}

echo "Clearing cache...\n";
passthru('drush cr ');
echo "Clearing cache complete.\n";

echo "Importing configuration from yml files...\n";
passthru('drush config-import -y');
echo "Import of configuration complete.\n";