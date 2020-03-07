<?php
  header('Content-type: application/json;charset=utf-8');

  require_once 'util/com.php';
  require_once "db/$_DB_HANDLER/media_crud.php";

  if(!empty($_GET['oid']))
  {
    $mo = $_GET['oid'];
    $mo = trim($mo);

    if(empty($mo)){
      echo array("code"=>"404", "msg"=>"invalid code");
      exit;
    } else {
      $crud = new  MediaCRUD();
      $media = $crud->get_medias_id($mo);
      $media = array_shift($media);
      
      if(empty($media)){
        echo json_encode(array("code"=>"500", "msg"=>"invalid code")); 
        exit;
      }
      
      echo json_encode($media);
      exit;
    }
  }else{
    echo json_encode(array("code"=>"500", "msg"=>"lost code"));
    exit;
  }
exit;
?>