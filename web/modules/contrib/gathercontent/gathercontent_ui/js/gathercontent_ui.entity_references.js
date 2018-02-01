/**
 * @file
 * Client side manipulation with mapping form.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.gathercontentFieldChanged = {
    attach: function (context, settings) {
      $(context).find('.gathercontent-ct-element').on(
        'change', function () {
          // On select change we want to validate if entity Reference field is selected. If no, we want to hide
          // mapping options, otherwise we want to show them.
          Drupal.gatherContent.toggleErMappingType(settings);
        }
      );

      if (typeof settings.gathercontent !== 'undefined') {
        // On page init we want to hide mapping options, otherwise we want to show them.
        Drupal.gatherContent.toggleErMappingType(settings);
      }
    }
  };

  Drupal.gatherContent = Drupal.gatherContent || {};

  Drupal.gatherContent.toggleErMappingType = function (settings) {
    var isErMapped = false;
    $('.gathercontent-ct-element').each(
      function () {
        if (jQuery.inArray($(this).val(), settings.gathercontent) !== -1) {
          isErMapped = true;
          return false;
        }
      }
    );

    $('.gathercontent-er-mapping-type').toggle(isErMapped);
  };

}(jQuery, Drupal));
