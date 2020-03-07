<?php
    header('Content-type: application/json;charset=utf-8');
    // 如果不是微信
    // $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
    // if(!strrpos($browser, 'micromessenger')){
    //     echo json_encode(array("code"=>"500", "msg"=>"invalid server")); 
    //     exit;
    // }

    require_once 'util/com.php';
    require_once "db/$_DB_HANDLER/media_crud.php";

    $crud = new  MediaCRUD();
    $media = $crud->get_medias_today_new();
    if(empty($media)){
        echo json_encode(array("code"=>"500", "msg"=>"invalid code")); 
    }else{
        echo json_encode(["code"=>"200", "data"=>$media]);
    }
    exit;
?>