<?php

if (function_exists('newrelic_ignore_transaction')) {
  newrelic_ignore_transaction(TRUE);
}

echo "Marking all content for reindexing...\n";
echo "Note: this will only work in a multi-dev if the following patch is applied:\n"
echo "https://github.com/pantheon-systems/search_api_pantheon/pull/53\n"
passthru('drush search-api-reindex -y');
echo "All content marked for reindexing.\n";

echo "Indexing content...\n";
passthru('drush search-api-index -y');
echo "All content indexed.\n");
