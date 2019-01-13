import mysql from "mysql2/promise";

import BasicModel from "./basicModel.model";

class DeliveryItem extends BasicModel {
    constructor(id){
        super();

        this.id = id || 0;
        this.thumbnail_id = null;
        this.price = 0;
        this.discount = 0;

        this.fetchDataFromDB = this.fetchDataFromDB.bind(this);
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

                const [ rows ] = await connection.query('SELECT * FROM delivery_item WHERE id = ?', [ this.id ]);
                connection.release();

                if(rows.length < 1)
                    return false;

                const { thumbnail_id, price, discount } = rows[0];
                this.thumbnail_id = thumbnail_id;
                this.price = price;
                this.discount = discount;

                return true;
            } catch(err) {
                console.error('DeliveryItem 모델 데이터를 가져오던 도중 오류가 발생했습니다.');
                connection.release();

                return false;
            }
        } catch(err) {
            console.error('Mysql database connection error occur while fetch DeliveryItem model');
            return false;
        }
    }
}

export default DeliveryItem;