<?php 
// 声明命名空间
namespace app\admin\controller;

// 导入类
use think\Controller;
// 导入Db类
use think\Db;

class Drawchart extends Controller{
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
	
	// public function drawchart1(){
	// 	$xname = $_POST['xaxis'];
	// 	$yname = $_POST['yaxis'];
	
	// 	$xdata = Db::query('select '.$xname.' from record order by '.$xname);
	// 	$ydata = Db::query('select '.$yname.' from record order by '.$xname);
		
	// 	foreach ($xdata as $key => $value) {
	// 		$xdata0[]=(double)$value[$xname];
	// 	}
	// 	foreach ($ydata as $key => $value) {
	// 		$ydata0[]=(double)$value[$yname];
	// 	}
	
	// 	return $this->fetch('',['xdata'=>$xdata0,'ydata'=>$ydata0,'xname'=>$xname,'yname'=>$yname]);
    // }
}