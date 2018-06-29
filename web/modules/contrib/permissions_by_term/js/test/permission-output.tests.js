import PermissionOutputCollector from '../src/client/permission-output-collector.prototype';
import PermissionOutput from '../src/model/permission-output.prototype';
import createPermission from "../src/async-function/create-permission";
import TermCollector from "../src/client/term-collector.prototype";
import sinon from "sinon";

QUnit.test("Collect output roles and usernames", async ( assert ) => {

  const fetchFromBackend = async() => {
    return {
      taxonomyRelationFieldNames: ['field-one', 'field-two', 'field-thrid'],
      permissions: {
        userDisplayNames: {
          '2': ['jeff'],
          '4': ['brandon', 'brian']
        },
        roleLabels: {
          '1': ['admin','editor'],
          '2': ['editor']
        }
      }
    };
  },
    domClient = {
      computeTids: sinon.stub().returns(['2'])
    },
    termCollector = new TermCollector;

  termCollector.addSelectedTids(domClient.computeTids(['first-field', 'second-field']));

  /**
   * @var Backend[] permissions
   * @var PermissionOutputCollector permissionOutputCollector
   */
  let permissions = await createPermission(fetchFromBackend),
      permissionOutput = new PermissionOutput,
      permissionOutputCollector = new PermissionOutputCollector(permissionOutput);

  permissionOutputCollector.collect(permissions, termCollector.getSelectedTids());

  assert.deepEqual(permissionOutputCollector.getPermissionOutput().getRoles(), ['editor']);
  assert.deepEqual(permissionOutputCollector.getPermissionOutput().getUsernames(), ['jeff']);

});

QUnit.test("Collect permissions without duplicates", async (assert) => {

  const fetchFromBackend = async() => {
        return {
          taxonomyRelationFieldNames: ['field-one', 'field-two', 'field-thrid'],
          permissions: {
            userDisplayNames: {
              '2': ['jeff','jeff'],
              '4': ['brandon', 'brian']
            },
            roleLabels: {
              '1': ['admin','admin','editor'],
              '2': ['editor','editor']
            }
          }
        };
      },
      domClient = {
        computeTids: sinon.stub().returns(['2'])
      },
      termCollector = new TermCollector;

  termCollector.addSelectedTids(domClient.computeTids(['first-field', 'second-field']));

  /**
   * @var Backend[] permissions
   */
  let permissions = await createPermission(fetchFromBackend),
      permissionOutput = new PermissionOutput,
      permissionOutputCollector = new PermissionOutputCollector(permissionOutput);

  permissionOutputCollector.collect(permissions, termCollector.getSelectedTids());

  assert.deepEqual(permissionOutputCollector.getPermissionOutput().getRoles(), ['editor']);
  assert.deepEqual(permissionOutputCollector.getPermissionOutput().getUsernames(), ['jeff']);

});