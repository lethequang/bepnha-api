<?php

namespace App\Http\Controllers\bepnha;

use App\Http\Controllers\Controller;
use App\Http\Models\Videos;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;
use Carbon\Carbon;

class HomeController extends Controller
{
//    public function index(Request $request) {
//        $result = array('status'=>'');
//        $limit = $request->input('limit', 10);
//        $uid = $request->input('uid');
//        $tags = DB::table('tags')->pluck('title', 'id');
//        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
//        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
//        $days = DB::raw("videos.date_created as days");
//        $liked = DB::raw('notebook.video_id IS NOT NULL as liked');
//        $queries = array();
//        try {
//            if(count($tags) > 0) {
//                foreach($tags as $k=>$t) {
//                    array_push($queries, DB::table('tag_video')
//                        ->leftJoin('videos', 'tag_video.video_id', '=', 'videos.id', 'videos.disable', '=', 0)
//                        ->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
//                        ->select('tag_video.tag_id', 'videos.id', 'videos.name', $image,
//                            $video, 'videos.description', 'videos.chef',
//                            'videos.ingredients', 'videos.ingredients_2','videos.steps', 'videos.duration', 'videos.time_to_done',
//                            'videos.level', 'videos.note', $days, 'videos.video_type_id as kind ', 'videos.view_count', $liked)
//                        ->where('tag_video.tag_id', $k)->where('disable','<>',1)->orderby('videos.date_created', 'desc')->distinct()->take($limit));
//                }
//                for ($i = 1; $i < count($queries); $i++) {
//                    $queries[0]->union($queries[$i]);
//                }
//
//                $taggedVideos = $queries[0]->get()->groupBy(function ($v) use ($tags) {
//                    $tag_id = $v->tag_id;
//                    unset($v->tag_id);
//                    return $tag_id;
//                });
//                $data = array();
//                Carbon::setLocale('vi');
//                foreach ($taggedVideos as $k => $v) {
//                    if(count($v)>0){
//                       foreach ($v as $item){
//                           $item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
//                           $notebook = DB::table('notebook')->where('video_id',$item->id)->get();
//                           if(isset($notebook) && count($notebook)>0){
//                               foreach ($notebook as $value){
//                                   if( $value->user_id == $uid){
//                                       $item->liked = 1;
//                                       break;
//                                   }else{
//                                       $item->liked = 0;
//                                   }
//                               }
//                           }else{
//                               $item->liked = 0;
//                           }
//                       }
//                    }
//                    array_push($data, array('tag_name' => $tags[$k], 'tag_id' => $k, 'data' => $v));
//                }
//                $result['data'] = $data;
//            }
//            else
//                $result['data'] = null;
//            $result['status'] = 200;
//        } catch(QueryException $e) {
//            $result['status'] = $e->getCode();
//            $result['errMsg'] = $e->getMessage();
//        }
//        return $result;
//
//    }

    public function index(Request $request) {

        $result = array('status'=>'');
        $limit = $request->input('limit', 10);
        $uid = $request->input('uid');

        $tags = DB::table('tags')
            ->join('tag_video', 'tag_video.tag_id', '=', 'tags.id')
            ->where('tags.disable', '=', 0)
            ->select('tags.title as tag_name', 'tags.id as tag_id')
            ->orderBy('tags.order_by')
            ->orderBy('tags.date_created')
            ->distinct()->get();

        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
        $days = DB::raw("videos.date_created as days");
        $liked = DB::raw('notebook.video_id IS NOT NULL as liked');

        try {
            if(count($tags) > 0) {
                foreach($tags as $item) {
                    $data_item = DB::table('tag_video')
                        ->leftJoin('videos', 'tag_video.video_id', '=', 'videos.id', 'videos.disable', '=', 0)
                        ->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
                        ->select('tag_video.tag_id', 'videos.id', 'videos.name', $image,
                            $video, 'videos.description', 'videos.chef',
                            'videos.ingredients', 'videos.ingredients_2','videos.steps', 'videos.duration', 'videos.time_to_done',
                            'videos.level', 'videos.note', $days, 'videos.video_type_id as kind ', 'videos.view_count', $liked)
                        ->where('tag_video.tag_id', $item->tag_id)
                        ->where('disable','<>',1)
                        ->orderby('videos.date_created', 'desc')
                        ->distinct()
                        ->take($limit)
                        ->get();
                    $item->data = $data_item;
                }

                $data = array();
                Carbon::setLocale('vi');
                foreach ($tags as $value) {
                    if(count($value->data)>0){
                        foreach ($value->data as $item){
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
                    }
                }
                $result['data'] = $tags;
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

    public function getVideos(Request $request, $tag_id) {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $uid = $request->input('uid');
        $result = array('status'=>'');
        $days = DB::raw("datediff(NOW(), videos.date_created) as days");
        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
        $liked = DB::raw('notebook.video_id IS NOT NULL as liked');
        try {
            $query = DB::table('tag_video')
                ->leftJoin('videos', 'tag_video.video_id', '=', 'videos.id')
                ->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
                ->select('videos.id', 'videos.name', $image,
                    $video, 'videos.description', 'videos.chef',
                    'videos.ingredients', 'videos.ingredients_2','videos.steps', 'videos.duration', 'videos.time_to_done',
                    'videos.level', 'videos.note', $days, 'videos.video_type_id as kind', 'videos.view_count', $liked)
                ->where('videos.disable', 0)
                ->where('tag_video.tag_id', $tag_id)->orderby('videos.date_created', 'desc');
            $query->skip($limit*($page-1))->take($limit);
            $result['data'] = $query->get();
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

    public function getHomeVideos(Request $request){
        $result = array('status'=>'');
        $limit = $request->input('limit', 10);
        $uid = $request->input('uid');
        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",video_location) as video');
        $days = DB::raw("date_created as days");
        try {

               $videos = Videos::where('is_home',true)->select('id', 'name', $image,
                    $video, 'videos.description', 'videos.chef',
                    'ingredients', 'ingredients_2', 'steps', 'duration', 'time_to_done',
                    'level', 'note', $days, 'video_type_id as kind ', 'view_count', 'is_home')
               ->orderby('date_created', 'desc')->take($limit)->get();

                Carbon::setLocale('vi');
                foreach ($videos as $item) {
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
                $result['data'] = $videos;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
	/*private function getQuery($uid) {
		$days_video = DB::raw("videos.date_created as days");
		$image_video = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
		$video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
		$liked_video = DB::raw('notebook.video_id IS NOT NULL as liked');

		$days_document = DB::raw("documents.date_created as days");
		$image_document = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as image');
		$liked_document = DB::raw('notebook_document.document_id IS NOT NULL as liked');

		$document = DB::table('documents')
			->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and', 'notebook_document.user_id', '=', $uid)
			->select('documents.id', 'documents.title', $image_document,
				'documents.content', 'documents.chef', 'documents.time_to_done',
				'documents.level', $days_document, 'documents.view_count',
				$liked_document, null, null, null, null, null, null, null)
			->where('documents.disable', '=', '0')
			->orderby('documents.date_created', 'desc')->distinct();

		$query = DB::table('videos')
			->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
			->select('videos.id', 'videos.name', $image_video,
				$video, 'videos.description', 'videos.chef',
				'videos.ingredients', 'videos.ingredients_2','videos.steps',
				'videos.duration', 'videos.time_to_done', 'videos.level',
				'videos.note', $days_video, 'videos.video_type_id as kind',
				'videos.view_count', $liked_video)
			->where('videos.disable', '=', '0')
			->orderby('videos.date_created', 'desc')->distinct()
			->union($document);
			//->orderby('date_created','desc');
		return $query;
	}*/
    public function Search(Request $request){
		$key = $request->input('key');
		$uid = $request->input('uid');
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$result = array('status'=>'');

		$days_video = DB::raw("videos.date_created as days");
		$image_video = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
		$video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
		$liked_video = DB::raw('notebook.video_id IS NOT NULL as liked');

		$days_document = DB::raw("documents.date_created as days");
		$image_document = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as image');
		$liked_document = DB::raw('notebook_document.document_id IS NOT NULL as liked');

		$document = DB::table('documents')
			->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and', 'notebook_document.user_id', '=', $uid)
			->select('documents.id', 'documents.title', $image_document,
				'documents.content', 'documents.chef', 'documents.time_to_done',
				'documents.level', $days_document, 'documents.view_count',
				$liked_document, DB::raw('NULL'), DB::raw('NULL'), DB::raw('NULL'), DB::raw('NULL'), DB::raw('NULL'), DB::raw('NULL'), DB::raw('NULL'))
			->where('documents.disable', '=', '0')
			->Where('documents.title','like',"%$key%")
			->orWhere('documents.content','like',"%$key%");
			//->orderby('documents.date_created', 'desc')->distinct();

		$query = DB::table('videos')
			->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
			->select('videos.id', 'videos.name', $image_video,
				$video, 'videos.description', 'videos.chef',
				'videos.ingredients', 'videos.ingredients_2','videos.steps',
				'videos.duration', 'videos.time_to_done', 'videos.level',
				'videos.note', $days_video, 'videos.video_type_id as kind',
				'videos.view_count', $liked_video)
			->where('videos.disable', '=', '0')
			->where('videos.name','like',"%$key%")
			->orWhere('videos.description','like',"%$key%")
			->orderby('videos.date_created','desc')->distinct()
			->union($document);

		try{
			if ($key !== null){
				if($page == 1)
					$query->take($limit);
				else
					$query->skip($limit * ($page-1))->take($limit);
				$data = $query->get();
				foreach ($data as $item){
					$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
					$notebook_document = DB::table('notebook_document')->where('document_id',$item->id);
					$notebook = DB::table('notebook')->where('video_id',$item->id)
							->union($notebook_document)
							->get();
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
			else{
				$result['status'] = 404;
			}
		}
		catch (\Exception $e){
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
				$document = DB::table('documents')
					->where('title','like',"%$key%")
					->orWhere('content','like',"%$key%")
					->select('id','title', 'content','time_to_done','pcategory_id','category_id','created_by','modified_by','date_created');
				$query = DB::table('videos')
					->where('name','like',"%$key%")
					->orWhere('description','like',"%$key%")
					->select('id','name as title', 'description','time_to_done','pcategory_id','category_id','created_by','modified_by','date_created')
					->union($document)
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