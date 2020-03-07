<?php

  require_once dirname(__FILE__)."/db_handler.php";


/**
 * 读取视频信息，构建静态页面
 * 1. 读 cover info
 * 2. 读 playgate
 */
class MediaCRUD {

  protected $mongo_db = null;
  protected $media_collec  = 'mediascover';

  public function __construct() {
    if ( is_null($this->mongo_db) ) {
      $this->mongo_db = new DBHandler('mobox');
    }
  }

  public function playgate ($ep) {
    $gates = Array();
    if(!empty($ep)){
      $gates = $this->mongo_db->query("playgate",
          ['isoff'=>'0', 'inscope'=>['$regex'=>$ep['playgate'], '$options' => 'i']],
          [ 'projection' => ['inscope'=>0, 'isoff'=>0], '$sort' => ['hit' => -1]]);
    }
    return $gates;
  }

  function get_related_medias($querypar, $limit = 12) {
      if(empty($querypar)){
          return [];
      }
      
      $options = [
          'projection' => ['_id'=>1, 'title'=>1, 'title_en'=>1, 'year'=>1, 'area'=>1, 'pic'=>1, 'mocode'=>1, 'type'=>1,
                           'langue'=>1, 'hot'=>1, 'director'=>1, 'actor'=>1, 'desc'=>1, 'subtype'=>1, 'epsprog'=>1],
          'limit' => $limit
      ];
      $k_title = trim($querypar['title']);
      $k_title = '.*'.str_replace(' ', '.*', $k_title).'.*';
      $k_title = new MongoDB\BSON\Regex($k_title); 
      $k_actor = isset($querypar['actor']) ? $querypar['actor'] : [];
      $k_director = isset($querypar['director']) ? $querypar['director'] : [];
      $k_year = isset($querypar['year']) ? $querypar['year'] : [];

      $ft_or = [
                  ['title' => ['$regex'=>$k_title, '$options' => 'i']], 
                  ['title_en' => ['$regex'=>$k_title, '$options' => 'i']],
                  ['alias' => ['$regex'=>$k_title, '$options' => 'i']]
              ];
      if(!empty($k_actor)) { array_push($ft_or, ['actor' => ['$in' => $k_actor]]); }
      if(!empty($k_director)) { array_push($ft_or, ['director' => ['$in' => $k_director]]); }
      if(!empty($k_year)) { array_push($ft_or, ['year' => $k_year]); }

      $filter = [
        '$and' => [
          ['$or'=>[['isoff' => 0],['isoff' => '0']]],
          ['$or'=>$ft_or ]
        ],
        '_id' =>['$ne' => new \MongoDB\BSON\ObjectId($querypar['_id']['$oid'])]
      ];
      $media_cover = $this->mongo_db->query($this->media_collec, $filter, $options);
      return json_decode(json_encode($media_cover),TRUE);
  }

  function get_medias_M_code($par_motoken, $offset=0, $pagesize=1){
      $par_motoken = trim($par_motoken);
      if(empty($par_motoken)){
          $rdp = new ReDirPage();
          return $rdp->redir_404();
          exit;
      }
      
      $options = [
          'projection' => ['_id'=>1,'cid'=>1, 'title'=>1, 'title_en'=>1, 'year'=>1, 'area'=>1, 'pic'=>1, 'mocode'=>1, 'type'=>1,
                           'hot'=>1, 'director'=>1, 'actor'=>1, 'desc'=>1, 'subtype'=>1, 'eps'=>1],
          'skip' => $offset, 
          'limit' => $pagesize, 
          'sort' => ['year' => -1, 'hot' => -1]
      ];

      $filter = [
        '$or'=>[['isoff' => 0],['isoff' => '0']],
        'mocode' => $par_motoken
      ];
      
      $media_cover = $this->mongo_db->query($this->media_collec, $filter, $options);
      $eps = null;
      foreach ($media_cover as $doc) {
          if(!empty($doc->eps)){
            $eps = json_decode(json_encode(array_filter($doc->eps, function($x){
              return intval($x->isoff) === 0;
            })),TRUE);

            // $doc->eps = $this->playgate($eps);
          }
      }
      return json_decode(json_encode($media_cover),TRUE);
  }

  private $media_type = ['其他', '电影', '剧集', '动漫', '综艺' ];
  function get_medias_today_new(){
    $today = date("Y-m-d");
    $yestoday = date("Y-m-d",strtotime("-1 day"));
    $options = [
        'projection' => ['_id'=>1, 'title'=>1, 'title_en'=>1, 'year'=>1, 'area'=>1, 'pic'=>1, 'newtoday'=>1, 'type'=>1, 'subtype'=>1],
        'sort' => ['type' => 1, 'year' => -1]
    ];
    $filter = [
      '$and' => [
        ['$or'=>[['isoff' => 0],['isoff' => '0']]],
        ['$or'=>[['newtoday' => $today],['newtoday' => $yestoday]]]
      ]
    ];
    $media_cover = $this->mongo_db->query($this->media_collec, $filter, $options);
    foreach ($media_cover as $doc) {
        if(!empty($doc->type)){
          $doc->type = $this->media_type[$doc->type];
        }
    }
    return json_decode(json_encode($media_cover),TRUE);
  }


  function get_medias_id($par_id){
      $par_motoken = trim($par_id);
      if(empty($par_id)){
          echo json_encode(["code"=>"404", "msg"=>'no data']);
          exit;
      }
      
      $options = [
          'projection' => ['_id'=>1, 'title'=>1, 'title_en'=>1, 'year'=>1, 'area'=>1, 'pic'=>1, 'type'=>1,
                          'director'=>1, 'actor'=>1, 'desc'=>1, 'subtype'=>1, 'imdb'=>1, 'dbid'=>1, 'alias'=>1]
      ];

      $filter = [
        '$or'=>[['isoff' => 0],['isoff' => '0']],
        '_id' => new \MongoDB\BSON\ObjectId($par_id)
      ];
      
      $media_cover = $this->mongo_db->query($this->media_collec, $filter, $options);
      foreach ($media_cover as $doc) {
          if(!empty($doc->type)){
            $doc->type = $this->media_type[$doc->type];
          }
      }
      return json_decode(json_encode($media_cover),TRUE);
  }
}
?>