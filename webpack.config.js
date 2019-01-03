var HtmlWebpackExcludeAssetsPlugin = require('html-webpack-exclude-assets-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var MiniCssExtractPlugin = require("mini-css-extract-plugin");
var path = require('path');

module.exports = {
    entry: {
        './css/event': path.resolve(__dirname, './resources/event.scss'),
        './css/admin': path.resolve(__dirname, './resources/admin.scss'),
        './js/event': path.resolve(__dirname, './resources/event.js')
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader"
                }
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader"
                ]
            },
            {
                test: /\.scss$/,
                loaders : ExtractTextPlugin.extract({
                    fallback : 'style-loader',
                    use : ['css-loader', 'sass-loader?outputStyle=compressed']
                }),
                exclude: /node_modules/
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "[name].css",
            chunkFilename: "[id].css"
        }),
        new ExtractTextPlugin("[name].css")
    ],
    output: {
        path: path.resolve(__dirname, './public_html/assets'),
        filename: '[name].js'
    },
    node: {
        fs: "empty"
    },
    watch: true
};