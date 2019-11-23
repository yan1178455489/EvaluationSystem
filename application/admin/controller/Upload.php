<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
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

	  if($zip->open($zipName, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE)){

	    $zip->extractTo($dest);

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
		// 移动到框架应用根目录/public/ 目录下
		$data_file = request()->file('dataset');
		$info = $data_file->move('./','');  
		$file_name = $_FILES['dataset']['name'];
		$array = explode('.',$file_name);
		$data_name = $array[0];
		$dataset = Dataset::where('name',$data_name)->select();
		if (!empty($dataset)) {
			$this->error('数据集名已存在');
		}

        if (!$this->unzip_file($file_name, './'.$data_name)) {
         	$this->error('数据集已存在');
         } 
		
		$data = array(
			'name'=>$data_name
		);
		Db::table('algorithm')->insert($data);

		$this->success('上传成功', 'Index/homepage');
	}
}