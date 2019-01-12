'use strict';

var _express = require('express');

var _express2 = _interopRequireDefault(_express);

var _dotenv = require('dotenv');

var _dotenv2 = _interopRequireDefault(_dotenv);

var _fs = require('fs');

var _fs2 = _interopRequireDefault(_fs);

var _calculator = require('./controllers/calculator.controller');

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_dotenv2.default.config();

var app = (0, _express2.default)();

app.get('/j/:nickname', async function (req, res) {
    res.writeHead(200, { 'Content-type': 'image/png' });
});

app.get('/c', _calculator.renderCalculatorThumbnail);

app.listen(process.env.APP_PORT, function () {
    console.log('앱은 ' + process.env.APP_PORT + '포트에서 작동중입니다.');
});