import express from 'express';
import env from 'dotenv';
import fs from 'fs';

import { renderCalculatorThumbnail } from './controllers/calculator.controller';

env.config();

const app = express();

app.get('/j/:nickname', async (req, res) => {
    res.writeHead(200, {'Content-type': 'image/png'});

});

app.get('/c', renderCalculatorThumbnail);

app.listen(process.env.APP_PORT, function () {
    console.log('앱은 ' + process.env.APP_PORT + '포트에서 작동중입니다.');
});