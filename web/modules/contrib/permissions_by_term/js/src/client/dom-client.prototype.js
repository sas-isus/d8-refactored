const _ = require('lodash');

/**
 * @param PermissionsOutput permissionsOutput
 * @param document
 */
let DomClient = function(document, permissionsOutput, drupal) {
  this.document = document;
  this.drupal = drupal;
  this.permissionsOutput = permissionsOutput;
}

DomClient.prototype.renderPermissionsInfo = function() {

  let allowedUsersHtml = '<b>' + this.drupal.t('Allowed users:') + '</b> ';

  if (!_.isEmpty(this.permissionsOutput.getUsernames())) {
    allowedUsersHtml += this.permissionsOutput.getUsernames().join(', ');
  } else {
    allowedUsersHtml += '<i>' + this.drupal.t('No user restrictions.') + '</i>';
  }

  let allowedRolesHtml = '<b>' + this.drupal.t('Allowed roles:') + '</b> ';

  if (!_.isEmpty(this.permissionsOutput.getRoles())) {
    allowedRolesHtml += this.permissionsOutput.getRoles().join(', ');
  } else {
    allowedRolesHtml += '<i>' + this.drupal.t('No role restrictions.') + '</i>';;
  }

  let generalInfoText = this.drupal.t("This widget shows information about taxonomy term related permissions. It's being updated, as soon you make any related changes in the form.");

  let newTermInfo = this.document.createElement('div');
  newTermInfo.innerHTML = '<div id="edit-permissions-by-term-info"><div class="form-type-item">' + generalInfoText + '<br /><br />' + allowedUsersHtml + '<br />' + allowedRolesHtml + '</div></div>';
  this.document.querySelector('#edit-permissions-by-term-info .form-type-item').replaceWith(newTermInfo);

}

DomClient.prototype._computeTidsByAutocomplete = function(fieldWrapperCSSClass) {
  let selectedTids = []

  let autocompleteInputs = this.document.querySelectorAll(fieldWrapperCSSClass + ' input.form-autocomplete');

  for (let autocompleteInput of autocompleteInputs) {

    if (autocompleteInput.value !== undefined && _.includes(autocompleteInput.value, '(') && _.includes(autocompleteInput.value, ')')) {

      let tidsInBrackets = autocompleteInput.value.match(/\(\d+\)/g);

      if (tidsInBrackets !== undefined && tidsInBrackets !== null && tidsInBrackets.constructor === Array) {

        for (let i = 0; i < tidsInBrackets.length; ++i) {
          let selectedTid = parseInt(tidsInBrackets[i].replace('(', '').replace(')', ''));
          if (!_.includes(selectedTids, selectedTid)) {
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
    let value = this.document.querySelector(fieldWrapperCSSClass + ' select').value;

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

  for (let checkbox of this.document.querySelectorAll(formElementCssClass + ' input[type="checkbox"]')) {
    if (checkbox.checked === true) {
      tids.push(checkbox.value);
    }
  }

  return tids;
}

DomClient.prototype.computeTids = function(formElementCssClass) {
  let tids = [];

  let lookup = {
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

  if (!_.isEmpty(this.document.querySelector(formElementCssClass + ' select'))) {
    formElement = 'select';
  }

  if (!_.isEmpty(this.document.querySelector(formElementCssClass + ' input'))) {
    formElement = 'input';
  }

  if (formElement === 'input') {
    if (_.get(this.document.querySelector(formElementCssClass + ' input.form-autocomplete'), 'type') && this.document.querySelector(formElementCssClass + ' input.form-autocomplete').type === "text") {
      return 'text';
    }
    if (this.document.querySelector(formElementCssClass + ' input').type === "checkbox") {
      return 'checkbox';
    }
    if (this.document.querySelector(formElementCssClass + ' input').type === "radio") {
      return 'radio';
    }
  }
  if (!_.isEmpty(formElement) && this.document.querySelector(formElementCssClass + ' select').tagName === "SELECT") {
    return 'select';
  }

}

export default DomClient;