<?php
namespace app\index\controller;

use think\Controller;

use think\View;

use think\Db;
class Index extends Controller
{
    public function index()
    {
        return $this->fetch('login');
    }

    // 加载页面
    public function login(){
        $link = mysqli_connect('localhost', 'root', 'root', 'eplatform');
        if (!$link){
            echo"<script>alert('数据库连接失败！')</script>";
        }else {
            if (isset($_POST['submit'])){
                $query = "select * from login where name = '{$_POST['name']}' and pw = '{$_POST['pw']}'";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 1){
                    $this->success('登录成功', 'admin/index/index');
                }
                else{
                    $this->error('用户名或密码错误！');
                }
            }
        }
    }

    public function regi(){
        return $this->fetch('register');
    }

    public function register(){
    	$link = mysqli_connect('localhost', 'root', 'root', 'eplatform');
        if (!$link) {
            die('Could not connect: ' . mysql_error());
        }else {
            if (isset($_POST['submit'])){
                if ($_POST['pw'] == $_POST['repw']){
                    $query = "insert into login (name,pw) values('{$_POST['name']}','{$_POST['pw']}')";
                    echo "$query";
                    $result=mysqli_query($link, $query);
                    $this->success('注册成功', 'index/index');
                }else {
                    $this->error('两次输入密码不一致！');
                }
            }
        }

    }

    // 模板赋值

    public function fuzhi(){

    	$name="云知梦";
    	$city="太原";

    	// 变量分配

    		// 控制器类中的assign方法

	    		// $this->assign('name',$name);
	    		// $this->assign('city',$city);

    			// 加载页面
    			// return view();

    		// 通过fetch方法

    			// return $this->fetch('',['name'=>$name,'city'=>$city]);

    		// 助手函数

    			// return view('',['name'=>$name,'city'=>'西安']);

    		// 对象赋值
    			$this->view->name="浩哥";
    			$this->view->city="临汾";

    			return view();

    	
    }

    // 引入联想首页

    public function lianxiang(){

    	// fetch 方法
    	return $this->fetch('',[],['__HOMES__'=>'/static/home/public','__ABC__'=>'abcdefg']);
    	// view 函数
    	return view('',[],['__HOMES__'=>'/static/home/public','__ABC__'=>'abcdefg']);
    }

    // 模板渲染

    public function xuanran(){

    	// 默认加载当前模块 当前控制器 当前方法对应的页面
    		// return $this->fetch();

    	// 指定加载页面
    		// 加载当前模块 当前控制器下的 用户定义页面
    		// return $this->fetch('jiazai');

    		// 加载当前模块 User控制器 jiazai页面
    		return $this->fetch('User/jiazai');
    }

    // 模板标签

    public function tags(){
    	// 分配字符串

    		$this->assign("str","TP5.0 非常简单非常适合初学者");

    	// 分配数组

    		$data=[
    			'name'=>'张三',
    			'age'=>18,
    			'sex'=>'妖'
    		];

    		$this->assign("data",$data);

    	// 分配时间

    		$this->assign('time',time());
    		$this->assign('pass','123');

    		$this->assign('status',3);

    		$this->assign('a',10);
    		$this->assign('b',5);

    	return $this->fetch();
    }


    // 系统变量

    public function sys(){


    	return $this->fetch();
    }

    // 页面包含

    public function baohan(){

    	return $this->fetch();
    }

    // volist 


    public function volist(){

        // 查询数据

        $data=Db::table("user")->select();

        // 分配数据

        $this->assign('data',$data);
        $this->assign('empty',"<b>数据不能为空</b>");

        // 加载页面
        return $this->fetch();
    }

    // foreach

    public function foreachs(){

        // 查询数据

        $data=Db::table("user")->select();

        // 分配数据

        $this->assign('data',$data);
        $this->assign('a',10);
        $this->assign('b',20);
        $this->assign('week',7);


        $type=Db::table("type")->select();

        foreach ($type as $key => &$value) {
            # code...

            $value['goods']=Db::table("goods")->where("cid = $value[id]")->select();
        }

        $this->assign('type',$type);


        // 加载页面
        return $this->fetch();
    }

}
