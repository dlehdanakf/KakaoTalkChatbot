import express from 'express';
import env from 'dotenv';
import path from 'path';
import fs from 'fs.promised';

import { renderCalculatorThumbnail } from './controllers/calculator.controller';
import { renderDeliveryThumbnail, renderDeliveryItemThumbnail } from './controllers/delivery.controller';
import { renderAffiliateThumbnail, renderAffiliateItemThumbnail } from './controllers/affiliate.controller';

env.config();

const app = express();

app.get('/c', renderCalculatorThumbnail);
app.get('/delivery/thumbnail/:id', renderDeliveryThumbnail);
app.get('/delivery/item/:id', renderDeliveryItemThumbnail);
app.get('/affiliate/thumbnail/:id', renderAffiliateThumbnail);
app.get('/affiliate/item/:id', renderAffiliateItemThumbnail);

(async () => {
    const caches = "./caches";
    const calculateCaches = "./calculator_caches";

    if(!fs.existsSync(path.resolve(caches))){
        await fs.mkdir(caches);
        console.log("캐시폴더가 존재하지 않아 새로 생성합니다.");
    } else {
        console.log("캐시폴더 확인 완료");
    }

    if(!fs.existsSync(path.resolve(calculateCaches))){
        await fs.mkdir(calculateCaches);
        console.log("종강일 캐시폴더가 존재하지 않아 새로 생성합니다.");
    } else {
        console.log("종강일 캐시폴더 확인 완료");
    }

    app.listen(process.env.APP_PORT, function () {
        console.log('앱은 ' + process.env.APP_PORT + '포트에서 작동중입니다.');
    });
})();