const path = require("path");
const webpackMerge = require("webpack-merge");
const commonConfig = require("./webpack.config.js");
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = webpackMerge(commonConfig, {
  mode: "development",
  devtool: 'source-map',
  resolve: {
    extensions: ['.ts', '.js'],
    modules: [path.resolve(__dirname, 'node_modules')]
  },
  plugins: [
    new BundleAnalyzerPlugin({
      analyzerMode: 'static',
      analyzerPort: 8888,
      reportFilename: 'webpackReport.html',
      openAnalyzer: false,
      generateStatsFile: true,
      statsFilename: 'stats.json',
    })
  ]
});
