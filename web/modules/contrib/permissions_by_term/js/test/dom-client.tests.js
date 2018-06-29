import DomClient from "../src/client/dom-client.prototype";
import sinon from "sinon";

QUnit.test( "Get permission objects by querying backend with all params", async function( assert ) {

  const spyReplaceWith = sinon.spy();

  const document = {
    querySelector: sinon.stub().returns({
      replaceWith: spyReplaceWith
    }),
    createElement: sinon.stub().returns({innerHTML: sinon.stub()}),
  }

  const permissionsOutput = {
    getRoles: sinon.stub().returns(['admin', 'editor']),
    getUsernames: sinon.stub().returns(['jeff', 'brandon'])
  }

  const drupal = {
    t: function(text) {
      return text;
    }
  }

  const domClient = new DomClient(document, permissionsOutput, drupal);
  domClient.renderPermissionsInfo();

  assert.deepEqual({innerHTML: '<div id="edit-permissions-by-term-info"><div class="form-type-item">This widget shows information about taxonomy term related permissions. It\'s being updated, as soon you make any related changes in the form.<br /><br /><b>Allowed users:</b> jeff, brandon<br /><b>Allowed roles:</b> admin, editor</div></div>'}, spyReplaceWith.getCall(0).args[0]);

});