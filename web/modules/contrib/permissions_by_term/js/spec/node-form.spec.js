describe("Permissions by Term Suite", function() {

  it("Compute field wrapper CSS classes", function() {
    fs = require('fs')
    prototypeClass = fs.readFileSync('node-form.prototype.js','utf-8')
    eval(prototypeClass)

    var NodeForm = new NodeForm();

    var fieldNames = ['field_name-one', 'field_name_two'];

    expect(NodeForm.computeFieldWrapperCSSClasses(fieldNames)).toEqual(['.field--name-field-name-one', '.field--name-field-name-two']);
  });

});