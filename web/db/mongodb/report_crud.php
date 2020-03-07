<?php

require_once dirname(__FILE__)."/db_handler.php";

/**
 * 错误报告
 */
class ReportCRUD {

  protected $mongo_db = null;

  public function __construct() {
    if ( is_null($this->mongo_db) ) {
      $this->mongo_db = new DBHandler('mobox');
    }
  }

  public function report($err) {
    if(empty($err)){exit;}
    $data['type'] = $err['type'];
    $data['mid'] = $err['mid'];
    $data['epid'] = $err['epid'];
    $data['content'] = $err['content'];
    $data['latime'] = date.time()*1000;
    $data['reply'] = '';

    $mongo_db = new DBHandler('mobox');
    $mongo_db->insert($data, "report_log");
  }
}
?>