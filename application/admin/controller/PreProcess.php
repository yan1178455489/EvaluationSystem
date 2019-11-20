<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Controller;
use app\admin\model\Dataset;


/**
* 压缩和解压文件
*/
class PreProcess
{
	// 返回数据集页面
	public function pre_process(){
		return $this->fetch();
	}
	//..\..\..\public\meetup_ch
	public function do_process(){
		$event_dict = array();
		$group_dict = array();
		$user_dict = array();
		$locat_dict = array();
		$file = fopen('..\..\..\public\meetup_ch\events.csv', "r") or die("Unable to open file!");
		$wf = fopen('..\..\..\public\meetup_ch\group_event.csv', "w") or die("Unable to open group!");
		$wf0 = fopen('..\..\..\public\meetup_ch\location_event.csv', "w") or die("Unable to open location!");
		$wf1 = fopen('..\..\..\public\meetup_ch\time_event.csv', "w") or die("Unable to open time!");
		$event_count = 0;
		$group_count = 0;
		$locat_count = 0;
		//读取输入文件
		while($line = fgetcsv($file)){
			$eid = $line[0];
			$time = $line[1];
			$lid = $line[2];
			$gid = $line[3];
			$time_array = split(" ", $time);
			$weekday = date("w",strtotime($time_array[0]));
			$hour_array = split(":", $time_array[1]);
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
		$train_file = fopen('..\..\..\public\meetup_ch\trains.csv', "r") or die("Unable to open file!");
		$wf = fopen('..\..\..\public\meetup_ch\train.csv', "w") or die("Unable to open file!");
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

		$test_file = fopen('..\..\..\public\meetup_ch\tests.csv', "r") or die("Unable to open file!");
		$wf = fopen('..\..\..\public\meetup_ch\test.csv', "w") or die("Unable to open file!");
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
$processor = new PreProcess();
$processor->do_process();
?>