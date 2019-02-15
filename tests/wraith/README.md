## What Is It?

Wraith uses a headless browser to create screenshots of webpages on different
environments (or at different moments in time) and then creates a diff of the
two images; the affected areas are highlighted in blue.

## 1. Pre-requisites

Either:

### Lando

Use [Lando](https://github.com/lando/lando) for local development.  Lando runs
on OSX, Windows, or Linux.

### Bare metal

Use whatever technique you are familiar with (Homebrew, MAMP, etc.) to set up a
site locally. Then follow the [wraith installation instructions](http://bbc-news.github.io/wraith/os-install.html).

## 2. Configuration

Copy
  tests/visual_regression/wraith/config/example.capture-local.yaml
To
  tests/visual_regression/wraith/config/capture-local.yaml
Edit the file and follow the instructions within it.

## 3. Running

Make sure

1. Both sites are running the same database so that content differences do not
   cause test failures.
2. Configuration is in a clean state on both.
3. Any database updates have been run on both.
4. Solr is configured on both, particularly for the /academics-and-research
   pages.

Then run Wraith:

1. `cd tests/wraith`
2. Either
    * `lando wraith capture config/capture-local.yaml`
    * Or if you are using bare metal: `wraith capture config/capture-local.yaml`

The most common cause of full failures is misconfigured domains.

## 4. Review Results

In your browser visit
  <local domain>/wraith-shots/gallery.html

Wraith is configured in capture.yaml to operate in diffs_only mode, meaning only
paths with a difference are shown.  Therefore, all results should be examined
for unintended changes.

## Known Issues

* JavaScript does not run on (even though it runs fine in Safari
  and Chrome), so you should test these manually:
  * homepage
  * /academics-research
  * /dean
  * /staff
* False positives
    * Sometimes images on a local site will show as half loaded.  This is
      because Stage File Proxy is still downloading the image. Re-run the test
      to see better results.
    * The homepage Academics and Research Randomizer shows a random list of
      programs/departments so will always show a false positive.
    * Styleguide tests fail because of differences in video/image rendering.
    * Search results may fail due to minor sorting differences ¯\_(ツ)_/¯ .
* The diffing on the components styleguide test (and other very long pages) cuts
  off after a certain height due to https://github.com/BBC-News/wraith/issues/318
* Things we don't currently test:
    * Admin screens
    * Any user interaction (e.g. clicking menus, submitting forms, scrolling),
      though with some additions to the 'before_capture' variable in
      capture.yaml could be made to do so.
