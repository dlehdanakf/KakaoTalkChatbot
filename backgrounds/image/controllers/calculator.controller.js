import fs from 'fs';
import path from 'path';
import watermark from 'image-watermark-2';

const protectedGetOriginalFileName = (fileName) => {
    console.log(path.resolve("images/" + fileName + ".png"));

    return path.resolve("images/" + fileName + ".png");
};
const protectedGetCacheFileName = (fileName, typeString, count) => {
    return path.resolve("calculator_caches/" + fileName + "." + typeString + "." + count + ".png");
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

    console.log('A');
    fs.readFile(processedImage, function (err, content) {
        console.log('B');
        if (err) {
            console.log('C');
            if(attempt > 2) {
                console.log('D');
                res.writeHead(500, {'Content-type': 'image/png'})
                res.end("Internal Server Error");
                return;
            }

            watermark.embedWatermarkWithCb(originalImage, {
                'text': 'D ' + type + ' ' + count,
                'color': 'rgba(0,0,0,.8)',
                'dstPath': processedImage,
                'align': 'ltr',
                'position': 'Center',
                'pointsize': 54
            }, function(e){
                console.log('E');
                if(e){
                    console.log('F');
                    //  do something
                    res.writeHead(500, {'Content-type': 'image/png'})
                    res.end("Internal Server Error");
                    return;
                }

                renderCalculatorThumbnail(req, res, attempt + 1);
            });

        } else {
            console.log('G');
            res.writeHead(200, {'Content-type': 'image/png'});
            res.end(content);
        }
    });
};

export {
    renderCalculatorThumbnail
};