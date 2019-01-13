import express from 'express';
import env from 'dotenv';

import { renderCalculatorThumbnail } from './controllers/calculator.controller';

env.config();

const app = express();

app.get('/c', renderCalculatorThumbnail);
app.get('/a', function(req, res){});

app.listen(process.env.APP_PORT, function () {
    console.log('앱은 ' + process.env.APP_PORT + '포트에서 작동중입니다.');
});