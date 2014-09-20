<?php

require( APP_PATH.'/http/http.class.php' );

class htmlParse
{
  public $html_content;
  
  private $http;

  public function parse($url){

    $HTTP = new HTTP();

    $html_content = $HTTP->get($url);
    //print_r($html_content);

    preg_match_all('|<div(.*?)class="([^\"]*)"([^>]*?)>([^^]*?)</div>|',$html_content,$matches);

    $data = array();

    for($i = 0,$j = count($matches[0]) ; $i<$j ;$i++){
      //拆分含有空格的class
      if( strchr( $matches[2][$i] ,' ' ) !== false ){
        
        $classes = explode(' ',$matches[2][$i]);
        foreach($classes as $class )
          $data[$class][] = $matches[4][$i];//将class作为键名称，class内容作为键值

      }else{

        //不含有空格则直接组合
        $data[$matches[2][$i]][] = $matches[4][$i];//将class作为键名称，class内容作为键值
      }
    }

    return $data;
    
  }
}


?>