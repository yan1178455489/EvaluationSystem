<?php
namespace app\api\model;

use think\Model;

class Moment extends Model {
    // 设置当前模型对应的完整数据表名称
    protected $table = 'lm_moment';

    protected $pk = 'id';

    protected $autoWriteTimestamp = 'datetime';

    public function comments() {
        return $this->hasMany("Comment", "moment_id", "id");
    }
}
