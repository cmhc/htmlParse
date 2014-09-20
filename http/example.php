<?php
  header("Content-type:text/html;charset=utf-8");
  echo '<pre>';
  $start = microtime(true);
  require('class.http.php');
  $http = new HTTP();
  
  /*
    【请求一个url地址，get方法 get($url)】
    --------------------------------------------------------
    参数$url url地址
    --------------------------------------------------------
    例子：$http->get('http://localhost/app/HTTP/server.php');

  */
  
  /*
    【请求一个url地址，post方法 post($url,$data)】
    --------------------------------------------------------
    参数$url url地址
    参数$data post数据的键值映射数组
    --------------------------------------------------------
    例子：$http->post('http://localhost/app/HTTP/server.php',array('post'=>1,'foo'=>'bar'));

  */

  /*
    【设置请求的http代理方法 set_proxy($proxy)】
    --------------------------------------------------
    参数$proxy 可用代理，比如198.144.155.32::7808
    --------------------------------------------------
    示例$http->set_proxy(198.144.155.32::7808)
  */
  
  /*
    【设置请求的cookie方法 set_cookie($cookie)】
    --------------------------------------------------------
    参数$cookie : 一个字符串，类似cookie1:1; cookie2:1的形式
    --------------------------------------------------------
    示例: $http->set_cookie("SLnewses=1; WPTLNG=1;");
    在服务端可用$_COOKIE获取到
  */
  
  
  /*
    【设置请求的referer来源方法 set_referer($url)】
    --------------------------------------------------------
    参数$url : url地址字符串
    --------------------------------------------------------
    示例: $http->set_referer("http://www.ttwrite.com");
  */
  
  /*
    【设置超时时间 set_timeout($second)】
    --------------------------------------------------------
    参数 $second 秒
    --------------------------------------------------------
    示例：$http->set_timeout(2);
  */
  
  
  
  /*
    【多线程请求方法 multi_get($url_array,$output_content)】
    ------------------------------------
    参数url_array : url数组
    参数 $output_content : 是否输出内容，默认为false，不输出。设置为true则返回的数组包含网页内容
    ------------------------------------
    示例：
    $res = $http->multi_get(array(
      'http://www.sina.cn',
      'http://ww.qq.cn',
      'http://www.baidu.com',
      'http://www.ttwrite.com',
      'http://www.w3school.com.cn',
      'http://www.xici.net.co'
    ),false);
    print_r($res);
    -------------------------------------
  */
  
  $end = microtime(true);
  echo '页面载入耗时：'.($end-$start);
  
?>