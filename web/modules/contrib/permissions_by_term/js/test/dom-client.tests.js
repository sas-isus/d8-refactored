import DomClient from "../src/client/dom-client.prototype";
import sinon from "sinon";

QUnit.test( "Get permission objects by querying backend with all params", async function( assert ) {
  const spySetDivHtmlByClassSelector = sinon.spy();

  const documentAdapter = {
    setDivHtmlByClassSelector: spySetDivHtmlByClassSelector,
    document: {
      createElement: () => sinon.stub(),
    }
  };

  const permissionsOutput = {
    getRoles: sinon.stub().returns(['admin', 'editor']),
    getUsernames: sinon.stub().returns(['jeff', 'brandon'])
  }

  const drupal = {
    t: function(text) {
      return text;
    }
  }

  const domClient = new DomClient(documentAdapter, permissionsOutput, drupal);
  domClient.renderPermissionsInfo();

  assert.equal(spySetDivHtmlByClassSelector.getCall(0).args[1], '<div id="edit-permissions-by-term-info"><div class="form-type-item">This widget shows information about taxonomy term related permissions. It\'s being updated, as soon you make any related changes in the form.<br /><br /><b>Allowed users:</b> jeff, brandon<br /><b>Allowed roles:</b> admin, editor</div></div>');

});
