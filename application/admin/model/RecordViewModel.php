<?php 
namespace app\admin\model;
use Think\Model;

/**
* 评价记录视图
*/
class RecordViewModel extends Model
{
	
	public $viewFields = array(
		'Record' => array('username','created_at','algorithm'),
		'User' => array('user_id','username','_on'=>'User.username=Record.username')
	);
}

 ?>