const path = require('path');
const ROOT = path.resolve(__dirname, './src/angular-app');
const DESTINATION = path.resolve(__dirname, 'src', 'dist');
const workboxPlugin = require('workbox-webpack-plugin');
const LoaderOptionsPlugin = require('webpack/lib/LoaderOptionsPlugin');

/**
 * Webpack Plugins
 */
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require("copy-webpack-plugin");
const webpack = require('webpack')
const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
    context: ROOT,
    resolve: {
      extensions: ['.ts', '.js']
    },
    output: {
      path: DESTINATION,
      filename: "[name].bundle.js",
    },
    optimization: {
      chunkIds: "named",
      splitChunks: {
        cacheGroups: {
          vendor: {
            test: /node_modules/,
            chunks: "initial",
            name: "vendor",
            priority: 10,
            enforce: true,
          },
        },
      },
      runtimeChunk: {
        name: "manifest",
      },
      minimize: true,
      minimizer: [new TerserPlugin()],
      usedExports: true
    },

    devServer: {
      historyApiFallback: true,
      watchOptions: { aggregateTimeout: 300, poll: 1000 },
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization'
      }
    },

    node: {
      global: true,
      __dirname: true,
      __filename: true
    },

    module: {
        rules: [
          { test: /\.ts$/, use: 'ts-loader' },
          { test: /\.s?css$/, use: ['style-loader', 'css-loader', 'sass-loader'] },
          {
            test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
            use: 'url-loader?limit=10000'
          },
          { test: /\.(ttf|eot|svg)(\?[\s\S]+)?$/, use: 'file-loader' },
          { test: /\.(png|jpg)$/, use: 'url-loader?limit=8192' },
          { test: /ng-table\/.*\.html$/, use: ['ngtemplate-loader?requireAngular&relativeTo=/src/browser/&prefix=ng-table/', 'html-loader'] }
        ]
    },

    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css'
        }),
        new CopyPlugin({
            patterns: [
                  { from: '../../node_modules/font-awesome/', to: 'font-awesome' },
                  { from: '../../node_modules/zxcvbn/dist/', to: 'zxcvbn' },
                  { from: '../../node_modules/ng-table/bundles/ng-table.min.css', to: 'ng-table' },
                  { from: '../../node_modules/ng-table/bundles/ng-table.min.css.map', to: 'ng-table' },
            ],
          }),
          new webpack.ProvidePlugin({
            process: 'process/browser',
          }),
          new webpack.ContextReplacementPlugin(

            // The ([\\/]) piece accounts for path separators in *nix and Windows
            /angular([\\/])core([\\/])@angular/,
            path.resolve(__dirname, './src'),

            // your Angular Async Route paths relative to this root directory
            {}
          ),
          new LoaderOptionsPlugin({
            debug: true,
            options: {
              tslint: {
                configuration: require('./tslint.json'),
                typeCheck: true
              }
            }
          }),
          new workboxPlugin.GenerateSW({
            clientsClaim: true,
            skipWaiting: true,
          }),
    ],

    entry: './main.ts',
    devtool: "source-map",
};
