"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _promise = require("mysql2/promise");

var _promise2 = _interopRequireDefault(_promise);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Affiliate = function () {
    function Affiliate(id) {
        _classCallCheck(this, Affiliate);

        this.id = id || 0;
        this.title = "";
        this.thumbnail_id = null;
        this.promotion = 0;
        this.contract = 0;

        this.fetchDataFromDB = this.fetchDataFromDB.bind(this);
    }

    _createClass(Affiliate, [{
        key: "fetchDataFromDB",
        value: async function fetchDataFromDB() {
            if (this.id === undefined || this.id == 0) return;

            try {
                var pool = _promise2.default.createPool({
                    host: process.env.DB_HOST,
                    port: process.env.DB_PORT,
                    database: process.env.DB_NAME,
                    user: process.env.DB_USERNAME,
                    password: process.env.DB_PASSWORD
                });
                var connection = await pool.getConnection(async function (conn) {
                    return conn;
                });

                try {
                    var _ref = await connection.query('SELECT * FROM affiliate WHERE id = ?', [this.id]),
                        _ref2 = _slicedToArray(_ref, 1),
                        rows = _ref2[0];

                    connection.release();

                    if (rows.length < 1 || rows[0].userid === undefined) return false;

                    var _rows$ = rows[0],
                        title = _rows$.title,
                        thumbnail_id = _rows$.thumbnail_id,
                        promotion = _rows$.promotion,
                        contract = _rows$.contract;

                    this.title = title;
                    this.thumbnail_id = thumbnail_id;
                    this.promotion = promotion;
                    this.contract = contract;

                    return true;
                } catch (err) {
                    console.log('Affiliate 모델 데이터를 가져오던 도중 오류가 발생했습니다.');
                    connection.release();

                    return false;
                }
            } catch (err) {
                console.log('Mysql database connection error occur while fetch affiliate model');
                return false;
            }
        }
    }]);

    return Affiliate;
}();

exports.default = Affiliate;