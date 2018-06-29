# Folder structure

## NPM
NPM in version 5.6.0 and NodeJS in version 9.11.1 are proven in building the JavaScript code. The build assumes a 
*NIX environment (Mac or Linux).

## babel-compiled
Files which are needed for tests in cli. Because [Babel](https://babeljs.io/) is transpiling the modern ES6 import
statement to CommonJS, which can be parsed by NodeJS.

## node-modules
Third-party JavaScript libraries.

## src
The application code.

## test
Automated tests. 

They can be executed via command line ("bat" stands for "build and test"):

`npm run bat`
 
 Or they can be executed via the webbrowser by opening the .html files. Google Chrome
 is good for JavaScript debugging. Therefor this option is provided by the QUnit JavaScript
 testing tool.
 
## Building the application
`npm run build`