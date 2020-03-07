<?php
  header('Content-type: text/html;charset=utf-8');
  // 如果不是微信，就跳转到主站
  $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
  if(!strrpos($browser, 'micromessenger')){
    $gourl = empty($_GET['mo']) ? 'Location: http://dy.ayioz.com' : 'Location: http://dy.ayioz.com/h/'.$_GET["mo"].'.html';
    header($gourl);
    exit;
  }

  require_once 'util/com.php';
  require_once "db/$_DB_HANDLER/media_crud.php";
  require_once 'redir.php';

  if(!empty($_GET['mo']))
  {
    $mo = $_GET['mo'];
    $mo = trim($mo);
    if(empty($mo)){
      $rdp = new ReDirPage();
      echo $rdp->redir_404();
      exit;
    } else {
      $crud = new  MediaCRUD();
      $media = $crud->get_medias_M_code($mo);
      $media = array_shift($media);
      // 如果指明了ep参数，则只加载player部分页面
      echo empty($_GET['ep']) ? parse_html_index($media) : parse_html_player($media);
      exit;
    }
  }

  /**
   * page 模版嵌套关系
   * index.tpl
   *    player.tpl
   *        screen.tpl
   *        eps.tpl
   *    about.tpl
   *    contact.tpl
   *    comment.tpl
   */
  
  // 主页
 function parse_html_index($play){
    if(is_null($play) || empty($play)){
      $rdp = new ReDirPage();
      return $rdp->redir_404();
      exit;
    }
    $TPL = $GLOBALS['_Page_Tpl_'];

    $html = file_get_contents($TPL['index']);
    $html = str_replace('{{$title$}}',$play['title'], $html);
    $html = str_replace('{{$mo$}}',$play['mocode'], $html);

    $html_player = parse_html_player($play);

    $html = str_replace('{{$player$}}', $html_player, $html);
    return $html;
  }

  // 播放页
  function parse_html_player($play){
    if(is_null($play) || empty($play)){
      $rdp = new ReDirPage();
      return $rdp->redir_404();
      exit;
    }
    $TPL = $GLOBALS['_Page_Tpl_'];

    $html = file_get_contents($TPL['player']);
    $html = str_replace('{{$year$}}',$play['year'], $html);
    $html = str_replace('{{$area$}}',empty($play['area']) ? '' : implode(',',$play['area']), $html);
    // $html = str_replace('{{$hot$}}',$play['hot'], $html);
    $html = str_replace('{{$director$}}', empty($play['director'])? '' : implode(', ', $play['director']), $html);
    $html = str_replace('{{$actor$}}', empty($play['actor']) ? '' : implode(', ', $play['actor']), $html);
    $html = str_replace('{{$subtype$}}', empty($play['subtype']) ? '' : implode(', ', $play['subtype']), $html);
    $html = str_replace('{{$desc$}}',$play['desc'], $html);

    $html = str_replace('{{$ep-type-css$}}', $play['type'] == '1'?'weui-cells':'weui-grids', $html);
    $html_eps = parse_html_player_eps($play);
    $html = str_replace('{{$eps$}}', $html_eps, $html);

    $eps = empty($play['eps']) ? [] : $play['eps'];
    // 获取当前播放的ep
    $playing_ep = empty($_GET['ep']) ? end($eps) : array_filter($eps, function($v){
      return $v['epid'] == $_GET['ep'];
    });

    $playing_ep = empty($_GET['ep']) ? $playing_ep  : reset($playing_ep);
    $html_screen = parse_html_player_screen($playing_ep);
    $html_screen = empty($html_screen) ? '' : $html_screen;
    $html = str_replace('{{$screen$}}', $html_screen, $html);
    $html = str_replace('{{$playing-epid$}}',$playing_ep['epid'], $html);
    $html = str_replace('{{$playing-mid$}}',$play['cid'], $html);
    
    return $html;
  }

  // 分集列表
  function parse_html_player_eps($play){
    if(is_null($play) || empty($play) || empty($play['eps'])){
      $rdp = new ReDirPage();
      return $rdp->redir_404();
      exit;
    }
    $TPL = $GLOBALS['_Page_Tpl_'];
    $type = $play['type'] == '1' ? 'movie' : 'tv';
    $html_eps = file_get_contents($TPL['player_eps'][$type]);

    $eps_html_list = '';
    $eps = $play['eps'];
    foreach ($eps as $value) {
      $ep_html = str_replace('{{$title$}}', $value['title'], $html_eps);
      $eps_html_list = $eps_html_list.str_replace('{{$epid$}}', $value['epid'], $ep_html);;
    }
    return $eps_html_list;
  }

  // 屏幕
  function parse_html_player_screen($ep){
    if(is_null($ep) || empty($ep)){
      $rdp = new ReDirPage();
      return $rdp->redir_404();
      exit;
    }
    $TPL = $GLOBALS['_Page_Tpl_'];
    $url = strtolower($ep['url']);
    $parpos = stripos($url, '?');
    $url = $parpos ? trim(substr($url, 0, $parpos)) : $url;
    $exname = stripos($url, 'magnet:') ? 'magnet:' : strtolower(trim(substr($url, strripos($url, '.'))));
    $exname = empty($exname) ? '.m3u8' : $exname;
    $exname = strlen($exname) > 8 ? '.html' : $exname;
    $ep['exname'] = $exname;
    $screens = $TPL['player_screen'];
    $use_screen = null;
    foreach ($screens as $key => $value) {
       if(in_array($exname, $value['extname'])){
          $use_screen = $value;
          break;
       }
    }
    if(empty($use_screen)){ return null; }
    else{ return call_user_func($use_screen['fun'], $use_screen, $ep);}
  }
  function parse_html_player_screen_frm($screen, $ep){
    $html_screen = file_get_contents($screen['tpl']);
    $crud = new  MediaCRUD();
    $gates = $crud->playgate($ep);
    $url = $ep['url'];

    // $sheets = [];
    // // foreach ($gates as $key => $value) {
    // //   array_push($sheets, '<div class="weui-actionsheet__cell" data-playgate="'.$value -> gateurl.'">线路'.($key+1).'</div>');
    // // }
    // $html_screen = str_replace('{{$gateactionsheets$}}', implode('',$sheets), $html_screen);

    $firstgate = reset($gates);
    $playing_gate = empty($firstgate) ? '' : $firstgate->gateurl;

    $html_screen = str_replace('{{$playurl$}}', empty($playing_gate) ? '' : $playing_gate.$url, $html_screen);
    // $html_screen = str_replace('{{$playing_gate$}}', $playing_gate, $html_screen);
    // $html_screen = str_replace('{{$vurl$}}', $url, $html_screen);
    return $html_screen;
  }
  function parse_html_player_screen_dplayer($screen, $ep){
    $html_screen = file_get_contents($screen['tpl']);
    $cdnjs = $screen['cdnjs'][$ep['exname']];
    $html_screen = str_replace('{{$cdnjs$}}', '<script src="'.$cdnjs.'"></script>', $html_screen);
    $html_screen = str_replace('{{$vurl$}}', $ep['url'], $html_screen);
    return $html_screen;
  }
  function parse_html_player_screen_qiniu($screen, $ep){
    $html_screen = file_get_contents($screen['tpl']);
    $html_screen = str_replace('{{$vurl$}}', $ep['url'], $html_screen);
    return $html_screen;
  }
  function parse_html_player_screen_clappr($screen, $ep){
    $html_screen = file_get_contents($screen['tpl']);
    $html_screen = str_replace('{{$vurl$}}', $ep['url'], $html_screen);
    return $html_screen;
  }
exit;
?>