'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.renderCalculatorThumbnail = undefined;

var _fs = require('fs');

var _fs2 = _interopRequireDefault(_fs);

var _imageWatermark = require('image-watermark-2');

var _imageWatermark2 = _interopRequireDefault(_imageWatermark);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var protectedGetOriginalFileName = function protectedGetOriginalFileName(fileName) {
    return "./images/" + fileName + ".png";
};
var protectedGetCacheFileName = function protectedGetCacheFileName(fileName, typeString, count) {
    return "./calculator_caches/" + fileName + "." + typeString + "." + count + ".png";
};
var protectedGetRandomFileName = function protectedGetRandomFileName(mode, count) {
    var good = ["happy", "like", "happy"];
    var bad = ["bored", "despair", "sad"];
    var sleepy = "sleepy";

    var rand = parseInt(Math.random() * 100) % 3;

    if (mode == 'semester') {
        if (count === 0) return "happy";

        if (count > 40) {
            return bad[rand];
        } else if (count > 20) {
            return good[rand];
        } else {
            return sleepy;
        }
    } else {
        if (count > 18) {
            return good[rand];
        } else {
            return bad[rand];
        }
    }
};

var renderCalculatorThumbnail = function renderCalculatorThumbnail(req, res, attempt) {
    // res.writeHead(200, {'Content-type': 'image/png'});

    var count = parseInt(req.query.c || 0);
    var type = req.query.t == 'plus' ? '+' : '-';
    var typeString = req.query.t == 'plus' ? 'plus' : 'minus';
    var mode = req.query.m || 'semester';

    if (attempt == undefined) attempt = 0;

    var fileName = protectedGetRandomFileName(mode, count);
    var originalImage = protectedGetOriginalFileName(fileName);
    var processedImage = protectedGetCacheFileName(fileName, typeString, count);

    _fs2.default.readFile(processedImage, function (err, content) {
        if (err) {
            if (attempt > 2) {
                res.writeHead(500, { 'Content-type': 'image/png' });
                res.end("Internal Server Error");
                return;
            }

            _imageWatermark2.default.embedWatermarkWithCb(originalImage, {
                'text': 'D ' + type + ' ' + count,
                'color': 'rgba(0,0,0,.8)',
                'dstPath': processedImage,
                'align': 'ltr',
                'position': 'Center',
                'pointsize': 54
            }, function (e) {
                if (e) {
                    //  do something
                    res.writeHead(500, { 'Content-type': 'image/png' });
                    res.end("Internal Server Error");
                    return;
                }

                renderCalculatorThumbnail(req, res, attempt + 1);
            });
        } else {
            res.writeHead(200, { 'Content-type': 'image/png' });
            res.end(content);
        }
    });
};

exports.renderCalculatorThumbnail = renderCalculatorThumbnail;