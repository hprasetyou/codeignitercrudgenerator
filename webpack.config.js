const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin')
const extractCSS = new ExtractTextPlugin('[name].css')

module.exports = {
  entry:  {
    main: './src/app.js'
 },
  module: {
    rules: [{
        test: /\.css$/,
        loader: extractCSS.extract(['css-loader','sass-loader'])
      },{ test: /\.json$/, loader: 'json-loader' },{
        test: /\.(png|jpe?g|gif|svg|woff|woff2|ttf|eot|ico)(\?.*)?$/,
        use: [{
          loader: 'url-loader',
          options: { limit: 10000 } // Convert images < 10k to base64 strings
        }]
      }]
  },

  plugins: [
    new webpack.ProvidePlugin({
        $: "jquery",
        jQuery: "jquery",
        "window.jQuery": "jquery"
    }),
    new webpack.optimize.UglifyJsPlugin({
        include: /\.(min\.js|min\.css)$/,
        minimize: true
      }),
    new webpack.IgnorePlugin(/^codemirror$/),
    extractCSS
    ],

  output: {
    path: path.resolve(__dirname, 'public/assets'),
    filename: '[name].js'
  }
};
