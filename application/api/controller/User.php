<?php
namespace app\api\controller;

use think\Request;
use think\response\Json;
use app\api\model\GroupUser;
use app\api\model\EventUser;

class User {
    public function profile(Request $request) {
        $userId = $request->get("user_id");

        $result = User::get($userId);
        return Json($result);
    }

    public function group(Request $request) {
        $userId = $request->get("user_id");

        $results = GroupUser::where("user_id", $userId)
            ->distinct("group_id")
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'groups' => array_map(function ($group) {
                return ['id' => $group['group_id']];
            }, $results)
        ]);
    }

    public function participated(Request $request) {
        $userId = $request->get("user_id");

        $results = EventUser::with("activity")
            ->where("user_id", $userId)
            ->where("user_type", "participants")
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'events' => array_map(function ($result) {
                return $result->activity;
            }, $results)
        ]);
    }

    public function wished(Request $request) {
        $userId = $request->get("user_id");

        $results = EventUser::with("activity")
            ->where("user_id", $userId)
            ->where("user_type", "wishers")
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'events' => array_map(function ($result) {
                return $result->activity;
            }, $results)
        ]);
    }

    public function created(Request $request) {
        $userId = $request->get("user_id");

        $results = EventUser::with("activity")
            ->where("user_id", $userId)
            ->where("user_type", "creator")
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'events' => array_map(function ($result) {
                return $result->activity;
            }, $results)
        ]);
    }
}
