<?php

require_once('../../emorepoconfig/config.php');

class DatabaseInfo {

    public $host;
    public $dbName;
    public $user;
    public $pass;
    public $DSN;
    public $is_server;

    public function __construct() {
        if (
            $_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1'
        ) {
            error_log("localhost called!");
            error_log($_SERVER['SERVER_ADDR']);
            error_log($_SERVER['HTTP_HOST']);
            //localhostの場合
            $this->host = LOC_DB_HOST;
            $this->dbName = LOC_DB_NAME;
            $this->user = LOC_DB_USER;
            $this->pass = LOC_DB_PASS;
            $this->DSN = $this->retDSN($this->host, $this->dbName);
            $this->is_server = false;

        } else {
            error_log("Server called!");
            error_log($_SERVER['SERVER_ADDR']);
            error_log($_SERVER['HTTP_HOST']);
            //localhostではない場合
            $this->host = PRO_DB_HOST;
            $this->dbName = PRO_DB_NAME;
            $this->user = PRO_DB_USER;
            $this->pass = PRO_DB_PASS;
            $this->DSN = $this->retDSN($this->host, $this->dbName);
            $this->is_server = true;
            error_log($this->host);
            error_log($this->dbName);
            error_log($this->user);
            // error_log($this->pass,3,'./error.log');

        }
    }

    public function retDSN($host, $dbName) {
        return 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
    }

}