<?php

namespace App\Http\Controllers\bepnha;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Videos;
use Illuminate\Database\QueryException;
use Exception;
use DB;

class VideoController extends Controller
{
    public static $model;
    public function __construct()
    {
        $this::$model = new Videos();
        Carbon::setLocale('vi');
    }
    private function getQuery($uid) {
        $days = DB::raw("videos.date_created as days");
        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
        $liked = DB::raw('notebook.video_id IS NOT NULL as liked');
        $query = DB::table('videos')
            ->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
            ->select('videos.id', 'videos.name', $image, $video,
                'videos.description', 'videos.chef', 'videos.ingredients', 'videos.ingredients_2','videos.steps',
                'videos.duration', 'videos.time_to_done', 'videos.level', 'videos.note',
                $days, 'videos.video_type_id as kind', 'videos.view_count', $liked)
            ->where('videos.disable', '=', '0')->orderby('videos.date_created', 'desc')->distinct();
        return $query;
    }
    public function getVideo(Request $request, $id) {
        $user_id = $request->input('uid');
        $result = array();
        try {
            $result['data'] = $this->getQuery($user_id)->find($id);
            $result['status'] = 200;
        }
        catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
    public function getVideos(Request $request) {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $uid = $request->input('uid');
        $result = array();
        try {
            $query = $this->getQuery($uid);
            $query->skip($limit * ($page-1))->take($limit);
            if($page == 1)
                $query->take($limit);
            else
                $query->skip($limit * ($page-1))->take($limit);

            $data = $query->get();
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
        } catch(Exception $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
    public function incViewCount($id) {
        $result = array();
        try {
            $this::$model->where('id', $id)->increment('view_count');
            $result['status'] = 200;
        }
        catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }


    public function Search(Request $request) {
        $key = $request->input('key');
        $uid = $request->input('uid');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $result = array('status'=>'');
        try {
            if($key !== null) {
                $query = $this->getQuery($uid)
                    ->where('videos.name','like', "%$key%");
                if($page == 1)
                    $query->take($limit);
                else
                    $query->skip($limit * ($page-1))->take($limit);
                $data = $query->get();
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
            }
            else {
                $result['status'] = 404;
            }
        } catch (QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
	/*public function Search2(Request $request){
		$key = $request->input('key');
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$result = array('status'=>'');
		try{
			if ($key !== null){
				$query = DB::table('videos')->where('name','like',"%$key%")
					->orderby('date_created','desc');
				if($page == 1)
					$query->take($limit);
				else
					$query->skip($limit * ($page-1))->take($limit);
				$data = $query->get();
				$result['data'] = $data;
				$result['status'] = 200;
			}
			else{
				$result['status'] = 404;
			}
		}
		catch (\Exception $e){
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}*/
}