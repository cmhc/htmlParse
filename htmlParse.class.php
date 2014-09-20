<?php
require('http/http.class.php');
class htmlParse extends HTTP
{
  /**
   * 解析后的内容
   * @access public
   * @var array
   */
  public $content;
  
  /**
   * 抓取的页面地址
   * @access private
   * @var string
  */
  private $url;
  
  
  /**
   * 设置需要抓取的地址
   * @param string
  */
  public function setUrl($url){
    $this->url = $url;
  }
  
  /**
    * 解析函数,抓取网页，并且解析到一个数组里面
    * @param @url string
    * @return array
  */
  public function parse(){


    $html_content = $this->get($this->url);

    preg_match_all('|<div(.*?)class="([^\"]*)"([^>]*?)>([^^]*?)</div>|',$html_content,$matches);

    $this->content = array();
    
    for($i = 0,$j = count($matches[0]) ; $i<$j ;$i++){
      //拆分含有空格的class
      if( strchr( $matches[2][$i] ,' ' ) !== false ){
        
        $classes = explode(' ',$matches[2][$i]);
        foreach($classes as $class )
          $this->content[$class][] = $matches[4][$i];//将class作为键名称，class内容作为键值

      }else{
      
        //不含有空格则直接组合
        $this->content[$matches[2][$i]][] = $matches[4][$i];//将class作为键名称，class内容作为键值
        
      }
      
    }
    
  }
  
}


?>