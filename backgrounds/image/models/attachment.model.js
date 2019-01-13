import mysql from "mysql2/promise";
import path from "path";
import fs from "fs.promised";

import BasicModel from "./basicModel.model";

class Attachment {
    constructor(id){
        this.id = id || 0;
        this.directory = null;
        this.hashed_name = null;
        this.extension = null;
        this.instagram = "";

        this.fetchDataFromDB = this.fetchDataFromDB.bind(this);
        this.getDownloadLink = this.getDownloadLink.bind(this);
        this.getCachedFileName = this.getCachedFileName.bind(this);
        this.getCachedItemFileName = this.getCachedItemFileName.bind(this);
        this.getCachedFileDir = this.getCachedFileDir.bind(this);
        this.makeDirPath = this.makeDirPath.bind(this);
    }

    async fetchDataFromDB(){
        if(this.id === undefined || this.id == 0) return;

        try {
            const pool = mysql.createPool({
                host: process.env.DB_HOST,
                port: process.env.DB_PORT,
                database: process.env.DB_NAME,
                user: process.env.DB_USERNAME,
                password: process.env.DB_PASSWORD
            });
            const connection = await pool.getConnection(async conn => conn);
            try {

                const [ rows ] = await connection.query('SELECT * FROM attachment WHERE id = ?', [ this.id ]);
                connection.release();

                if(
                    rows.length < 1 ||
                    rows[0].directory === undefined
                )
                    return false;

                let { directory, hashed_name, extension, instagram } = rows[0];
                this.directory = directory;
                this.hashed_name = hashed_name;
                this.extension = extension;
                this.instagram = instagram || "";

                if(!this.instagram)
                    this.instagram = "";

                return true;
            } catch(err) {
                console.log('Mysql query error');
                connection.release();

                return false;
            }
        } catch(err) {
            console.log('Mysql database connection error');
            return false;
        }
    }
    getDownloadLink(){
        if(this.hashed_name === "default.png" && this.directory === "/")
            return process.env.SERVICE_URL + '/assets/images/' + this.hashed_name;

        return process.env.SERVICE_URL + '/attachments/' + this.directory + this.hashed_name;
    }

    /**
     * @param {BasicModel} model
     * @returns {string}
     */
    getCachedFileName(model){
        const { name } = path.parse(this.hashed_name);
        const rules = ([name, model.promotion, model.sticker, model.contract]).join(".");

        return "./caches/" + this.directory + rules + '.png';
    }
    getCachedItemFileName(){
        const { name } = path.parse(this.hashed_name);
        let rules = ([name, "item"]).join(".");
        if(this.instagram && this.instagram.toString().length > 0)
            rules = ([name, "item", this.instagram.toString()]).join(".");

        return "./caches/" + this.directory + rules + '.png';
    }
    getCachedFileDir(){
        return "./caches/" + this.directory;
    }
    async makeDirPath(){
        const arr = this.directory.split('/');
        for(let i = 0; i < arr.length; i++){
            if(arr[i] == '')
                continue;

            let p = arr.slice(0, i + 1).join('/');
            let d = path.resolve("./caches/" + p);

            if(fs.existsSync(d))
                continue;

            await fs.mkdir(d);
        }
    }

}

export default Attachment;