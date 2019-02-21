<?php
namespace app\api\controller;

use think\Request;
use think\response\Json;
use app\api\model\PersonalRecommend;
use app\api\model\GroupRecommend;

class Recommendation {
    public function personal(Request $request) {
        $userId = $request->get('user_id');
        $count = $request->get('count');

        $results = PersonalRecommend::where('user_id', $userId)
            ->limit($count)
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'events' => array_map(function ($recommendation) {
                return ['id' => $recommendation['event_id']];
            }, $results)
        ]);
    }

    public function group(Request $request) {
        $groupId = $request->get('group_id');
        $count = $request->get('count');

        $results = GroupRecommend::where('group_id', $groupId)
            ->limit($count)
            ->select();
        return Json([
            'count' => count($results),
            'start' => 0,
            'total' => count($results),
            'events' => array_map(function ($recommendation) {
                return ['id' => $recommendation['event_id']];
            }, $results)
        ]);
    }
}
