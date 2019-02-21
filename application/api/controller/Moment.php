<?php
namespace app\api\controller;

use think\Request;
use think\response\Json;
use app\api\model\PersonalRecommend;
use app\api\model\Moment as MomentModel;
use app\api\model\MomentVote as MomentVoteModel;
use app\api\model\Comment as CommentModel;

class Moment {
    public function momentlist(Request $request) {
        $userId = $request->get("user_id");
        $location = $request->get("loc_id");
        $start = $request->get("start", 0);
        $count = $request->get("count", 20);

        $resultQuery = MomentModel::where('loc_id', $location);
        $resultTotalCount = $resultQuery->count();
        $results = $resultQuery->limit($start, $count)
            ->select();
        for($i = 0;$i<count($results);++$i){ 
            $isVoted = MomentVoteModel::where('moment_id',$results[$i]['id'])->where('user_id',$userId)->select();
            if ($isVoted == null) {
                $results[$i]['is_voted'] = 0;
            } else{
                $results[$i]['is_voted'] = 1;
            }
        }
        return Json([
            'count' => $count,
            'start' => $start,
            'total' => $resultTotalCount,
            'momentlist' => $results
        ]);
    }

    public function mymomentlist(Request $request) {
        $userId = $request->get("user_id");
        $location = $request->get("loc_id");
        $start = $request->get("start", 0);
        $count = $request->get("count", 20);

        $resultQuery = MomentModel::where('user_id', $userId);
        $resultTotalCount = $resultQuery->count();
        $results = $resultQuery->limit($start, $count)
            ->select();
        return Json([
            'count' => $count,
            'start' => $start,
            'total' => $resultTotalCount,
            'momentlist' => $results
        ]);
    }

    public function commentlist(Request $request) {
        $momentId = $request->get("moment_id");

        $resultQuery = CommentModel::where('moment_id', $momentId);
        $resultTotalCount = $resultQuery->count();
        $results = $resultQuery->select();
        return Json([
            'total' => $resultTotalCount,
            'commentlist' => $results
        ]);
    }

    public function create(Request $request) {
        if (!$request->isPost()) {
            throw new HttpException(405, "Method Not Allowed", null, ["Allow" => "POST"]);
        }

        $userId = $request->post("user_id");
        $location = $request->post("loc_id");
        $content = $request->post("content");

        MomentModel::create([
 
             'user_id' => $userId,
 
             'loc_id' => $location,

             'content' => $content,
 
        ]);
        return Json([
            'status' => 200,
            'message' => "发布成功"
        ]);
    }

    public function vote_up(Request $request) {
        if (!$request->isPost()) {
            throw new HttpException(405, "Method Not Allowed", null, ["Allow" => "POST"]);
        }

        $userId = $request->post("user_id");
        $momentId = $request->post("moment_id");

        MomentVoteModel::create([
 
             'user_id' => $userId,
 
             'moment_id' => $momentId,
 
        ]);
        return Json([
            'status' => 200,
            'message' => "点赞成功"
        ]);
    }

    public function cancel_vote(Request $request) {
        if (!$request->isPost()) {
            throw new HttpException(405, "Method Not Allowed", null, ["Allow" => "POST"]);
        }

        $userId = $request->post("user_id");
        $momentId = $request->post("moment_id");

        MomentVoteModel::destroy(function($query) use ($userId,$momentId) {
            $map['user_id'] = $userId;
            $map['moment_id'] = $momentId;
            $query->where($map);
        });
        return Json([
            'status' => 200,
            'message' => "取消点赞成功"
        ]);
    }

    public function post_comment(Request $request) {
        if (!$request->isPost()) {
            throw new HttpException(405, "Method Not Allowed", null, ["Allow" => "POST"]);
        }

        $userId = $request->post("user_id");
        $momentId = $request->post("moment_id");
        $commentContent = $request->post("content");
        
        CommentModel::create([
 
             'user_id' => $userId,
 
             'moment_id' => $momentId,

             'content' => $commentContent,
 
        ]);
        return Json([
            'status' => 200,
            'message' => "评论成功"
        ]);
    }
}
