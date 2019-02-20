const path = require('path');

module.exports = {
  entry: ['./src/drupal-behavior-function/node-form-client.js'],
  output: {
    path: path.join(__dirname, '/webpack-dist/'),
    filename: 'bundle.js'
  },
  module: {
    rules: [
      {
        exclude: /(node_modules)/,
        use: [{
          loader: 'babel-loader',
          options: {
            babelrc: './.babelrc'
          }
        }]
      }
    ]
  },
};