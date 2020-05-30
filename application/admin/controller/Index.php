<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Controller;
use think\Db;
//use app\admin\model\RecordViewModel;
// 声明控制器

class Index extends Controller{

	// 登录页面方法
	public function Index(){
		return $this->fetch();
	}

	public function register(){
		return $this->fetch();
	}

	public function do_register(){
		$username = $_POST['username'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];
		if(empty($username)){
			$this->error('用户名不能为空');
		}
		if(empty($password)){
			$this->error('密码不能为空');
		}
		if ($password != $repassword) {
			$this->error('确认密码错误');# code...
		}

		$user = Db::table('user')->where('username',$username)->find();
		if (!empty($user)) {
			$this->error('用户名已存在');
		}
		$data = array(
			'username'=>$username,
			'password'=>md5($password),
			'auth'=>'normal'
		);
		$result = Db::table('user')->insert($data);
		if(!$result){
			$this->error('注册失败！'.$model->gerDbError());
		}
		$this->success('注册成功，请登录',url('admin/Index/index'));
	}

	public function do_login(){
		$username = $_POST['username'];
		$password = $_POST['password'];
		$user = Db::table('user')->where('username',$username)->find();
		if (empty($user)||$user['password']!=md5(($password))) {
			$this->error('账号或密码错误');# code...
		}

		session('user.userid',$user['id']);
		session('user.username',$user['username']);
		$this->success('登录成功','User/homepage');
	}

	public function logout(){
		if (!session('user.userid')) {
			$this->error('请登录');
		}
		session_destroy();
		$this->success('退出登录成功',url('Index/index'));
	}

	public function test(){
		return $this->fetch();
	}
}


 ?>