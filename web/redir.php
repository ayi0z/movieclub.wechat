<?php
  class ReDirPage {
    public function redir_404(){
      return file_get_contents($GLOBALS['_Page_Tpl_']['404']);
    }
  }
?>