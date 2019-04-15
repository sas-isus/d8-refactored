import createPermission from '../async-function/create-permission';
import fetchFromBackend from '../async-function/fetch-from-backend';
import DomClient from '../client/dom-client.prototype';
import PermissionOutput from '../model/permission-output.prototype';
import PermissionOutputCollector from '../client/permission-output-collector.prototype';
import TermCollector from "../client/term-collector.prototype";
import DocumentAdapter from "../adapter/document-adapter.prototype";

(($) => {

  'use strict';

  if (document.querySelector("#edit-permissions-by-term-info") !== null) {

    /**
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.nodeForm = {
      attach: async () => {
        /**
         * @var Backend backend
         */
        let backend = await createPermission(fetchFromBackend);

        const hasTaxonomyFormFields = (permissions) => {
          if (permissions.taxonomyRelationFieldNames.length !== 0) {
            return true;
          }

          return false;
        }

        if (hasTaxonomyFormFields(backend)) {

          const processPermissionsDisplay = () => {

            const permissionOutput = new PermissionOutput,
                termCollector = new TermCollector,
                documentAdapter = new DocumentAdapter(document),
                domClient = new DomClient(documentAdapter, permissionOutput, Drupal);

            const permissionOutputCollector = new PermissionOutputCollector(permissionOutput);

            for (let formElementCssClass of backend.getFieldWrapperCSSClasses()) {
              termCollector.addSelectedTids(domClient.computeTids(formElementCssClass));

              permissionOutputCollector.collect(backend, termCollector.getSelectedTids());
            }

            domClient.renderPermissionsInfo();
          }

          for (let formElementCssClass of backend.getFieldWrapperCSSClasses()) {

            $(formElementCssClass + ' input[type="text"]').on('autocomplete-select', () => {
              processPermissionsDisplay();
            });

            $(formElementCssClass + ' select').change(function (){
              processPermissionsDisplay();
            });

            $(formElementCssClass + ' input[type="text"]').on('keyup', function (){
              processPermissionsDisplay();
            });

            $(formElementCssClass + ' input[type="checkbox"]').change(function (){
              processPermissionsDisplay();
            });

          }

        };

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

        // Return false to tell jQuery UI that we've filled in the value
        // already.
        return false;
      }
    }

  }

})(jQuery);
