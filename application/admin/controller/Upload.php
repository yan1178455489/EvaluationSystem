<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Db;
use think\Controller;
use app\admin\model\Dataset;


/**
* 压缩和解压文件
*/
class Upload extends Controller
{
	
	function unzip_file($zipName, $dest){

	  // 检测要解压压缩包是否存在

	  if(!file_exists($zipName)){

	    return false;

	  }
	  
	  //检测目标路径是否存在

	  if(!is_dir($dest)){

	    mkdir($dest,0777,true);

	  }
	  
	  $zip=new \ZipArchive();

	  if($zip->open($zipName)===true){

	    $zip->extractTo($dest."/");

	    $zip->close();

	    return true;

	  }else{

	    return false;

	  }

	}

	// 返回数据集页面
	public function upload_dataset(){
		return $this->fetch();
	}

	public function do_upload_dataset(){
		$dataset = Dataset::where('name',$_POST['dataset_name'])->select();
		if (!empty($dataset)) {
			$this->error('数据集名已存在');
		}
		// 获取绝对路径
		$path = getcwd().'/';
		// 定义压缩包要保存的路径
		$filepath= $path.'upload_dataset/';
		// 定义解压后文件要保持的路径
		$unzip_path = $path.$_POST['dataset_name'];
		// 移动到框架应用根目录/public/ 目录下
		$data_file = request()->file('dataset');
		$data_file->move($filepath,'');  
		$file_name = $_FILES['dataset']['name'];
		// 解压
        if (!$this->unzip_file($filepath.$file_name, $unzip_path)) {
			var_dump($Hhh);
         	$this->error('解压失败');
        } 
		
		$data = array(
			'username'=>session('user.username'),
			'name'=>$_POST['dataset_name'],
			'type'=>$_POST['type'],
			'create_time'=>date("Y-m-d H:i:s",time())
		);
		Db::table('dataset')->insert($data);

		$this->success('上传成功', 'Index/homepage');
	}

	// 访问上传算法页面
	public function upload_alg(){
		return $this->fetch();
	}

	// 上传算法到数据库
	public function do_upload_alg(){
		$alg = Db::table('algorithm')->where('algname',$_POST['algname'])->find();
		if (!empty($alg)) {
			$this->error('算法名已存在');
		}
		$alg_file = request()->file('alg');
		$config = request()->file('config');
	    // 移动到框架应用根目录/public/ 目录下
		$string = strrev($_FILES['alg']['name']);
        $array = explode('.',$string);
        $array[0] = strrev($array[0]);
	    $info = $alg_file->move('./','');   
	    $info = $config->move('./','');   
		$dataset = $_POST['dataset'];
		// 拼接数据集字段
		$datasets = "";
		$n = count($dataset);
		if ($n>0) {
			for ($i=0; $i < $n-1; $i++) { 
				$datasets = $datasets.$dataset[$i].",";
			}
			$datasets = $datasets.$dataset[$n-1];
		}
		$data = array(
			'algname'=>$_POST['algname'],
			'username'=>session('user.username'),
			'reference'=>$_POST['reference'],
			'dataset'=>$datasets,
			'algtype'=>$_POST['algtype'],
			'need_computed'=>$_POST['need_computed'],
			'filetype'=>$array[0],
			'create_time'=>date("Y-m-d H:i:s",time())
		);
		Db::table('algorithm')->insert($data);

		$this->success('上传成功', 'Index/homepage');
	}
}