let Backend = function(taxonomyRelationFieldNames = [], tidToUsernames = [], tidToRoles = [], fieldWrapperCSSClasses = []) {
  this.taxonomyRelationFieldNames = taxonomyRelationFieldNames;
  this.tidsToRoles = tidToRoles;
  this.tidToUsernames = tidToUsernames;
  this.fieldWrapperCSSClasses = fieldWrapperCSSClasses;
}

/**
 * @returns object[]
 */
Backend.prototype.getTidToUsername = function() {
  return this.tidToUsernames;
}

/**
 * @returns object[]
 */
Backend.prototype.getTidToRoles = function() {
  return this.tidsToRoles;
}

/**
 * @returns string[]
 */
Backend.prototype.getTaxonomyRelationFieldNames = function() {
  return this.taxonomyRelationFieldNames;
}

Backend.prototype.getFieldWrapperCSSClasses = function() {
  return this.fieldWrapperCSSClasses;
}

export default Backend;