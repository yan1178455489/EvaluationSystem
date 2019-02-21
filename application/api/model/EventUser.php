<?php
namespace app\api\model;

use think\Model;

class EventUser extends Model {
    // 设置当前模型对应的完整数据表名称
    protected $table = 'lm_eventuser';

    public function activity() {
        return $this->hasOne('Event', 'id', 'event_id');
    }
}
