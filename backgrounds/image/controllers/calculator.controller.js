import fs from 'fs';
import path from 'path';
import watermark from 'image-watermark-2';

const protectedGetOriginalFileName = (fileName) => {
    return "./images/" + fileName + ".png";
};
const protectedGetCacheFileName = (fileName, typeString, count) => {
    return "./calculator_caches/" + fileName + "." + typeString + "." + count + ".png";
};
const protectedGetRandomFileName = (mode, count) => {
    const good = [ "happy", "like", "happy" ];
    const bad = [ "bored", "despair", "sad" ];
    const sleepy = "sleepy";

    const rand = parseInt(Math.random() * 100) % 3;

    if(mode == 'semester'){
        if(count === 0)
            return "happy";

        if(count > 40){
            return bad[rand];
        } else if(count > 20) {
            return good[rand];
        } else {
            return sleepy;
        }
    } else {
        if(count > 18){
            return good[rand];
        } else {
            return bad[rand];
        }
    }
};

const renderCalculatorThumbnail = (req, res, attempt) => {
    // res.writeHead(200, {'Content-type': 'image/png'});

    const count = parseInt(req.query.c || 0);
    const type = req.query.t == 'plus' ? '+' : '-'
    const typeString = req.query.t == 'plus' ? 'plus' : 'minus'
    const mode = req.query.m || 'semester';

    if(attempt == undefined)
        attempt = 0;

    const fileName = protectedGetRandomFileName(mode, count);
    const originalImage = protectedGetOriginalFileName(fileName);
    const processedImage = protectedGetCacheFileName(fileName, typeString, count);

    fs.readFile(processedImage, function (err, content) {
        if (err) {
            if(attempt > 2) {
                res.writeHead(500, {'Content-type': 'image/png'})
                res.end("Internal Server Error");
                return;
            }

            watermark.embedWatermarkWithCb(originalImage, {
                'text': '  D ' + type + ' ' + count,
                'color': 'rgba(0,0,0,.8)',
                'dstPath': processedImage,
                'align': 'ltr',
                'position': 'SouthWest',
                'pointsize': 74
            }, function(e){
                if(e){
                    res.writeHead(500, {'Content-type': 'image/png'})
                    res.end("Internal Server Error");
                    return;
                }

                renderCalculatorThumbnail(req, res, attempt + 1);
            });
        } else {
            res.writeHead(200, {'Content-type': 'image/png'});
            res.end(content);
        }
    });
};

export {
    renderCalculatorThumbnail
};