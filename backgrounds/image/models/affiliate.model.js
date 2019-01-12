import mysql from "mysql2/promise";

class Affiliate {
    constructor(id){
        this.id = id || 0;
        this.title = "";
        this.thumbnail_id = null;
        this.promotion = 0;
        this.contract = 0;

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

                const [ rows ] = await connection.query('SELECT * FROM affiliate WHERE id = ?', [ this.id ]);
                connection.release();

                if(
                    rows.length < 1 ||
                    rows[0].userid === undefined
                )
                    return false;

                const { title, thumbnail_id, promotion, contract } = rows[0];
                this.title = title;
                this.thumbnail_id = thumbnail_id;
                this.promotion = promotion;
                this.contract = contract;

                return true;
            } catch(err) {
                console.log('Affiliate 모델 데이터를 가져오던 도중 오류가 발생했습니다.');
                connection.release();

                return false;
            }
        } catch(err) {
            console.log('Mysql database connection error occur while fetch affiliate model');
            return false;
        }
    }
}

export default Affiliate;