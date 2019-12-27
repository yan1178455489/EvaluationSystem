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
	
	// 删除
	public function delete(){
		$id=$_POST['ids'];
		if(Db::table('record')->delete($id)){

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


	public function run(){
		$alg = Db::table('algorithm')->where('algname',$_POST['alg'])->find();
		$need_computed = (int)$alg['need_computed'];
		$dataset = $_POST['dataset'];
		$file_type = $alg['filetype'];
		$selects = $_POST['indicator'];

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
		$param = array();
		// 读取输入参数
		while (strlen($line)>2) {
			list($key,$value) = explode("=", $line);
			$key = trim($key);
			$value = trim($value);
			$param[$key]=$value;
			$line = fgets($config);
		}
		// 读取文件对应路径
		$datafile = array();
		while(!feof($config)){
			$line = fgets($config);
			list($key,$value) = explode("=", $line);
			$key = trim($key);
			$value = trim($value);
			$datafile[$key] = $value;
		}
		fclose($config);
		if($need_computed == 1){
			$train_file = fopen($dataset."/".$datafile['train'], "r");
			$test_file = fopen($dataset."/".$datafile['test'], "r");
			$test = array();
			$participate_num = array();
			while($line = fgetcsv($train_file)){
				if (array_key_exists($line[1], $participate_num)) {
					$participate_num[$line[1]]++;
				} else{
					$participate_num[$line[1]] = 1;
				}
			}
			$cand_users = array();
			while($line = fgetcsv($test_file)){
				if (array_key_exists($line[1], $participate_num)) {
					$participate_num[$line[1]]++;
				} else{
					$participate_num[$line[1]] = 1;
				}
				$cand_users[$line[0]] = 1;
				$test[$line[0]][] = $line[1];
			}
			fclose($train_file);
			fclose($test_file);
			// 如果有user_map等映射文件
			if(array_key_exists("user_map",$datafile)&&array_key_exists("event_map",$datafile)){
				$user_map_file = fopen($dataset."/".$datafile['user_map'], "r");
				while($line = fgetcsv($user_map_file)){
					$user_map[$line[0]] = $line[1];
				}
				fclose($user_map_file);
				$event_map_file = fopen($dataset."/".$datafile['event_map'], "r");
				while($line = fgetcsv($event_map_file)){
					$event_map[$line[0]] = $line[1];
				}
				fclose($event_map_file);
			}
			$i_num = count($participate_num);
			$hits = 0;
			$precision = 0;
			$recall = 0;
			$nDCG = 0;
			$iDCG = 0;
			$DCG = 0;
			$novelty = 0;
			$diversity = 0;
			$recommend_list = array();
			$recommend_map = array();
			$topN = (int)$param['topN'];

			$u_num = 0;
			$results_file = fopen($datafile['results'], "r");
			if(array_key_exists("user_map",$datafile)&&array_key_exists("event_map",$datafile)){
				while($line = fgetcsv($results_file)){
					for ($i=1; $i <=$topN ; $i++) {	
						$recommend_list[$u_num][] = $event_map[$line[$i]];
						$recommend_map[$user_map[$line[0]]][] = $event_map[$line[$i]];
					}
					$u_num++;
				}
			} else {
				while($line = fgetcsv($results_file)){
					for ($i=1; $i <=$topN ; $i++) {	
						$recommend_list[$u_num][] = $line[$i];
						$recommend_map[$line[0]][] = $line[$i];
					}
					$u_num++;
				}
			}
			fclose($results_file);
			// 计算用户间多样性
			for ($i=0; $i < $u_num; $i++) { 
				for ($j=$i+1; $j < $u_num ; $j++) { 
					$common_list = array_intersect($recommend_list[$i], $recommend_list[$j]);
					$diversity += 1 - count($common_list)/$topN;					
				}
			}
			$diversity /= ($u_num*($u_num-1))/2;
			for ($i=1; $i <=$topN ; $i++) { 
				$iDCG += log($i+1,2);
			}
			$max_k = 0;
			foreach ($participate_num as $key => $value) {
				$max_k = max($max_k, $value);
			}
			foreach($recommend_map as $uid => $list){	
				if (!array_key_exists($uid, $cand_users)) {
					continue;
				}
				for ($i=0; $i <$topN ; $i++) {
					$eid = $list[$i];
					if (!array_key_exists($eid, $participate_num)) {
						continue;
					}
					$items[$eid] = 1; 
					$novelty += (1+$participate_num[$eid])/(1+$max_k)/log($i+2,2);
					if(in_array($eid, $test[$uid])){
						$hits++;
						$DCG += log($i+1,2);
					}
				}
				$precision += $topN;
				$recall += count($test[$uid]);
			}
			$novelty = $novelty/$u_num/$topN;
			$novelty = 1-$novelty;
			if($recall>0){
				$precision = $hits/$precision;
				$recall = $hits/$recall;
			}
			$nDCG = $DCG/($iDCG * $u_num);
			$coverage = count($items)/$i_num;
			$f1 = 2*$precision*$recall/($precision+$recall);
			$data=['username'=>$_POST['username'],'createdat'=>date("Y-m-d H:i:s",time()),'algorithm'=>$_POST['alg'],'dataset'=>$_POST['dataset'],'runtime'=>$runtime,
			'precisions'=>$precision,'recall'=>$recall,'f1'=>$f1,'nDCG'=>$nDCG,'coverage'=>$coverage,'diversity'=>$diversity,'novelty'=>$novelty,'topN'=>$param['topN']];
			$param_json = json_encode($param);
			$data["param"] = $param_json;
			Db::table('record')->insert($data);
		}
		else{
			$data=['username'=>$_POST['username'],'createdat'=>date("Y-m-d H:i:s",time()),'algorithm'=>$_POST['alg'],
			'dataset'=>$_POST['dataset'],'runtime'=>$runtime,'topN'=>$param['topN']];
			$results = fopen($datafile['results'], "r");
			$line = fgets($results);
			while (strlen($line)>2) {
				list($key,$value) = explode("=", $line);
				$key = trim($key);
				$value = trim($value);
				$data[$key]=$value;
				$line = fgets($results);
			}
			fclose($results);
			$param_json = json_encode($param);
			$data["param"] = $param_json;
			Db::table('record')->insert($data);
		}
		$this->success('运行成功，正在返回实验结果','search');
	}

	public function drawchart(){
		$xname = $_POST['xaxis'];
		$yname = $_POST['yaxis'];
		$rid_array = $_POST['selected_record'];
		$sql_str = join(",",$rid_array);

		$xdata = Db::query('select '.$xname.' from record where id in('.$sql_str.') order by '.$xname);
		$ydata = Db::query('select '.$yname.' from record where id in('.$sql_str.') order by '.$xname);
		

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