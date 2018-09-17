<?php

namespace App\Http\Controllers\bepnha;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;

class NotebookController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('vi');
    }

    public function addNote($id, $video_id) {
        $result = array('status'=>'');
        try {
            $user = DB::table('login_users')->where('uuid', $id)->take(1)->get();
            if(count($user) == 0) {
                DB::table('login_users')->insertGetId(['uuid'=>$id]);
            }
            $result['data'] = DB::table('notebook')->insertGetId(['user_id'=>$id, 'video_id'=>$video_id]);
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
    public function checkNote($id, $video_id) {
        $result = array('status'=>'');
        try {
            $notes = DB::table('notebook')
                ->where('user_id', $id)
                ->where('video_id', $video_id)
                ->get();
            return count($notes) > 0;
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
    public function rmNote($id, $video_id) {
        $result = array('status'=>'');
        try {
            $is_del = DB::table('notebook')
                ->where('user_id', $id)
                ->where('video_id', $video_id)
                ->delete();
            $result['data'] = $is_del > 0;
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

    // Get kinds (bua sang, bua trua, bua toi), nhung video dang co
    public function getKinds($user_id) {
        $result = array('status'=>'');
        try {
            $user = DB::table('login_users')->where('uuid', $user_id)->take(1)->get();
            if(count($user) > 0) {
                $data = array(['id'=>-2, 'name'=>'Recently added'], ['id'=>-1, 'name'=>'All']);
                $result['data'] = array_merge($data, DB::table('notebook')
                    ->leftJoin('videos', 'notebook.video_id', 'videos.id')
                    ->leftJoin('video_types', 'videos.video_type_id', 'video_types.id')
                    ->select('video_types.id', 'video_types.name')
                    ->where('notebook.user_id', $user_id)
                    ->where('videos.disable', 0)
                    ->distinct()->get()->toArray());
            }
            else
                $result['data'] = null;
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

    // Get recent videos / Paging
    public function getRecentVideos(Request $request, $uid) {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $result = array('status'=>'');
        try {
            $query = $this->getQuery($uid, $request->input('kind'))
                ->orderBy('notebook.date_created', 'desc')->take(10);
            if($page == 1)
                $query->take($limit);
            else
                $query->skip($limit*($page-1))->take($limit);

            $data = $query->orderby('videos.date_created','desc')->get();
            foreach ($data as $item){
                $item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
                $notebook = DB::table('notebook')->where('video_id',$item->id)->get();
                if(isset($notebook) && count($notebook)>0){
                    foreach ($notebook as $value){
                        if( $value->user_id == $uid){
                            $item->liked = 1;
                            break;
                        }else{
                            $item->liked = 0;
                        }
                    }
                }else{
                    $item->liked = 0;
                }
            }
            $result['data'] = $data;
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

    // Get videos by kind(all, sang, trua, chieu) / Paging
    public function getVideos(Request $request, $uid) {
        $kind = $request->input('kind');
        // Recently added
        if($kind == -2)
            return $this->getRecentVideos($request, $uid);
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $result = array('status'=>'');
        try {
            $query = $this->getQuery($uid, $kind);
            if($page == 1)
                $query->take($limit);
            else
                $query->skip($limit*($page-1))->take($limit);

            $data = $query->orderby('videos.date_created','desc')->get();
            foreach ($data as $item){
                $item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
//                $notebook = DB::table('notebook')->where('video_id',$item->id)->get();
//                if(isset($notebook) && count($notebook)>0){
//                    foreach ($notebook as $value){
//                        if( $value->user_id == $uid){
//                            $item->liked = 1;
//                            break;
//                        }else{
//                            $item->liked = 0;
//                        }
//                    }
//                }else{
//                    $item->liked = 0;
//                }
            }
            $result['data'] = $data;
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

    private function getQuery($user_id, $kind) {
        $days = DB::raw("videos.date_created as days");
        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
        $liked = DB::raw("1 as liked");
        $query = DB::table('notebook')
            ->leftJoin('videos', 'notebook.video_id', '=', 'videos.id')
            ->select('videos.id', 'videos.name', $image,
                $video, 'videos.description', 'videos.chef',
                'videos.ingredients', 'videos.ingredients_2','videos.steps', 'videos.duration', 'videos.time_to_done',
                'videos.level', 'videos.note', $days, 'videos.video_type_id as kind', 'videos.view_count', $liked)
            ->where('videos.disable', 0)
            ->where('notebook.user_id', $user_id);
        if($kind >= 0) {
            $query->where('videos.video_type_id', $kind);
        }
        return $query;
    }
}
