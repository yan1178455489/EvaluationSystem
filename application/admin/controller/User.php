<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Controller;
// 导入Db类
use think\Db;
use think\Upload;
use think\Paginator;

class User extends Controller{

	// 访问选择数据集页面
	public function dataset(){
		return $this->fetch();
	}
	// 访问上传算法页面
	public function add(){
		return $this->fetch();
	}
	
	// 删除record
	public function delete(){
		$id = $_POST['ids'];
		$login_name = session('user.username');
		$db_username=Db::table('record')->where('id',$id)->value('username');
		if($login_name==$db_username){
			Db::table('record')->delete($id);
			return (int)$id;
		}
		return;
	}

	// 删除算法
	public function deletea(){
		$id=$_POST['ids'];
		$algname=Db::table('algorithm')->where('algid',$id)->value('algname');
		$login_name = session('user.username');
		$db_username=Db::table('algorithm')->where('algid',$id)->value('username');
		if($login_name==$db_username){
			Db::table('algorithm')->delete($id);
			$filename = "./".$algname.".jar"; 
			unlink($filename); //删除文件 

			$filename = "./".$algname.".txt"; 
			unlink($filename); 
			return (int)$id;
		}
		return;
	}
	// 跳转到设置参数页面
	public function setpara(){
		//dump($_POST['dataset']);
		$file_path = $_POST['alg'].".txt";
		if(file_exists($file_path)){
			$config = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
		}
		else{
			$this->error('配置文件打开失败！');
		}
		return $this->fetch('',['alg'=>$_POST['alg'],'dataset'=>$_POST['dataset'],'config'=>$config ]);
	}

	// 跳转到使用说明
	public function instructions(){
		return $this->fetch();
	}
	// // 查看历史记录方法
	// public function history(){
	// 	$data = Db::table('record')->paginate(6);

	// 	$this->assign('data',$data);
	// 	return $this->fetch();
	// }
	//搜索实验记录
	public function search(){
		$filed_content = "username";
		$content_content = "";
		if(array_key_exists('sel',$_POST)){
			$filed_content = $_POST['sel'];
		}
		if(array_key_exists('search',$_POST)){
			$content_content = $_POST['search'];
		}
		$field = $filed_content;
		$content = $content_content;
		$content = $content.'%';
		$data = Db::table('record')->whereLike($field,$content)->paginate(8);
		return $this->fetch('',['data'=>$data]);
	}

}


 ?>