import isEmpty from 'lodash/isEmpty';
import Backend from '../model/backend.prototype';
import get from 'lodash/get';

/**
 * @returns Access
 */
const createPermission = async (fetchFromBackend) => {
  const fieldCssClasses = [];
  let data = await fetchFromBackend();

  if (!isEmpty(data.taxonomyRelationFieldNames)) {
    data.taxonomyRelationFieldNames.forEach((fieldName) => {
      const fieldWrapperClass = '.field--name-' + fieldName.replace(/_/g, '-');

      fieldCssClasses.push(fieldWrapperClass);
    });
  }

  let userDisplayNames = null;
  if (get(data, 'permissions.userDisplayNames')) {
    userDisplayNames = data.permissions.userDisplayNames;
  }

  let roleLabels = null;
  if (get(data, 'permissions.roleLabels')) {
    roleLabels = data.permissions.roleLabels;
  }

  return new Backend(
    data.taxonomyRelationFieldNames,
    userDisplayNames,
    roleLabels,
    fieldCssClasses
  );
}

export default createPermission;
