<?php
// クラス役割: DB接続・各種クエリの準備実行・結果データ成形
// コンストラクタ引数: DB名（必須）、ホスト名、DBユーザー名、DBパスワード
// 処理内容: コンストラクタでDBの接続を確立、下記メソッドの実行
//           ・SQLの実行（結果ステートメントの受け取りまで）
//           ・Selectの実行・データ成形（結果を受領後、結果の加工）
//           ・Insert、Update、Deleteの実行（結果を受領後、実行件数を返す

class DatabaseController {
    private $dbh;
    private $isDebug;
    private $db_name;
    private $host;
    private $user;
    private $pass;
    private $dsn;

    /**
     * DBコネクションを確立し、デバッグモードを設定
     */
    public function __construct($dbinfo, $isDebug = false) {
        // var_dump($dbinfo).PHP_EOL;
        $this->db_name = $dbinfo->dbName;
        $this->host = $dbinfo->host ?? "localhost";
        $this->user = $dbinfo->user ?? "root";
        $this->pass = $dbinfo->pass ?? "";
        $this->dsn = $dbinfo->DSN ?? "mysql:host=" . $this->host . ";dbname=" . $this->db_name;

        $this->isDebug = $isDebug;

        $fulldsn = $this->dsn . ";charset=utf8mb4";
        // echo("fulldsn:" . $fulldsn).PHP_EOL;
        
        try {
            $this->dbh = new PDO($fulldsn, $this->user, $this->pass);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if ($this->isDebug) {
                error_log("DB接続成功 - DSN: " . $fulldsn. PHP_EOL);
            }
        } catch (PDOException $e) {
            throw new Exception("DB接続エラー: " . $e->getMessage().PHP_EOL);
        }
    }

    /**
     * 値の型に基づいてPDOパラメータ型を判定
     */
    private function getParamType($value) {
        if (is_null($value)) return PDO::PARAM_NULL;
        if (is_int($value)) return PDO::PARAM_INT;
        if (is_bool($value)) return PDO::PARAM_BOOL;
        return PDO::PARAM_STR;
    }

// タイトル: SELECT・INSERT・UPDATE・DELETEクエリの実行メソッド
// 引数: SQLクエリ、パラメーター（連想配列 or 配列）
// 返り値: PDOStatementオブジェクト
// 処理内容: SQLのテーブル操作クエリを準備・実行する
//           ・パラメータの型を判定し適切なbindValueを設定しクエリ準備
//使い方：本クラスの末尾に記載


    public function execute($sql, $params = null) {
        if ($this->isDebug) {
            error_log("[DEBUG] SQL実行: " . $sql);
            error_log("[DEBUG] パラメータ: " . print_r($params, true));
        }

        try {
            $stmt = $this->dbh->prepare($sql);
            
            if ($params) {
                foreach ($params as $key => $value) {
                    $paramType = $this->getParamType($value);
                    $paramKey = is_numeric($key) ? $key + 1 : $key;
                    //?のパラメータ方式への対応。PDOは1から始まるインデックスで指定する必要があるが配列のキーは０から開始するので＋１している
                    $stmt->bindValue($paramKey, $value, $paramType);
                    
                if ($this->isDebug) {
                      error_log("[DEBUG] バインド: " . $paramKey . " = " . print_r($value, true) . " (型: " . $paramType . ")");
                    }
                }
            }

            $stmt->execute();
            return $stmt;

        } catch (PDOException $e) {
            if ($this->isDebug) {
                error_log("SQLエラー: " . $e->getMessage());
                error_log("SQL: " . $sql);
                error_log("パラメータ: ". print_r($params, true));
            }
            throw new Exception("クエリ実行エラー: " . $e->getMessage());
        }
    }

    /**
     * SELECT文の実行用メソッド（少数レコードの取得）
     */
    public function select($sql, $params = null) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * INSERT/UPDATE/DELETE文の実行用メソッド
     */
    public function modify($sql, $params = null) {
        if ($this->isDebug) {
            error_log("SQL: " . $sql);
            error_log("Parameters: " . print_r($params, true));
        }
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }


// タイトル: 大量データ処理用の順次SELECT処理メソッド
// 引数: SQLクエリ、パラメーター（連想配列 or 配列）
// 返り値: ジェネレーター（yeild）、
// 処理内容: SQLのテーブル操作クエリを準備・実行する
//           ・パラメータの型を判定し適切なbindValueを設定しクエリ準備
//使い方：本クラスの末尾に記載


    /**
     * SELECT文の実行用メソッド（大量レコードの取得）
     * 結果を1行ずつ取得できるイテレータを返却
     * CSV出力やAPIデータ通信向け
     * @@@@@@@@@テスト中@@@@@@@@@@@@@@@
     */
    public function selectEach($sql, $params = null) {
        $stmt = $this->execute($sql, $params);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->isDebug) {
                error_log("[DEBUG] 取得行: " . print_r($row, true));
            }
            yield $row;
        }
    }

    /**
     * SELECT文の実行用メソッド（大量レコードの取得）
     * 実行結果を1行ずつコールバック関数で処理
     * バッチ処理やデータ移行向け
     * @@@@@@@@@テスト中@@@@@@@@@@@@@@@
     */
    public function selectWithCallback($sql, $params, callable $callback) {
        $stmt = $this->execute($sql, $params);
        $rowCount = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rowCount++;
            if ($this->isDebug) {
                error_log("[DEBUG] 処理行数: " . $rowCount);
            }
            
            $callback($row, $rowCount);
        }
        
        return $rowCount;
    }

}

