/**
 * @file
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.gatherContentCounter = {
    attach: function (context) {
      Drupal.gatherContent.counterUpdateSelectedMessage();
      $(Drupal.gatherContent.counterCountedSelector, context).once('gather-content-counter').on('change', Drupal.gatherContent.onCountedChanged);
    }
  };

  Drupal.gatherContent = Drupal.gatherContent || {};

  Drupal.gatherContent.counterCountedSelector = '.gather-content-counted';
  Drupal.gatherContent.counterMessageWrapperSelector = '.gather-content-counter-message';

  Drupal.gatherContent.onCountedChanged = function () {
    Drupal.gatherContent.counterUpdateSelectedMessage();
  };

  Drupal.gatherContent.counterUpdateSelectedMessage = function () {
    var count = $(Drupal.gatherContent.counterCountedSelector + ':checked').length;
    var msg = Drupal.t('There is no selected template');
    if (count !== 0) {
      msg = Drupal.formatPlural(
        count,
        '1 template selected',
        '@count templates selected'
      );
    }

    $(Drupal.gatherContent.counterMessageWrapperSelector).text(msg);
  };

})(jQuery, Drupal);
