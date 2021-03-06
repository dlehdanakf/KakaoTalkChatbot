import fs from 'fs';
import path from 'path';
import { promisify } from 'util';
import request from 'request';
import sharp from 'sharp';

import mergeImages from 'merge-images';
import Canvas from 'canvas';
import base64Img from 'base64-img';

import Delivery from '../models/delivery.model';
import DeliveryItem from '../models/delivery.item.model';
import Attachment from '../models/attachment.model';
import watermark from "image-watermark-2";

const protectedRespondDefaultImage = (req, res, from) => {
    console.log(from);
    fs.readFile(path.resolve("./images/default.png"), async function (err, content) {
        if (err) {
            res.writeHead(500, {'Content-type': 'image/png'});
            res.end();
        } else {
            const c = await sharp(content)
                .resize(600, 300)
                .toBuffer();

            res.writeHead(200, {'Content-type': 'image/png'});
            res.end(c);
        }
    });
};

const renderDeliveryThumbnail = async (req, res, attempt) => {
    if(attempt == undefined)
        attempt = 0;

    const { id } = req.params;
    const delivery = new Delivery(id);
    if(await delivery.fetchDataFromDB() === false){
        protectedRespondDefaultImage(req, res, 'No such Delivery Model');
        return;
    }

    let attachment = new Attachment(delivery.thumbnail_id);
    if(
        await attachment.fetchDataFromDB() === false ||
        ([ 'png', 'jpg', 'gif', 'jpeg' ]).includes(attachment.extension) === false
    ){
        attachment = new Attachment;
        attachment.id = 0;
        attachment.directory = "/";
        attachment.hashed_name = "default.png";
        attachment.extension = "png";
    }

    const cachedName = attachment.getCachedFileName(delivery);
    fs.readFile(cachedName, async function (err, content) {
        if (err) {
            /***
             *  >> 이미지 처리르 위한 프로세스 시작
             *  1. 첫번째로 이미지 다운로드
             */
            if(attempt > 3){
                protectedRespondDefaultImage(req, res, 'Exceeded number of attempts');
                return;
            }

            request({url: attachment.getDownloadLink(), encoding: null}, async (error, response, buffer) => {
                if(response.statusCode != 200){
                    protectedRespondDefaultImage(req, res, 'Failed to load image file in server');
                    return;
                }

                const c = await sharp(buffer)
                    .resize(600, 300)
                    .toBuffer();

                await attachment.makeDirPath();
                fs.writeFile(path.resolve(cachedName), c, async function(err){
                    if(err){
                        protectedRespondDefaultImage(req, res, 'Failed to save image file');
                        return;
                    }

                    if(1 <= delivery.promotion && delivery.promotion <= 3){
                        const labels = [ "students.png", "drinks.png", "sides.png" ];
                        const labelFileName = "./images/" + labels[delivery.promotion - 1];

                        const b64 = await mergeImages([path.resolve(cachedName), path.resolve(labelFileName)], {
                            format: 'image/png',
                            quality: 1,
                            Canvas: Canvas
                        });

                        const { dir, name } = path.parse(path.resolve(cachedName));
                        base64Img.img(b64, dir, name, function(){
                            return renderDeliveryThumbnail(req, res, attempt + 1);
                        });
                    } else {
                        return renderDeliveryThumbnail(req, res, attempt + 1);
                    }
                });
            });
        } else {
            res.writeHead(200, {'Content-type': 'image/png'});
            res.end(content);
        }
    });
};
const renderDeliveryItemThumbnail = async (req, res, attempt) => {
    if(attempt == undefined)
        attempt = 0;

    const { id } = req.params;
    const deliveryItem = new DeliveryItem(id);
    if(await deliveryItem.fetchDataFromDB() === false){
        protectedRespondDefaultImage(req, res, 'No such Delivery Model');
        return;
    }

    let attachment = new Attachment(deliveryItem.thumbnail_id);
    if(
        await attachment.fetchDataFromDB() === false ||
        ([ 'png', 'jpg', 'gif', 'jpeg' ]).includes(attachment.extension) === false
    ){
        attachment = new Attachment;
        attachment.id = 0;
        attachment.directory = "/";
        attachment.hashed_name = "default.png";
        attachment.extension = "png";
        attachment.instagram = "";
    }

    const cachedName = attachment.getCachedItemFileName();
    fs.readFile(cachedName, async function (err, content) {
        if (err) {
            /***
             *  >> 이미지 처리르 위한 프로세스 시작
             *  1. 첫번째로 이미지 다운로드
             */
            if(attempt > 3){
                protectedRespondDefaultImage(req, res, 'Exceeded number of attempts');
                return;
            }

            request({url: attachment.getDownloadLink(), encoding: null}, async (error, response, buffer) => {
                if(response.statusCode != 200){
                    protectedRespondDefaultImage(req, res, 'Failed to load image file in server');
                    return;
                }

                const c = await sharp(buffer)
                    .resize(600, 300)
                    .toBuffer();

                await attachment.makeDirPath();
                fs.writeFile(path.resolve(cachedName), c, async function(err){
                    if(err){
                        protectedRespondDefaultImage(req, res, 'Failed to save image file');
                        return;
                    }

                    const blank = "./images/blank.png";
                    const wm = "./caches/item.watermark.png";

                    if(attachment.instagram && attachment.instagram.toString().length > 0){
                        watermark.embedWatermarkWithCb(path.resolve(blank), {
                            'text': attachment.instagram.toString(),
                            'color': 'rgba(255,255,255,1)',
                            'dstPath': path.resolve(wm),
                            'align': 'ltr',
                            'position': 'NorthWest',
                            'pointsize': 16
                        }, e => {
                            if(e){
                                protectedRespondDefaultImage(req, res, 'Failed to save item watermark');
                                return;
                            }

                            (async () => {
                                const b64 = await mergeImages([
                                    { src: path.resolve(cachedName), x: 0, y: 0 },
                                    { src: path.resolve("./images/instagram.png"), x: 0, y: 0 },
                                    { src: path.resolve(wm), x: 56, y: 20 },
                                ], {
                                    format: 'image/png',
                                    quality: 1,
                                    Canvas: Canvas
                                });

                                const { dir, name } = path.parse(path.resolve(cachedName));
                                base64Img.img(b64, dir, name, function(){
                                    return renderDeliveryItemThumbnail(req, res, attempt + 1);
                                });
                            })();
                        });
                    } else {
                        return renderDeliveryItemThumbnail(req, res, attempt + 1);
                    }
                });
            });
        } else {
            res.writeHead(200, {'Content-type': 'image/png'});
            res.end(content);
        }
    });
};

export {
    renderDeliveryThumbnail,
    renderDeliveryItemThumbnail
};