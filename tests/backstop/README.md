# What Is It?

BackstopJS uses a headless browser to create screenshots of webpages on different
environments (or at different moments in time) and then creates a diff of the
two images; the changed areas are highlighted.

# 1. Pre-requisites

Either:

## Lando

Use [Lando](https://github.com/lando/lando) for local development.  Lando runs
on OSX, Windows, or Linux.

## Bare metal

Installing on bare metal is theoretically possible, but will probably result in more false positives because your version of the headless browser will render slightly differently than the Lando Docker one.  Honestly it will be less effort to just setup Lando, even if you don't go so far as copying down a database.

Use whatever technique you are familiar with (Homebrew, MAMP, etc.) to set up a
site locally. Then follow the [BackstopJS installation instructions](https://github.com/garris/BackstopJS/).

In the examples below you'd then run `backstop ...` instead of `lando backstop ...`

# 2. Configuration

TBD a config script.  Until then:

1. `cd tests/backstop`
2. `cp backstop.json backstop-local.json`
3. Edit `backstop-local.json`.  Change each scenario to use the correct domain (usually your multisite domain) for `url` (ignore `referenceUrl` for now).

# 3. Run the quick test

`lando backstop test`

# 4. Review the results

In your browser visit either

* http://<lando-domain>/backstop/html_report/index.html
* file://<path-to-app-root>/web/backstop/html_report/index.html Note the three leading slashes.  (e.g. file:///Users/me/Sites/project/web/backstop/html_report/index.html)

If you see no differences, you're done.

If the differences are:
* Intentionally caused by your code changes (move on to the next step about updating the reference)
* Unintentionally caused by a bug in your code changes. Either:
  * fix the code (You can run a single scenario with `--filter=<scenarioLabelRegex>`)
  * agree with the team to live with it (move on to the next step about updating the reference)
* Obviously because of changed content (move on to the next step about updating the reference)
* Possibly because of changed content, but you're not sure (skip to the last step about running the full test)

# 5. Update the reference snapshots

You want to save your test results as the new "reference".  Future tests will be compared against this new reference.

`lando backstop approve`

If you want to save only _some_ of your test results (e.g. you wouldn't want to save snapshots of Solr-powered pages if Solr has not been configured) use the format

`lando backstop approve --filter=<image_filename_regex>`

You're done.

# 6. Run the full test

## Config

3. Edit `backstop-local.json`.  Change each scenario to use the correct domain (usually the Pantheon dev or test domain) for `referenceUrl`.  `url` should already be correctly configured from your quick test.

## Prep

Make sure

1. Both sites are running the same database so that content differences do not
   cause test failures.
2. Configuration is in a clean state on both.
3. Any database updates have been run on both.
4. Solr is configured, and fully indexed on both, particularly for the site-wide search, or any Solr-powered views (e.g. HumSci Academics & Research).

## Capture screenshots and compare

1. `lando backstop reference`
2. Return to [# 3. Run the quick test]

# Known Issues

* False positives
    * Sometimes if you are testing a local site (not a Pantheon environment) images will show as half loaded.  This is
      because Stage File Proxy is still downloading the image. Re-run the test
      to see better results.
    * Styleguide tests fail because of differences in video/image rendering.
    * Search results may fail due to minor sorting differences ¯\_(ツ)_/¯ .
* Things we don't currently test:
    * Admin screens
    * Much user interaction (e.g. clicking menus, submitting forms, scrolling),
      To add more, see the [BackstopJS Advanced Scenarios](https://github.com/garris/BackstopJS/#advanced-scenarios).
