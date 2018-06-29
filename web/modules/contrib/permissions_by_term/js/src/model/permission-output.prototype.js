let PermissionOutput = function() {
  this.roles = [];
  this.usernames = [];
}

/**
 * @returns array
 */
PermissionOutput.prototype.getUsernames = function() {
  return this.usernames;
}

/**
 * @returns array
 */
PermissionOutput.prototype.getRoles = function() {
  return this.roles;
}

/**
 * @returns array
 */
PermissionOutput.prototype.addUsername = function(username) {
  return this.usernames.push(username);
}

/**
 * @returns array
 */
PermissionOutput.prototype.addRole = function(role) {
  return this.roles.push(role);
}

export default PermissionOutput;