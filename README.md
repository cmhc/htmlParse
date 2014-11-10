html解析器
==========
去除html里面的标签信息，提取有用的正文
--------------------------------------

##开始

>首先，需要加载一个http库，以及htmlParse库，路径请自己定义好
	require( 'http/http.class.php' );
	require( 'htmlParse.class.php' );
>然后我们需要实例化这个类
		$HP = new htmlParse();//创建一个html解析对象
>实例化这个htmlParse类的时候，实际上http类已经在htmlParse类里面实例化了
>然后我们可能还需要设置一下url地址，设置url地址的时候，实际上就已经将网页下载下来了
	$HP->setUrl("http://www.baidu.com")
>然后，使用:
	$HP->getMainContent();
>作用就是获取网页里面最主要的内容

##使用常用api

####1.getMainContent([$depth]) 
>获取网页的主内容，默认输出三个元素的数组，可以指定$depth为1，只输出程序认为最重要的内容数组，如果$depth为0，则会将网页的内容按照div块从大到小的排序全部返回。

###2.parse([$url])
>将url网页下载下来，解析为一个class和id的数组，接下来，你就可以通过class和id找到网页的内容了比如:
	$content = $htmlParse->parse($url)
	$content['content']即可读取到这个网页里面为content的class的内容