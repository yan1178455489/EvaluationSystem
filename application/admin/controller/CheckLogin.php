<?php

namespace app\admin\controller;

use think\Session;

use think\Controller;

use think\Db;

class CheckLogin extends Controller{
    public function _initialize(){
        if (empty(session('user.username'))) {
            $this->error('请登录',url('Index/index'));
        }
    }
}