<?php

  header('Content-type: text/html;charset=utf-8');

  if(!function_exists('fastcgi_finish_request')) {
    ob_end_flush();
    ob_start();
  }
  echo '{"return":"success"}';
  if(!function_exists('fastcgi_finish_request')) {
    // header("Content-Type: text/html;charset=utf-8");
    header("Connection: close");
    header('Content-Length: '. ob_get_length());
    ob_flush();
    flush();
  } else {
    fastcgi_finish_request();
  }  



  require_once 'util/com.php';
  require_once "db/$_DB_HANDLER/report_crud.php";

  $report['type']=trim($_GET['type']);
  $report['mid'] = trim($_GET['mid']);
  $report['epid'] = trim($_GET['epid']);
  $report['content'] = trim($_GET['content']);
  
  $report_curd = new ReportCRUD();
  $report_curd->report($report);
  exit;
?>