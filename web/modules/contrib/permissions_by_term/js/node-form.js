/**
 * @file
 * Info behaviors on node edit form.
 */

(function ($, window) {

  'use strict';

  if ($("#edit-permissions-by-term-info").length > 0) {

    var relationFieldsPathByContentType = "/admin/permissions-by-term/access-info-by-content-type/",
      relationFieldsPathByUrl = "/admin/permissions-by-term/access-info-by-url?url=";

    /**
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.nodeForm = {
      attach: function () {

        var contentType = getContentType(),
            getFormInfo = null;

        if (contentType !== null) {
          getFormInfo = $.get(relationFieldsPathByContentType + contentType);
        } else {
          getFormInfo = $.get(relationFieldsPathByUrl + window.location.pathname);
        }

        $.when(getFormInfo).done(function(formInfo){

          if (formInfo['taxonomyRelationFieldNames'] !== null) {

            var nodeForm = new NodeForm($),
                fieldWrapperCSSClasses = nodeForm.computeFieldWrapperCSSClasses(formInfo['taxonomyRelationFieldNames']);

            initPermissionInfoByFormElements(nodeForm, fieldWrapperCSSClasses, formInfo);

            for (var index = 0; index < fieldWrapperCSSClasses.length; ++index) {

              var formElementCssClass = fieldWrapperCSSClasses[index];

              nodeForm.addFormElementCssClass(formElementCssClass);

              $(formElementCssClass + ' select').change(function (){
                nodeForm.displayPermissionsBySelect(fieldWrapperCSSClasses, formInfo['permissions']);
              });

              $(formElementCssClass + ' input[type="text"]').on('autocomplete-select', function (){
                nodeForm.displayPermissionsByAutocomplete(fieldWrapperCSSClasses, formInfo['permissions']);
              });

              $(formElementCssClass + ' input[type="text"]').on('keyup', function (){
                nodeForm.displayPermissionsByAutocomplete(fieldWrapperCSSClasses, formInfo['permissions']);
              });

              $(formElementCssClass + ' input[type="checkbox"]').change(function (){
                nodeForm.displayPermissionsByCheckbox($(this).prop('value'), $(this).prop('checked'), formInfo['permissions']);
              });
            }
          }

        });

        function initPermissionInfoByFormElements(nodeForm, fieldWrapperCSSClasses, formInfo) {
          nodeForm.displayPermissionsBySelect(fieldWrapperCSSClasses, formInfo['permissions']);
          nodeForm.displayPermissionsByAutocomplete(fieldWrapperCSSClasses, formInfo['permissions']);
          nodeForm.displayPermissionsByCheckbox($(this).prop('value'), $(this).prop('checked'), formInfo['permissions']);
        }

        function getContentType() {
          if (window.location.href.indexOf('/node/add') !== -1) {
            return window.location.href.split("/").pop();
          }

          return null;
        }

      }
    };

    if (Drupal.autocomplete) {
      /**
       * Handles an auto-complete select event.
       *
       * Override the autocomplete method to add a custom event. Overriding is
       * happening to get full input.
       *
       * @param {jQuery.Event} event
       *   The event triggered.
       * @param {object} ui
       *   The jQuery UI settings object.
       *
       * @return {boolean}
       *   Returns false to indicate the event status.
       */
      Drupal.autocomplete.options.select = function selectHandler(event, ui) {
        var terms = Drupal.autocomplete.splitValues(event.target.value);
        // Remove the current input.
        terms.pop();
        // Add the selected item.
        if (ui.item.value.search(',') > 0) {
          terms.push('"' + ui.item.value + '"');
        }
        else {
          terms.push(ui.item.value);
        }
        event.target.value = terms.join(', ');
        // Fire custom event that other controllers can listen to.
        jQuery(event.target).trigger('autocomplete-select');

        // Return false to tell jQuery UI that we've filled in the value already.
        return false;
      }
    }
  }

})(jQuery, window);
