import includes from 'lodash/includes';

/**
 * @param PermissionOutput permissionOutput
 */
let PermissionsOutputCollector = function(permissionOutput) {
  this.permissionOutput = permissionOutput;
}

PermissionsOutputCollector.prototype.collect = function(permissions, tidsByInputs) {
  this._collectRoles(permissions, tidsByInputs);
  this._collectUsers(permissions, tidsByInputs);
}

PermissionsOutputCollector.prototype._collectRoles = function(permissions, tidsByInputs) {

  for (let tids of tidsByInputs) {

    for (let tidToRole in permissions.tidsToRoles) {

      if (includes(tids, tidToRole)) {

        for (let role of permissions.tidsToRoles[tidToRole]) {
          if (!includes(this.permissionOutput.getRoles(), role)) {
            this.permissionOutput.addRole(role);
          }
        }

      }

    }

  }

}

PermissionsOutputCollector.prototype._collectUsers = function(permissions, tidsByInputs) {

  for (let tids of tidsByInputs) {

    for (let tidToUsername in permissions.tidToUsernames) {
      if (includes(tids, tidToUsername)) {
        for (let username of permissions.tidToUsernames[tidToUsername]) {
          if (!includes(this.permissionOutput.getUsernames(), username)) {
            this.permissionOutput.addUsername(username);
          }
        }

      }

    }

  }
}

/**
 * @returns PermissionOutput
 */
PermissionsOutputCollector.prototype.getPermissionOutput = function() {
  return this.permissionOutput;
}

export default PermissionsOutputCollector;