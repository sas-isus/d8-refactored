/**
 * @param Document document
 */
let DocumentAdapter = function(document) {
  this.document = document;
}

DocumentAdapter.prototype.setDivHtmlByClassSelector = function(classSelector, html) {
  let divHtml = this.document.createElement('div');
  divHtml.innerHTML = html;
  this.document.querySelector(classSelector).innerHTML = divHtml.outerHTML;
}

export default DocumentAdapter;
