<?php
namespace app\api\model;

use think\Model;

class User extends Model {
    use SoftDelete;

    // 设置当前模型对应的完整数据表名称
    protected $table = 'oauth_users';

    protected $pk = 'id';
}
