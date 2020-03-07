<?php

  $_DB_HANDLER = 'mongodb';
  
  $_Page_Tpl_ = [
    'about' => 'tpl/about.tpl.html',
    'contact' => 'tpl/contact.tpl.html',
    'comment' => 'tpl/comment.tpl.html',
    '404' => 'tpl/404.tpl.html',
    'index' => 'tpl/index.tpl.html',
    'player' => 'tpl/player.tpl.html',
    'player_eps' => [
      'movie' => 'tpl/player_eps_movie.tpl.html',
      'tv' => 'tpl/player_eps_tv.tpl.html'
    ],
    'player_screen' => [
      'html' => [
        'tpl' => 'tpl/player_screen_frm.tpl.html',
        'fun' => 'parse_html_player_screen_frm',
        'extname' => ['.html']
      ],
      'clappr' => [
        'tpl' => 'tpl/player_screen_clappr.tpl.html',
        'fun' => 'parse_html_player_screen_clappr',
        'extname' => ['.m3u8', 'mp4']
      ],
      'dplayer' => [
        'tpl' => 'tpl/player_screen_dplayer.tpl.html',
        'fun' => 'parse_html_player_screen_dplayer',
        'extname' => ['magnet:', '.mpd', '.flv'],
        'cdnjs' => [
          ".m3u8" => "https://cdn.jsdelivr.net/npm/hls.js@latest",
          "magnet:" => "https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js",
          ".mpd" => "http://reference.dashif.org/dash.js/nightly/dist/dash.all.min.js",
          ".flv" => "https://cdn.jsdelivr.net/npm/flv.js@1.5.0/dist/flv.min.js"
        ]
      ],
      'qiniu' => [
        'tpl' => 'tpl/player_screen_qiniu.tpl.html',
        'fun' => 'parse_html_player_screen_qiniu',
        'extname' => ['.webm'],
      ]
    ]
  ];



  function array_filter_callback_get_years($value, $key){
    $val = intval($value);
    return $val && $val>1887 && $val<2888;
  }
?>