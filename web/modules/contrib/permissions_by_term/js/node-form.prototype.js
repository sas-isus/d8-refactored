var NodeForm = function($){
  this.jQuery = $;
  this.selectedTids = [];
  this.formElementCssClasses = [];
};

NodeForm.prototype.getSelectedTids = function() {
  var tids = [];

  for (var index = 0; index < this.formElementCssClasses.length; ++index) {
    if (this.selectedTids[this.formElementCssClasses[index]] !== undefined && this.selectedTids[this.formElementCssClasses[index]].constructor === Array) {

      this.selectedTids[this.formElementCssClasses[index]].forEach(function(tid){
        tids.push(tid);
      })
    }
  }

  return tids;
}

NodeForm.prototype.addFormElementCssClass = function(formElementCssClass) {
  this.formElementCssClasses.push(formElementCssClass);
}


NodeForm.prototype.keyExists = function(key, array) {
  if (!array || (array.constructor !== Array && array.constructor !== Object)) {
    return false;
  }
  for (var i = 0; i < array.length; i++) {
    if (array[i] === key) {
      return true;
    }
  }
  return key in array;
}

NodeForm.prototype.addSelectedTid = function(tid, formElementCssClass) {
  if (!this.keyExists(formElementCssClass, this.formElementCssClasses)) {
    this.formElementCssClasses.push(formElementCssClass);
  }

  if (this.selectedTids[formElementCssClass] === undefined) {

    this.selectedTids[formElementCssClass] = [];
  }

  this.selectedTids[formElementCssClass].push(tid);
}

NodeForm.prototype.removeTid = function(value, formElementCssClass) {
  const index = this.selectedTids[formElementCssClass].indexOf(parseInt(value));

  if (index !== -1) {
    this.selectedTids[formElementCssClass].splice(index, 1);
  }
}

NodeForm.prototype.resetData = function(formElementCssClass) {
  this.selectedTids[formElementCssClass] = [];
}

NodeForm.prototype.computeFieldWrapperCSSClasses = function(fieldNames) {
  var wrapperCssClasses = [];

  for (var index = 0; index < fieldNames.length; ++index) {
    var fieldWrapperClass = '.field--name-' + fieldNames[index].replace(/_/g, '-');

    wrapperCssClasses.push(fieldWrapperClass);
  }

  return wrapperCssClasses;
}

NodeForm.prototype.displayPermissionsByCheckbox = function(tid, checked, permissions) {
  if (checked === false) {
    this.resetData('checkbox_tid_' + tid);
  } else if (checked === true){
    this.addSelectedTid(parseInt(tid), 'checkbox_tid_' + tid);
  }

  this.renderPermissionsInfo(permissions);
}

NodeForm.prototype.displayPermissionsBySelect = function(fieldWrapperCSSClasses, permissions) {
  for (var index = 0; index < fieldWrapperCSSClasses.length; ++index) {
    var inputTypes = ['select', 'input'];

    var fieldWrapperCSSClass = fieldWrapperCSSClasses[index];

    for (var inputTypesIndex = 0; inputTypesIndex <= inputTypes.length; inputTypesIndex++) {
      var values = this.jQuery(fieldWrapperCSSClass + ' select').val();

      if (values !== undefined && values !== null && values.constructor === Array) {
        if (values[0] === '_none') {
          this.resetData(fieldWrapperCSSClass);
        }

        for (var i = 0; i < values.length; ++i) {
          if (isNaN(values[i]) === false) {
            this.addSelectedTid(parseInt(values[i]), fieldWrapperCSSClass);
          }
        }
      }

    }

  }

  this.renderPermissionsInfo(permissions);

}

NodeForm.prototype.displayPermissionsByAutocomplete = function(fieldWrapperCSSClasses, permissions) {
  for (var index = 0; index < fieldWrapperCSSClasses.length; ++index) {
    var fieldWrapperCSSClass = fieldWrapperCSSClasses[index];

    var values = this.jQuery(fieldWrapperCSSClass + ' input').val();

    this.resetData(fieldWrapperCSSClass);

    if (values !== undefined && values.indexOf('(') !== -1 && values.indexOf(')')) {

      var tidsInBrackets = values.match(/\(\d+\)/g);

      if (tidsInBrackets !== undefined && tidsInBrackets !== null && tidsInBrackets.constructor === Array) {

        for (var i = 0; i < tidsInBrackets.length; ++i) {
          var selectedTid = parseInt(tidsInBrackets[i].replace('(', '').replace(')', ''));
          this.addSelectedTid(selectedTid, fieldWrapperCSSClass);
        }

      }

    }

  }

  this.renderPermissionsInfo(permissions);

}

NodeForm.prototype.separateByComma = function(values) {
  return values.join(', ');
}

NodeForm.prototype.renderPermissionsInfo = function(permissions) {

  var permissionsToDisplay = this.getPermissionsByTids(this.getSelectedTids(), permissions);

  var allowedUsersHtml = '<b>' + Drupal.t('Allowed users:') + '</b> ';

  if (this.isAllowedUsersRestriction(permissionsToDisplay)) {
    allowedUsersHtml += this.separateByComma(permissionsToDisplay['permittedUsers']);
  } else {
    allowedUsersHtml += '<i>' + Drupal.t('No user restrictions.') + '</i>';
  }

  var allowedRolesHtml = '<b>' + Drupal.t('Allowed roles:') + '</b> ';

  if (this.isAllowedRolesRestriction(permissionsToDisplay)) {
    allowedRolesHtml += this.separateByComma(permissionsToDisplay['permittedRoles']);
  } else {
    allowedRolesHtml += '<i>' + Drupal.t('No role restrictions.') + '</i>';;
  }

  var generalInfoText = Drupal.t("This widget shows information about taxonomy term related permissions. It's being updated, as soon you make any related changes in the form.");

  this.jQuery('#edit-permissions-by-term-info .form-type-item').html(generalInfoText + '<br /><br />' + allowedUsersHtml + '<br />' + allowedRolesHtml);
}

NodeForm.prototype.isAllowedUsersRestriction = function(permissionsToDisplay) {
  if (permissionsToDisplay['permittedUsers'].length > 0 && permissionsToDisplay['permittedUsers'] !== null) {
    return true;
  }

  return false;
}

NodeForm.prototype.isAllowedRolesRestriction = function(permissionsToDisplay) {
  if (permissionsToDisplay['permittedRoles'].length > 0 && permissionsToDisplay['permittedRoles'] !== null) {
    return true;
  }

  return false;
}

NodeForm.prototype.pushUserDisplayNames = function(tids, permissionsToDisplay, permissions) {
  for (var index = 0; index < tids.length; ++index) {
    if (permissions.hasOwnProperty('userDisplayNames') && permissions['userDisplayNames'].hasOwnProperty(tids[index]) && permissions['userDisplayNames'][tids[index]] !== null &&
        permissionsToDisplay['permittedUsers'].indexOf(permissions['userDisplayNames'][tids[index]]) === -1) {

      var userDisplayNames = permissions['userDisplayNames'][tids[index]];

      if (userDisplayNames.constructor === Array && userDisplayNames.length > 1) {
        userDisplayNames.forEach(function(value){
          if (permissionsToDisplay['permittedUsers'].indexOf(value) === -1) {
            permissionsToDisplay['permittedUsers'].push(value);
          }
        });
      } else {
        if (permissionsToDisplay['permittedUsers'].indexOf(userDisplayNames) === -1) {
          permissionsToDisplay['permittedUsers'].push(userDisplayNames);
        }
      }
    }
  }

  return permissionsToDisplay;
}

NodeForm.prototype.pushRoles = function(tids, permissionsToDisplay, permissions) {
  for (var index = 0; index < tids.length; ++index) {

    if (permissions['roleLabels'] === undefined) {
      return permissionsToDisplay;
    }

    if (permissions['roleLabels'][tids[index]] !== undefined && permissions['roleLabels'][tids[index]] !== null) {
      permissions['roleLabels'][tids[index]].forEach(function(role){
        if (permissionsToDisplay['permittedRoles'].indexOf(role) === -1) {
          permissionsToDisplay['permittedRoles'].push(role);
        }
      });
    }
  }

  return permissionsToDisplay;
}

NodeForm.prototype.getPermissionsByTids = function(tids, permissions) {
  var permissionsToDisplay = {
    permittedUsers: [],
    permittedRoles: []
  };

  permissionsToDisplay = this.pushRoles(tids, permissionsToDisplay, permissions);
  permissionsToDisplay = this.pushUserDisplayNames(tids, permissionsToDisplay, permissions);

  return permissionsToDisplay;
}
