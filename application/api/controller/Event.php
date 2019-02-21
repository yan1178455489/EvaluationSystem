<?php
namespace app\api\controller;

use think\Request;
use think\response\Json;
use app\api\model\Event as EventModel;

class Event {
    public function list(Request $request) {
        $location = $request->get("loc");
        $type = $request->get("type");
        $dayType = $request->get("day_type");
        $start = $request->get("start", 0);
        $count = $request->get("count", 20);

        $resultQuery = Event::where('loc_id', $location);
        $resultTotalCount = $resultQuery->count();
        $results = $resultQuery->limit($start, $count)
            ->select();
        return Json([
            'count' => $count,
            'start' => $start,
            'total' => $resultTotalCount,
            'events' => $results
        ]);
    }
}
