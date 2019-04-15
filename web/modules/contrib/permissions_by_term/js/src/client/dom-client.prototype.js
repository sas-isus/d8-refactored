const get = require('lodash/get');
const includes = require('lodash/includes');
const isEmpty = require('lodash/isEmpty');

/**
 * @param documentAdapter   documentAdapter
 * @param PermissionsOutput permissionsOutput
 * @param Drupal            drupal
 */
let DomClient = function(documentAdapter, permissionsOutput, drupal) {
  this.documentAdapter = documentAdapter;
  this.drupal = drupal;
  this.permissionsOutput = permissionsOutput;
}

DomClient.prototype.renderPermissionsInfo = function() {

  let allowedUsersHtml = '<b>' + this.drupal.t('Allowed users:') + '</b> ';

  if (!isEmpty(this.permissionsOutput.getUsernames())) {
    allowedUsersHtml += this.permissionsOutput.getUsernames().join(', ');
  } else {
    allowedUsersHtml += '<i>' + this.drupal.t('No user restrictions.') + '</i>';
  }

  let allowedRolesHtml = '<b>' + this.drupal.t('Allowed roles:') + '</b> ';

  if (!isEmpty(this.permissionsOutput.getRoles())) {
    allowedRolesHtml += this.permissionsOutput.getRoles().join(', ');
  } else {
    allowedRolesHtml += '<i>' + this.drupal.t('No role restrictions.') + '</i>';;
  }

  const generalInfoText = this.drupal.t("This widget shows information about taxonomy term related permissions. It's being updated, as soon you make any related changes in the form.");

  this.documentAdapter.setDivHtmlByClassSelector('#edit-permissions-by-term-info .form-type-item', '<div id="edit-permissions-by-term-info"><div class="form-type-item">' + generalInfoText + '<br /><br />' + allowedUsersHtml + '<br />' + allowedRolesHtml + '</div></div>');
}

DomClient.prototype._computeTidsByAutocomplete = function(fieldWrapperCSSClass) {
  let selectedTids = []

  let autocompleteInputs = this.documentAdapter.document.querySelectorAll(fieldWrapperCSSClass + ' input.form-autocomplete');

  for (let autocompleteInput of autocompleteInputs) {

    if (autocompleteInput.value !== undefined && includes(autocompleteInput.value, '(') && includes(autocompleteInput.value, ')')) {

      let tidsInBrackets = autocompleteInput.value.match(/\(\d+\)/g);

      if (tidsInBrackets !== undefined && tidsInBrackets !== null && tidsInBrackets.constructor === Array) {

        for (let i = 0; i < tidsInBrackets.length; ++i) {
          let selectedTid = parseInt(tidsInBrackets[i].replace('(', '').replace(')', ''));
          if (!includes(selectedTids, selectedTid)) {
            selectedTids.push(String(selectedTid));
          }
        }

      }

    }

  }

  return selectedTids;
}

DomClient.prototype._computeTidsBySelect = function(fieldWrapperCSSClass) {
  let tids = [],
    inputTypes = ['select', 'input'];

  for (let inputTypesIndex = 0; inputTypesIndex <= inputTypes.length; inputTypesIndex++) {
    let value = this.documentAdapter.document.querySelector(fieldWrapperCSSClass + ' select').value;

    if (typeof value === "string") {
      tids.push(value);
    } else {
      throw "Value must be type of string.";
    }

  }

  return tids;
}

DomClient.prototype._computeTidsByCheckbox = function(formElementCssClass) {
  let tids = [];

  for (let checkbox of this.documentAdapter.document.querySelectorAll(formElementCssClass + ' input[type="checkbox"]')) {
    if (checkbox.checked === true) {
      tids.push(checkbox.value);
    }
  }

  return tids;
}

DomClient.prototype.computeTids = function(formElementCssClass) {
  let tids = [];

  const lookup = {
    checkbox: '_computeTidsByCheckbox',
    text: '_computeTidsByAutocomplete',
    select: '_computeTidsBySelect',
  };

  let inputType = this._getInputType(formElementCssClass);

  tids.push(this[lookup[inputType]](formElementCssClass));

  return tids;
}

DomClient.prototype._getInputType = function(formElementCssClass) {
  let formElement = null;

  if (!isEmpty(this.documentAdapter.document.querySelector(formElementCssClass + ' select'))) {
    formElement = 'select';
  }

  if (!isEmpty(this.documentAdapter.document.querySelector(formElementCssClass + ' input'))) {
    formElement = 'input';
  }

  if (formElement === 'input') {
    if (get(this.documentAdapter.document.querySelector(formElementCssClass + ' input.form-autocomplete'), 'type') && this.documentAdapter.document.querySelector(formElementCssClass + ' input.form-autocomplete').type === "text") {
      return 'text';
    }
    if (this.documentAdapter.document.querySelector(formElementCssClass + ' input').type === "checkbox") {
      return 'checkbox';
    }
    if (this.documentAdapter.document.querySelector(formElementCssClass + ' input').type === "radio") {
      return 'radio';
    }
  }
  if (!isEmpty(formElement) && this.documentAdapter.document.querySelector(formElementCssClass + ' select').tagName === "SELECT") {
    return 'select';
  }

}

export default DomClient;
