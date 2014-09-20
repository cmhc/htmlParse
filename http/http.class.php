<?php
/**
 *  一个HTTP 请求类
 *  根据php的curl写的一个http请求类，可以实现以下功能
 *  1.获取远程内容，简单的get请求
 *  2.发送post请求
 *  3.自定义useragent信息,referer来源等头部信息
 *  4.支持cookie的发送与接收
 *  5.支持使用代理连接
 *  6.支持并发请求，随机代理功能，但请勿用于非法用途！
 *  程序部分代码思路来自于php官方网站
 *  @author 胡超
 *  @email hu_chao@139.com
 */

class HTTP{
  
  public $head_info;//自定义头部数据

  private $optmap;//curl 设置选项的数组映射关系
  
  private $curlopt;//最终的设置数组
  
  private $curl;//curl句柄
  
  private $curl_queue;//curl多线程列队

  /**
   * 构造函数
   * 作用是
   * 1.初始化useragent，referer，cookie头部信息
   * 2.初始化curl
   */
  public function __construct(){
    
    $this->optmap = array(
      'header'        => CURLOPT_HEADER,
      'returntransfer'=> CURLOPT_RETURNTRANSFER,
      'useragent'     => CURLOPT_USERAGENT,
      'referer'       => CURLOPT_REFERER,
      'cookie'        => CURLOPT_COOKIE,
      'url'           => CURLOPT_URL,
      'proxy'         => CURLOPT_PROXY,
      'port'          => CURLOPT_PROXYPORT,
    );
    
    $this->default_opt = array(
      'useragent'=>'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0',//浏览器useragent信息
      'header'=>true,
      'returntransfer'=>true
    );
    
    $this->init();

  }
  
  public function __destruct(){
  
    curl_close($this->curl);
    
  }
  
  /**
   * 单线程初始化，执行curl_init，将句柄赋值给$this->curl
   * @param none
   * @return none
   */
  private function init(){

    $this->curl = curl_init();
    $this->curlopt = array();
    $this->set($this->default_opt);//设置初始信息
    
  }
  
  /**
   * 多线程初始化函数
   * 对多线程处理事务进行初始化工作，并将句柄赋值给$this->curl_queue。仅仅在执行多url请求的时候才会执行此方法
   * @param none
   * @return none
   */
  private function multi_init(){
  
    $this->curl_queue = curl_multi_init();
    
  }
  
  
  /**
   * 设置发送的post数据
   * @param $post array 发送post数据的键值映射数组
   * @return none 
   */
  private function set_post($post){
  
    curl_setopt($this->curl,CURLOPT_POST,1);
    curl_setopt($this->curl,CURLOPT_POSTFIELDS,$post);
    
  }
  
  /**
    执行简单的http请求，将请求的结果返回
    @param string $url 任意网址
    @return string 网站内容
  */
  public function get($url){
    curl_setopt($this->curl,CURLOPT_URL,$url);
    return $this->run();
  }
  
  /**
    post请求
    @param string $url url内容 
    @param array $post 需要发送post请求的数组
  */
  public function post($url,$post){
  
    curl_setopt($this->curl,CURLOPT_URL,$url);
    $this->set_post($post);
    return $this->run();
    
  }
  
  
  /**
   * 执行最后的curl步骤，发送以及接收数据，作为结果返回
   * @param none
   * @return 请求得到的结果
   */
   
  public function run(){
  
    return curl_exec($this->curl);
    
  }
  
  
  /**
   * 批量设置curl参数，参数并非curl的参数，而是去掉CURLOPT之后的参数，程序会将参数映射到CURLOPT上面
   * @param array $option 有效的参数为一个 '选项=>值'的数组。 可选的选项有
   * header=>0|1
   * returntransfer=>0|1
   * useragent=>浏览器UA
   * referer=>来源地址
   * cookie=>cookie内容
   * url=>请求的地址
   * proxy=>代理ip
   * port=>代理端口
   */
  public function set($options){
  
    foreach($options as $opt=>$value){
      $this->curlopt[$this->optmap[$opt]] = $value;//设置参数
    }
    curl_setopt_array($this->curl,array_filter($this->curlopt));
    
  }
  
  

  /**
   * 单独设置cookie，将cookie加载到设置项中。
   * @param string $cookie cookie内容
   * @return none
   */
  public function set_cookie($cookie){
  
    curl_setopt($this->curl,CURLOPT_COOKIE,$cookie);
    
  }
  
  /**
   *单独设置代理
   * @param string $proxy 比如172.0.0.1:80,端口可加可不加
   */
  public function set_proxy($proxy){
  
    if(strpos($proxy,':')){
      $proxy_array = explode(':',$proxy);
      $ip = $proxy_array[0];
      $port = $proxy_array[1];
    }else{
      $ip = $proxy;
    }
    
    curl_setopt($this->curl,CURLOPT_PROXY,$ip);
    
    if(isset($port))
      curl_setopt($this->curl,CURLOPT_PROXYPORT,$port);
  }
  
  /**
   * 单独设置来路
   * @param string $referer 参数为来路的地址
   * @return none
  */
  public function set_referer($referer){
  
    curl_setopt($this->curl, CURLOPT_REFERER, $referer); //构造来路
    
  }
  
  /**
   * 单独设置超时时间
   * @param int $timeout 参数为时间限制，单位为秒，最小值为1
   * @return none
   */
  public function set_timeout($timeout){
  
    curl_setopt($this->curl,CURLOPT_TIMEOUT, $timeout);

  }
  

  /**
   * 多线程请求方法
   * @param array $url_array 一个url数组
   * @return array 请求状态结果
   */
  public function multi_get($url_array , $content = false){
    $this->multi_init();//调用多线程初始化，对$curl_queue复制

    foreach($url_array as $url){
      $curl = curl_init();
      curl_setopt_array(
          $curl,
          array(
            CURLOPT_URL=>$url,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_HEADER=>1,
            CURLOPT_NOSIGNAL=>true,
            CURLINFO_HEADER_OUT=>true
          )
       );
      curl_multi_add_handle($this->curl_queue,$curl);//添加执行列队
    }
    $responses = array();
    do{
        while( ( $code = curl_multi_exec($this->curl_queue,$active ) ) == CURLM_CALL_MULTI_PERFORM );
        if ($code != CURLM_OK) { break; }
        //找出哪一个是已经完成的请求
        while ($done = curl_multi_info_read($this->curl_queue)) {
        
            // 获取请求的结果
            $info = curl_getinfo($done['handle'],CURLINFO_HEADER_OUT);            
            $error = curl_error($done['handle']);
            if($content){
              $html = curl_multi_getcontent($done['handle']);
              $responses[] = compact('info', 'error','html');
            }else{
              $responses[] = compact('info', 'error');
            }
            
            //移除已经完成的列队
            curl_multi_remove_handle($this->curl_queue, $done['handle']);
            curl_close($done['handle']);
            
        }
        // 锁定输出的内容
        if ($active > 0) {
            curl_multi_select($this->curl_queue, 0.5);
        }
        
        
    }while ($active);
 
    curl_multi_close($this->curl_queue);
    return $responses;    
  }
  
  
  
  
}
?>