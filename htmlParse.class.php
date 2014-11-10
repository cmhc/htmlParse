<?php
/*----------------------------------------
 * http内容解析类
 * 作者:胡超
 * 创建日期:2014/9/1
 * 最后更新日期 17:34 2014/10/28
------------------------------------------*/

class htmlParse
{
  /**
   * 解析后的内容
   * @access public
   * @var array
   */
  public $content;
  
  private $html_content;
  
  /**
   * 抓取的页面地址
   * @access private
   * @var string
  */
  private $url;
  
  public $dom;//dom操作对象
  
  /**
   * http对象，依赖于http.class.php
  */
  public $HTTP;
  
  /**
   * 设置输出方式
  */
  
  private $out;
	
	/**
	 * 网页内容编码
	*/
	private $charset;
	
  public function __construct(){
      $this->charset = array(
        'utf-8','utf8','gbk','gb2312'
        );

      $this->content = array();
      $this->HTTP = new HTTP();
      $this->out = 'multi';//设置输出为多维数组

  }
  
  public function __destruct(){
		unset($this->html_content);
		unset($this->HTTP);
  }

  /**
   * 检查网页的编码，以便对编码进行转换
  */
  public function getCharset(){
    foreach($this->charset as $charset){
      
      //if( strpos($this->html_content,"charset=\"$charset\"") != false ){
 
      $pattern = "<meta[^>]*charset=\"?$charset\"";
      
      if( preg_match("/$pattern/i",$this->html_content) ){
        //echo $charset;
        return $charset;
      }
      
    }
    return 'utf-8';
  }
  

  /**
   * 对非utf-8编码格式进行转换
  */
  private function convertEncode(){

    if( ($charset = $this->getCharset()) != 'utf8')
      $this->html_content = mb_convert_encoding($this->html_content, 'utf-8', $charset);
  }
  
  
  /**
   * 设置需要抓取的地址
   * @param string
  */
  public function setUrl($url){
    $this->url = $url;
    $request = $this->HTTP->get($url);//开始获取地址
    //print_r($request);
    $this->html_content = $this->clearNoise($request['content']);
    
    $this->convertEncode();//统一转化为utf-8的编码
      
    $this->status = $request['status'];//网页状态，比如200,301,404等等
    //print_r($this->html_content);
    
  }
  
  
  /**
   * 清除网页的无用信息，方便正则表达式匹配
   * @param $string 网页内容
   * @return $string 清除后的内容
  */
  public function clearNoise($string){
    $string = str_replace(array("\n","\t","\r","  ","</br>"),'',$string);
    $string = preg_replace("/<style.*?>(.*?)<\/style>/i","",$string);//去除内置样式
    $string = preg_replace("/<script.*?>(.*?)<\/script>/i","",$string);//去除内置脚本
    
    return $string;
  }


  /**
    * 解析函数,抓取网页，并且解析到一个数组里面
    * @param @url string 此参数为可选参数
    * @return array
  */
  public function parse($url = ''){
  
    if($url != '') $this->setUrl($url);		
    $pattern = "<div([^>]*)?(class|id|style)=\"(?<classid>[^\"]*)\"[^>]*>(?<domcontent>(?:(?!<div).)*?)<\/div>";
		//echo '<pre>'; //fordebug
		$content = $this->html_content;
		//print_r($content);
		$result = array();
		$is_match = true;
		
		while($is_match){
			preg_match_all("/$pattern/",$content,$matches);
			if(empty($matches[0]))
				$is_match = false;
			//print_r($matches);
			$nodiv = $matches[0];//最内层    
			$content = str_replace($nodiv,'',$content);
			//print_r($nodiv);
			//print_r($content);
			$result = $this->convert($matches['classid'],$matches['domcontent'],$result,$this->out);//将匹配到的信息转化为class和id数组
		}
		preg_match("/<p.*?>(.*?)<\/p>/",$content,$matches);
		if(isset($matches[1]))
			$result[] = $matches[1];
		//print_r($result);
    return $result;
    
  }
  
  
  /**
   * 选择最大的一个数组
  */
  public function getMainContent($depth = 3){
		$this->setOut('single');
		$content = $this->parse();
		$tags = $this->tagParse();
		
		/*---排序---*/
		for($i = 0,$j=count($content); $i<$j ;$i++){
			for($k = count($content)-1 ; $k>$i ; $k--){
				if( strlen( strip_tags($content[$i]) ) < strlen( strip_tags($content[$k]) ) ){
					$temp = $content[$k];
					$content[$k] = $content[$i];
					$content[$i] = $temp;
				}
			}
		}
		
		//无深度则全部返回
		if($depth == 0){
			$content['title'] = $tags['title'][0];
			unset($tags);
			return $content;
		}
		//返回需要的深度
		$depth_content = array();
		foreach($content as $key=>$item){
			$depth_content[] = $item;
			if($key >= $depth-1)
				break;
		}
		
		unset($content);
		
		if( isset($tags['title'][0]) )//含有标题则将标题组装进来
			$depth_content['title'] = $tags['title'][0];
			
		unset($tags);
		return $depth_content;
  }
   
   
  /**
   * 将classid 和 内容数组转化
   * @param $classid classid数组
   * @param $content 内容数组
   * 两个数组必须是同样的长度
  */
  private function convert($classid,$content,$result = array(),$out){
		if($out == 'multi'){
		
			for($i = 0,$j = count($classid) ; $i<$j ;$i++){
				//拆分含有空格的class
				if( strchr( $classid[$i] ,' ' ) !== false ){
        
					$classes = explode(' ',$classid[$i]);
					foreach($classes as $class )
						$result[$class][] = $content[$i];//将class作为键名称，class内容作为键值

				}else{

					//不含有空格则直接组合
					$result[$classid[$i]][] = $content[$i];//将class作为键名称，class内容作为键值

				}
			}
			
		}else{
			
			foreach($content as $item)
				//不含有空格则直接组合
				$result[] = $item;//将class作为键名称，class内容作为键值
				
		}

    return $result;
  }
  
  /**
   * 设置输出格式
   * @param $out 参数为multi则输出多维数组，其他则输出一维数组 
  */
  public function setOut($out){
		$this->out = $out;
  }
    
 
  /**
    * 根据标签进行提取
    * @param $html_content string 可选网页内容，如果没有指定网页内容，则默认使用类初始化时候设定的网页内容
  */
  public function tagParse($html_content = ''){
    if($html_content != '')
      $this->html_content = $html_content;
        
    preg_match_all('|<([^/>]*?)>([^<]*?)</(.*?)>|',$this->html_content,$matches);
    $tag_arr = array();
      
    for($i = 0,$j = count($matches[1]); $i<$j ; $i++){
      $tmp_arr = explode(" ",$matches[1][$i]);
      $tag = $tmp_arr[0];
      $tag_arr[$tag][] = $matches[2][$i];
    }
    unset($matches);
    return $tag_arr;
      
  }
  
  
  /**
   * 获取网页属性
   * @param $attr 可以是href，src，class，id等等
   * @param $html_content 网页内容，不指定则使用类初始化时候的获取的网页
  */
  public function attr($attr, $html_content = ''){
  
    if($html_content != '')
      $this->html_content = $html_content;
    
    /*获得网站首页*/
    $urlarray = parse_url($this->url);
    $baseurl = $urlarray['scheme'] . '://' . $urlarray['host'] . (isset($urlarray['port']) ? ':'.$urlarray['port'] : '');
    
    $links = array();
    
    preg_match_all("|<([^/>]*?)$attr=[\'\"]([^\'\"]*?)[\'\"]|",$this->html_content,$matches);
    
    foreach($matches[2] as $href){
			if( substr($href,0,1) == '/')
				$links[] = $baseurl.$href;
			else
				$links[] = $href;
    }
    unset($matches);
    
    return $links;
    
  }
  
  
  
  
}


?>