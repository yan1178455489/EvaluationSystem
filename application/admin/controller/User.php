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

	// 选择数据集方法
	public function dataset(){
		return $this->fetch();
	}
	// 把算法信息插入数据库
	public function add(){
		return $this->fetch();
	}

	public function do_add(){
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
		
		$data = array(
			'algname'=>$_POST['algname'],
			'reference'=>$_POST['reference'],
			'dataset'=>$_POST['dataset'],
			'algtype'=>$_POST['algtype'],
			'need_computed'=>$_POST['need_computed'],
			'filetype'=>$array[0]
		);
		Db::table('algorithm')->insert($data);

		$this->success('上传成功', 'Index/homepage');
	}
	
	// 用户修改方法
	public function delete(){
		$id=$_POST['ids'];
		if(Db::table('record')->delete($id)){

			return (int)$id;
		}

		return;
		
	}

	public function deletev(){
		$id=$_POST['ids'];
		if(Db::execute('delete from search where id='.$id)){

			return (int)$id;
		}

		return;
		
	}

	public function deletea(){
		$id=$_POST['ids'];
		$algname=Db::table('algorithm')->where('algid',$id)->value('algname');
		if(Db::table('algorithm')->delete($id)){
			$filename = "./".$algname.".jar"; 
			unlink($filename); //删除文件 

			$filename = "./".$algname.".txt"; 
			unlink($filename); 
			return (int)$id;
		}

		return;
		
	}

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


	public function run(){
		$alg = Db::table('algorithm')->where('algname',$_POST['alg'])->find();
		$need_computed = (int)$alg['need_computed'];
		$dataset = $_POST['dataset'];
		$file_type = $alg['filetype'];
		$selects = $_POST['indicator'];

		if($need_computed == 1){
			$config = fopen($_POST['alg'].".txt", "w");
			fwrite($config,$_POST['config']);
			fclose($config);

			list($msec, $sec) = explode(' ', microtime());
	 		$starttime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	 		if($file_type == 'jar'){
				exec("java -jar ".$_POST['alg'].".jar", $output, $return_val);
	 		}
			else if($file_type == 'py'){
				exec("python ".$_POST['alg'].".py", $output, $return_val);
			}
			else if($file_type == 'exe'){
				exec("start ".$_POST['alg'].".exe", $output, $return_val);
			}


			list($msec, $sec) = explode(' ', microtime());
	 		$endtime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	 		$runtime = $endtime-$starttime;

			$config = fopen($_POST['alg'].".txt", "r");
			$line = fgets($config);
			//读取输入参数
			while (strlen($line)>2) {
				list($key,$value) = explode("=", $line);
				$len=strlen($value);
				$value = str_replace(PHP_EOL, '', $value);
				$para[$key]=$value;
				$line = fgets($config);
			}
			//读取输入输出文件
			while(!feof($config)){
				$line = fgets($config);
				list($key,$value) = explode("=", $line);
				$len=strlen($value);
				$value = str_replace(PHP_EOL, '', $value);
				$datafile[$key] = $value;
			}
			fclose($config);

			$test_file = fopen($dataset."/".$datafile['test'], "r");
			$i_num = 0;
			$test = array();
			$participate_num = array();
			while($line = fgetcsv($test_file)){
				$participate_num[$line[1]] = 0;
			}
			if (array_key_exists('user_map', $datafile)) {
				$user_map_file = fopen($datafile['user_map'], "r");
				while($line = fgetcsv($user_map_file)){
					$user_map[$line[1]]=$line[0];
				}
				fclose($user_map_file);

				$event_map_file = fopen($datafile['event_map'], "r");
				while($line = fgetcsv($event_map_file)){
					$event_map[$line[1]]=$line[0];
				}
				fclose($event_map_file);
				$i_num = count($event_map);
				while($line = fgetcsv($test_file)){
					$test[$user_map[$line[0]]][]=$event_map[$line[1]];
					$participate_num[$line[1]]++;
				}
			} else {
				if (strpos($datafile['test'],'csv') !== false) {
					while($line = fgetcsv($test_file)){
						$test[$line[0]][]=$line[1];
						$participate_num[$line[1]]++;
						$i_num = max($i_num, intval($line[1]));
					}
				} else {
					while (!feof($test_file)) {
						$line = fgets($test_file);
						list($uid, $eid) = explode("\t", $line);
						$test[$uid][]=$eid;
						$participate_num[$eid]++;
						$i_num = max($i_num, intval($eid));
					}
				}
			}	
			fclose($test_file);

			$results_file = fopen($datafile['results'], "r");

			$hits = 0;
			$Precision = 0;
			$Recall = 0;
			$nDCG = 0;
			$iDCG = 0;
			$DCG = 0;
			$Novelty = 0;
			$Diversity = 0;
			$recommend_list = array();
			$u_num = 0;
			while($line = fgetcsv($results_file)){
				for ($i=1; $i <=(int)$para['topn'] ; $i++) {	
        			$recommend_list[$u_num][] = $line[$i];
        		}
        		$u_num++;
			}
			// 计算用户间多样性
			for ($i=0; $i < $u_num; $i++) { 
				for ($j=$i+1; $j < $u_num ; $j++) { 
					$common_list = array_intersect($recommend_list[$i], $recommend_list[$j]);
					$Diversity += 1 - count($common_list)/$para['topn'];					
				}
			}
			$Diversity /= ($u_num*($u_num-1))/2;
			for ($i=1; $i <=(int)$para['topn'] ; $i++) { 
				$iDCG += log(2,$i+1);
			}
			$max_k = 0;
			foreach ($participate_num as $key => $value) {
				$max_k = max($max_k, $value);
			}
			while($line = fgetcsv($results_file)){	
				for ($i=1; $i <=(int)$para['topn'] ; $i++) {
					$eid = $line[$i];
					$items[$eid]=1; 
					$Novelty += log(2,(1+$max_k)/(1+$participate_num[$eid]))/log(2,$i+1);
					if(in_array($line[$i], $test[$line[0]])){
						$hits++;
						$DCG += log(2,$i+1);
					}
				}
				$Precision += (int)$para['topn'];
				$Recall += count($test[$line[0]]);
			}
			$Novelty = $Novelty/$u_num/$para['topn'];
			$Precision = $hits/$Precision;
			$Recall = $hits/$Recall;
			$nDCG = $DCG/($iDCG * $u_num);
			$Coverage = count($items)/$i_num;
			
			fclose($results_file);
			$data=['username'=>$_POST['username'],'createdat'=>date("Y-m-d H:i:s",time()),'algorithm'=>$_POST['alg'],'dataset'=>$_POST['dataset'],'precisions'=>$Precision,'recall'=>$Recall,'nDCG'=>$nDCG,'coverage'=>$Coverage,'diversity'=>$Diversity,'novelty'=>$Novelty,'runtime'=>$runtime];
			foreach ($para as $key => $value) {
				$data[$key]=$value;
			}

			Db::table('record')->insert($data);
		}
		else{
			$config = fopen($_POST['alg'].".txt", "w");
			fwrite($config,$_POST['config']);
			fclose($config);

			list($msec, $sec) = explode(' ', microtime());
	 		$starttime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
			if($file_type == 'jar'){
				exec("java -jar ".$_POST['alg'].".jar", $output, $return_val);
	 		}
			else if($file_type == 'py'){
				exec("python ".$_POST['alg'].".py", $output, $return_val);
			}
			else if($file_type == 'exe'){
				exec("start ".$_POST['alg'].".exe", $output, $return_val);
			}
			list($msec, $sec) = explode(' ', microtime());
	 		$endtime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	 		$runtime = $endtime-$starttime;

	 		$data=['username'=>$_POST['username'],'createdat'=>date("Y-m-d H:i:s",time()),'algorithm'=>$_POST['alg'],'dataset'=>$_POST['dataset'],'runtime'=>$runtime];

			$config = fopen($_POST['alg'].".txt", "r");
			$line = fgets($config);
			while (strlen($line)>2) {
				list($key,$value) = explode("=", $line);
				$len=strlen($value);
				if(strrpos($value, "\n")){
					$value = substr($value, 0, $len-2);
				}
				$data[$key]=$value;
				$line = fgets($config);
			}
			while(!feof($config)){
				$line = fgets($config);
				list($key,$value) = explode("=", $line);
				if(strrpos($value, "\n")){
					$value = substr($value, 0, $len-2);
				}
				$datafile[$key] = $value;
			}
			fclose($config);

			$results = fopen($datafile['results'], "r");
			$line = fgets($results);
			while (strlen($line)>2) {
				list($key,$value) = explode("=", $line);
				$len=strlen($value);
				if(strrpos($value, "\n")){
					$value = substr($value, 0, $len-1);
				}
				$data[$key]=$value;
				$line = fgets($results);
			}
			fclose($results);

			Db::table('record')->insert($data);
		}
		$this->success('运行成功，正在返回实验结果','history');
	}

	public function drawchart(){
		$xname = $_POST['xaxis'];
		$yname = $_POST['yaxis'];

		$xdata = Db::query('select '.$xname.' from search order by '.$xname);
		$ydata = Db::query('select '.$yname.' from search order by '.$xname);
		

		foreach ($xdata as $key => $value) {
			$xdata0[]=$value[$xname];
		}
		foreach ($ydata as $key => $value) {
			$ydata0[]=(double)$value[$yname];
		}

		return $this->fetch('',['xdata'=>$xdata0,'ydata'=>$ydata0,'xname'=>$xname,'yname'=>$yname]);
	}

	public function drawchart1(){
		$xname = $_POST['xaxis'];
		$yname = $_POST['yaxis'];

		$xdata = Db::query('select '.$xname.' from record order by '.$xname);
		$ydata = Db::query('select '.$yname.' from record order by '.$xname);
		
		foreach ($xdata as $key => $value) {
			$xdata0[]=(double)$value[$xname];
		}
		foreach ($ydata as $key => $value) {
			$ydata0[]=(double)$value[$yname];
		}

		return $this->fetch('',['xdata'=>$xdata0,'ydata'=>$ydata0,'xname'=>$xname,'yname'=>$yname]);
	}

	

	public function instructions(){
		return $this->fetch();
	}
	// 查看历史记录方法
	public function history(){
		$data = Db::table('record')->paginate(6);

		$this->assign('data',$data);
		return $this->fetch();
	}
	//搜索实验记录
	public function search(){
		$field = $_POST['sel'];
		$content = $_POST['search'];
		$content = '%'.$content.'%';
		$data = Db::table('record')->whereLike($field,$content)->select();
		Db::execute('drop view search');
		Db::execute("create view search as (select * from record where $field LIKE '$content')");
		return $this->fetch('',['data'=>$data]);
	}

}


 ?>