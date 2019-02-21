<?php
namespace app\api\model;

use think\Model;

class GroupRecommend extends Model {
    // 设置当前模型对应的完整数据表名称
    protected $table = 'lm_group_recommend';

    public function activity() {
        return $this->hasOne('Event', 'id', 'event_id');
    }
}
