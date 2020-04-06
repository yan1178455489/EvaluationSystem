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
}