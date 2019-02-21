<?php
namespace app\api\model;

use think\Model;

class Comment extends Model {
    // 设置当前模型对应的完整数据表名称
    protected $table = 'lm_comment';

    protected $pk = 'id';

    protected $autoWriteTimestamp = 'datetime';
}
