module.exports = function (phantom, ready) {
    'use strict';

    // Force all lazyload images to load.  Otherwise we tend to get a few at
    // the bottom of a long page that haven't loaded yet.
    if (typeof lazySizes !== 'undefined') {
      jQuery('.lazyload').each(function(){lazySizes.loader.unveil(this);});
    }

    // make Wraith wait a bit longer before taking the screenshot
    setTimeout(ready, 2000); // you MUST call the ready() callback for Wraith to continue
}
