import TermCollector from '../src/client/term-collector.prototype';
import _ from 'lodash';
import sinon from 'sinon';

QUnit.test( "Term selector retrieves empty array if no tids selected", function( assert ) {

  const domClient = {
      computeTids: sinon.stub().returns([])
    },
    termCollector = new TermCollector;
  termCollector.addSelectedTids(domClient.computeTids());

  assert.ok(_.isEmpty(termCollector.getSelectedTids()));
});

QUnit.test( "Term selector retrieves array with tids if tids selected", function( assert ) {
  const domClient = {
        computeTids: sinon.stub().returns(['1','2','3'])
      },
      termCollector = new TermCollector;
  termCollector.addSelectedTids(domClient.computeTids(['first-field', 'second-field']));

  assert.deepEqual(termCollector.getSelectedTids(), ['1','2','3']);
});

QUnit.test( "Term selector retrieves tid array with no duplicates", function( assert ) {
  const termCollector = new TermCollector;
  termCollector.addSelectedTids(['1','1','1','2','2','2']);

  assert.deepEqual(termCollector.getSelectedTids(), ['1','2']);
});
