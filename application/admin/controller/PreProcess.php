<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Controller;
use think\Db;
use think\Paginator;

/**
* 压缩和解压文件
*/
class Preprocess extends Controller{
	// 返回选择处理方式页面
	public function index(){
		return $this->fetch();
	}
	// 返回id重新编号处理页面
	public function renumber(){
		$datasets = Db::table('dataset')->select();
		$files = Db::table('dataset')->where("id",$datasets[0]['id'])->column('name');

		return $this->fetch("",['datasets'=>$datasets,'list1'=>$files]);
	}
	// 返回数据集对应文件
	public function get_files(){
		$id = $_GET['id'];
		$files = Db::table('dataset')->where('id',$id)->column('files');
		$list = explode(',',$files[0]);
		return $list;
	}
	// 返回id重新编号页面
	public function process_renumber(){
		$dataset = $_POST['datasets'];
		if ($dataset=="meetup_ch") {
			$this->process_meetup();	
		}
		$this->success('处理成功', 'Index/homepage');
	}
	// 返回生成群组页面
	public function gen_group(){
		$datasets = Db::table('dataset')->select();
		$files = Db::table('dataset')->where("id",$datasets[0]['id'])->column('name');

		return $this->fetch("",['datasets'=>$datasets,'list1'=>$files]);
	}
	// 深度优先遍历活动参与者hash表
	public function dfs($event_participant, $estart, $ustart, $group_size, $groups, $group){
		if(count($group)==$group_size){
			$gr = $group;
			array_push($groups, $gr);
			return;
		}
		if(!array_key_exists($estart, $event_participant)
		||$ustart>=count($event_participant[$estart])){
			return;
		}
		array_push($group, $event_participant[$estart][$ustart]);
		$this->dfs($event_participant, $estart, $ustart+1, $group_size, $groups, $group);
		array_pop($group);
	}
	// 处理生成群组逻辑
	public function process_gen_group(){
		$id = $_POST['datasets'];
		$group_strategy = $_POST['group_strategy'];
		$group_size = (int)$_POST['group_size'];
		$result_name = $_POST['result_name'];

		$dataset = Db::table('dataset')->where("id", $id)->column('name');
		$dataset_name = $dataset[0];
		$test_file = fopen($dataset_name.'\test.csv', "r") or die("Unable to open file!");
		// 构建活动参与者hash表
		$event_participant = array();
		$groups = array();
		$group = array();
		$min = 0;
		while($line=fgetcsv($test_file)){
			$uid = $line[0];
			$eid = $line[1];
			// $min = min($min, (int)$eid);
			$event_participant[$eid][] = $uid;
		}
		if($group_strategy=='random'){
			foreach($event_participant as $event=>$participants){
				$this->dfs($event_participant, $event, 0, $group_size, $groups, $group);
			}
		} elseif($group_strategy=='relation'){

		}
		$groups_file = fopen($dataset_name.'\\'.$result_name, 'w') or die("Unable to write group file");
		foreach($groups as $key0=>$group){
			$group_str = join(",", $group);
			fwrite($groups_file, $groups_str."\r\n");
		}
		fclose($groups_file);
		// $this->success('生成群组成功', 'Index/homepage');
	}
	// 处理id重新编号逻辑
	public function process_meetup(){
		$event_dict = array();
		$group_dict = array();
		$user_dict = array();
		$locat_dict = array();
		$file = fopen('meetup_ch\events.csv', "r") or die("Unable to open file!");
		$wf = fopen('meetup_ch\group_event.csv', "w") or die("Unable to open group!");
		$wf0 = fopen('meetup_ch\location_event.csv', "w") or die("Unable to open location!");
		$wf1 = fopen('meetup_ch\time_event.csv', "w") or die("Unable to open time!");
		$event_count = 0;
		$group_count = 0;
		$locat_count = 0;
		$line = fgetcsv($file);
		//读取输入文件
		while($line = fgetcsv($file)){
			$eid = $line[0];
			$time = $line[1];
			$lid = $line[2];
			$gid = $line[3];
			$time_array = explode(" ", $time);
			$weekday = date("w",strtotime($time_array[0]));
			$hour_array = explode(":", $time_array[1]);
			$real_time = intval($hour_array[0]);
			if ($real_time >= 0 && $real_time <= 5) {
				$real_time = 1;
			} elseif ($real_time >= 6 && $real_time <= 11) {
				$real_time = 2;
			} elseif ($real_time >= 12 && $real_time <= 17) {
				$real_time = 3;
			} else {
				$real_time = 4;
			}
			$wp = 4*$weekday+$real_time;
			if (!array_key_exists($gid, $group_dict)) {
				$group_dict[$gid] = strval($group_count);
				$group_count++;
			}
			if (!array_key_exists($eid, $event_dict)) {
				$event_dict[$eid] = strval($event_count);
				$event_count++;
			}
			if (!array_key_exists($lid, $locat_dict)) {
				$locat_dict[$lid] = strval($locat_count);
				$locat_count++;
			}
			fwrite($wf, $group_dict[$gid].",".$event_dict[$eid]."\r\n");
			fwrite($wf0, $locat_dict[$lid].",".$event_dict[$eid]."\r\n");
			fwrite($wf1, strval($wp).",".$event_dict[$eid]."\r\n");
			if ($event_count>5000) {
				break;
			}
		}
		fclose($file);
		fclose($wf);
		fclose($wf0);
		fclose($wf1);

		$user_count = 0;
		$train_file = fopen('meetup_ch\trains.csv', "r") or die("Unable to open file!");
		$wf = fopen('meetup_ch\train.csv', "w") or die("Unable to open file!");
		while ($line=fgetcsv($train_file)) {
			$uid = $line[0];
			$eid = $line[1];
			if (!array_key_exists($eid, $event_dict)) {
				continue;
			}
			if (!array_key_exists($uid, $user_dict)) {
				$user_dict[$uid] = strval($user_count);
				$user_count++;
			}
			fwrite($wf, $user_dict[$uid].','.$event_dict[$eid].",1\n");
		}
		fclose($train_file);
		fclose($wf);

		$test_file = fopen('meetup_ch\tests.csv', "r") or die("Unable to open file!");
		$wf = fopen('meetup_ch\test.csv', "w") or die("Unable to open file!");
		while ($line=fgetcsv($test_file)) {
			$uid = $line[0];
			$eid = $line[1];
			if (!array_key_exists($eid, $event_dict)||!array_key_exists($uid, $user_dict)) {
				continue;
			}
			fwrite($wf, $user_dict[$uid].','.$event_dict[$eid]."\n");
		}
		fclose($test_file);
		fclose($wf);

	}
}
?>