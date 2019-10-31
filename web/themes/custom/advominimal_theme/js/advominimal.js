/**
 * @file
 * A JavaScript file for the theme, for script that should be loaded globally.
 * Anything component-based, or code that is only called contextually
 * should go in a separate JS file... as should any code limited to specific parts
 * of the page that are complicated and deserve their own file (to avoid making this file massive).
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 *
 * Utilize Drupal behaviors. They are applied consistently when the page is first loaded and
 * then when new content is added during AHAH/AJAX requests.
 *
 * JavaScript API overview: https://www.drupal.org/docs/8/api/javascript-api/javascript-api-overview
 *
 * Avoid processing the same element multiple times within Drupal behaviors.
 * Use the jQuery Once plugin that's bundled with core:
 * $('.foo', context).once('foo').css('color'a, 'red');
 *
 * Practice DRY techniques:
 * https://learn.jquery.com/code-organization/dont-repeat-yourself
 * https://brandoncodes.wordpress.com/2012/06/29/making-some-simple-jquery-code-more-efficient
 *
 * Use helper functions to further DRY up your code:
 * http://joecritchley.com/articles/dry-chaining-jquery
 *
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.tableDragA11y = {
    attach: function (context) {

      $('.tabledrag-handle', context).once().each(function() {
        // Remove &nbsp and insert visually hidden help text for screenreaders.
        $(this).find('.handle').html(function (i, html) {
          return html.replace(/&nbsp;/g, '');
        }).prepend('<span class="visually-hidden">Use arrow keys to move row up, down, or indented within others via left/right.</span>');
      });

    },
  };

  Drupal.behaviors.ariaA11y = {
    attach: function (context) {

      $('.form-wrapper *[aria-describedby]', context).once().each(function() {

        var targetID = $(this).attr('aria-describedby');
        var $target = $(this).closest('form-wrapper').find('*#' + targetID);

        // If there's no matching IDed element, just remove the aria-describedby since there's no reliable descriptive alternative.
        if (!$target.length) {
          $(this).removeAttr('aria-describedby');
        }

      });

    },
  };

  Drupal.behaviors.labelsA11y = {
    attach: function (context) {

      // menu_parent_form_ui label insertion/override.
      $('.form-item-menu-menu-parent', context).once().each(function() {

        var $target = $(this).find('div > select');
        var targetLabelText = $target.parent().siblings('label').text();
        var targetID = $target.attr('id');

        $target.before('<label for="' + targetID + '" class="visually-hidden">' + targetLabelText + '</label>');

      });

      // "No label" form elements that actually have no label.
      $('.form-type-select.form-no-label:not(:has(label))').once().each(function() {

        var $target = $(this).find('select');
        var targetID = $target.attr('id');
        var targetLabelText = $(this).next('.form-submit').attr('value');

        $target.before('<label for="' + targetID + '" class="visually-hidden">' + targetLabelText + '</label>');

      });

      // select2
      $('.select2').once().each(function(index) {

        var $target = $(this).find('input[type=search]');
        var targetID = $target.attr('id');
        var targetLabelText = $target.closest('.form-type-select').find('label').text();

        // Set an ID for search fields that don't have them.
        if ($target.length) {
          $target.attr("id", "search2_select_" + index);
          targetID = $target.attr('id');
        }

        $target.before('<label for="' + targetID + '" class="visually-hidden">' + targetLabelText + '</label>');

      });

    },
  };

})(jQuery, Drupal);
