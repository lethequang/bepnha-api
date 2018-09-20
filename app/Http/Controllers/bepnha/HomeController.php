<?php

namespace App\Http\Controllers\bepnha;

use App\Http\Controllers\Controller;
use App\Http\Models\Videos;
use App\Http\Models\Documents;
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
			->join('tag_document', 'tag_document.tag_id', '=', 'tags.id')
			->where('tags.disable', '=', 0)
			->select('tags.title as tag_name', 'tags.id as tag_id')
			->orderBy('tags.order_by')
			->orderBy('tags.date_created')
			->distinct()->get();
		$image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
		$imageDocument = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as imageDoc');
		$video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
		$days = DB::raw("videos.date_created as days");
		$dayDocument = DB::raw("documents.date_created as dayDoc");
		$liked = DB::raw('notebook.video_id IS NOT NULL as liked');
		$likedDocument = DB::raw('notebook_document.document_id IS NOT NULL as likedDoc');
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
					$item->video = $data_item;
				}
				foreach($tags as $item) {
					$data_item = DB::table('tag_document')
						->leftJoin('documents', 'tag_document.document_id', '=', 'documents.id', 'documents.disable', '=', 0)
						->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and', 'notebook_document.user_id', '=', $uid)
						->select('tag_document.tag_id', 'documents.id', 'documents.title', $imageDocument,
							'documents.chef',
							'documents.time_to_done',
							'documents.level',  $dayDocument,  'documents.view_count', $likedDocument)
						->where('tag_document.tag_id', $item->tag_id)
						->where('disable','<>',1)
						->orderby('documents.date_created', 'desc')
						->distinct()
						->take($limit)
						->get();
					$item->document = $data_item;
				}
				$data = array();
				Carbon::setLocale('vi');
				foreach ($tags as $value) {
					if(count($value->video)>0){
						foreach ($value->video as $item){
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
				foreach ($tags as $value) {
					if(count($value->document)>0){
						foreach ($value->document as $item){
							$item->dayDoc = Carbon::createFromTimeStamp(strtotime($item->dayDoc))->diffForHumans();
							$notebook = DB::table('notebook_document')->where('document_id',$item->id)->get();
							if(isset($notebook) && count($notebook)>0){
								foreach ($notebook as $value){
									if( $value->user_id == $uid){
										$item->likedDoc = 1;
										break;
									}else{
										$item->likedDoc = 0;
									}
								}
							}else{
								$item->likedDoc = 0;
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


	public function getVideos(Request $request, $tag_id)
	{
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$uid = $request->input('uid');
		$result = ['status' => ''];
		$days = DB::raw("datediff(NOW(), videos.date_created) as days");
		$image = DB::raw('concat("' . env('MEDIA_URL_IMAGE') . '/",videos.image_location) as image');
		$video = DB::raw('concat("' . env('MEDIA_URL_VIDEO') . '/",videos.video_location) as video');
		$liked = DB::raw('notebook.video_id IS NOT NULL as liked');
		try {
			$query = DB::table('tag_video')
				->leftJoin('videos', 'tag_video.video_id', '=', 'videos.id')
				->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
				->select('videos.id', 'videos.name', $image,
					$video, 'videos.description', 'videos.chef',
					'videos.ingredients', 'videos.ingredients_2', 'videos.steps', 'videos.duration',
					'videos.time_to_done',
					'videos.level', 'videos.note', $days, 'videos.video_type_id as kind', 'videos.view_count', $liked)
				->where('videos.disable', 0)
				->where('tag_video.tag_id', $tag_id)->orderby('videos.date_created', 'desc');
			$query->skip($limit * ($page - 1))->take($limit);
			$result['data'] = $query->get();
			$result['status'] = 200;
		} catch (QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}


	public function getHomeVideos(Request $request)
	{
		$result = ['status' => ''];
		$limit = $request->input('limit', 10);
		$uid = $request->input('uid');
		$image = DB::raw('concat("' . env('MEDIA_URL_IMAGE') . '/",image_location) as image');
		$video = DB::raw('concat("' . env('MEDIA_URL_VIDEO') . '/",video_location) as video');
		$days = DB::raw("videos.date_created as days");
		$tags = DB::raw('group_concat(tags.title) as tags');
		try {
			$videos = Videos::where('is_home', TRUE)
				->leftJoin('tag_video','videos.id','=','tag_video.video_id')
				->join('tags','tag_video.tag_id','=','tags.id')
				->select('videos.id', 'name', $image, $video, 'videos.description', 'videos.chef',
				'ingredients', 'ingredients_2', 'steps', 'duration', 'time_to_done', 'level',
					'note', $days, 'video_type_id as kind ', 'view_count', 'is_home',$tags)
				->groupby('videos.id')
				->orderby('videos.date_created', 'desc')->take($limit)->get();

			Carbon::setLocale('vi');
			foreach ($videos as $item) {
				$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
				$notebook = DB::table('notebook')->where('video_id', $item->id)->get();
				if (isset($notebook) && count($notebook) > 0) {
					foreach ($notebook as $value) {
						if ($value->user_id == $uid) {
							$item->liked = 1;
							break;
						} else {
							$item->liked = 0;
						}
					}
				} else {
					$item->liked = 0;
				}
			}
			$result['data'] = $videos;
		} catch (QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}


	public function getHomeDocuments(Request $request)
	{
		$result = ['status' => ''];
		$limit = $request->input('limit', 10);
		$uid = $request->input('uid');
		$image = DB::raw('concat("' . env('MEDIA_URL_IMAGE') . '/",image_location) as image');
		$days = DB::raw("date_created as days");
		try {

			$documents = Documents::where('is_home',TRUE)->select('documents.id', 'title', $image, 'documents.content',
				'documents.chef','time_to_done','level', $days, 'view_count', 'is_home')
				->orderby('date_created', 'desc')->take($limit)->get();

			Carbon::setLocale('vi');
			foreach ($documents as $item) {
				$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
				$notebook = DB::table('notebook_document')->where('document_id', $item->id)->get();
				if (isset($notebook) && count($notebook) > 0) {
					foreach ($notebook as $value) {
						if ($value->user_id == $uid) {
							$item->liked = 1;
							break;
						} else {
							$item->liked = 0;
						}
					}
				} else {
					$item->liked = 0;
				}
			}
			$result['data'] = $documents;
		} catch (QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}

	public function Search(Request $request)
	{
		$key = $request->input('key');
		$uid = $request->input('uid');
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$result = ['status' => ''];

		$days_video = DB::raw("videos.date_created as days");
		$image_video = DB::raw('concat("' . env('MEDIA_URL_IMAGE') . '/",videos.image_location) as image');
		$video = DB::raw('concat("' . env('MEDIA_URL_VIDEO') . '/",videos.video_location) as video');
		$liked_video = DB::raw('notebook.video_id IS NOT NULL as liked');

		$days_document = DB::raw("documents.date_created as days");
		$image_document = DB::raw('concat("' . env('MEDIA_URL_IMAGE') . '/",documents.image_location) as image');
		$liked_document = DB::raw('notebook_document.document_id IS NOT NULL as liked');

		$document = DB::table('documents')
			->Join('categories', 'documents.category_id', '=', 'categories.id')
			->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and',
				'notebook_document.user_id', '=', $uid)
			->select('documents.id', 'documents.title', $image_document,
				DB::raw('NULL'), 'documents.content', 'documents.chef',
				DB::raw('NULL'),DB::raw('NULL'),DB::raw('NULL'),
				DB::raw('NULL'), 'documents.time_to_done','documents.level',
				DB::raw('NULL'), $days_document, DB::raw('NULL'),
				'documents.view_count', $liked_document, 'categories.name as category', 'categories.style as style',
				'documents.date_created as date_created')
			->where('documents.disable', '=', '0')
			->Where('documents.title', 'like', "%$key%")
			->orWhere('documents.content', 'like', "%$key%")->distinct();

		$query = DB::table('videos')
			->Join('categories', 'videos.category_id', '=', 'categories.id')
			->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)
			->select('videos.id', 'videos.name', $image_video,
				$video, 'videos.description', 'videos.chef',
				'videos.ingredients', 'videos.ingredients_2', 'videos.steps',
				'videos.duration', 'videos.time_to_done', 'videos.level',
				'videos.note', $days_video, 'videos.video_type_id as kind',
				'videos.view_count', $liked_video, 'categories.name as category', 'categories.style as style',
				'videos.date_created as date_created')
			->where('videos.disable', '=', '0')
			->where('videos.name', 'like', "%$key%")
			->orWhere('videos.description', 'like', "%$key%")->distinct()
			->union($document);
		$querySql = $query->toSql();
		$query = DB::table(DB::raw("( $querySql ) as aaaa order by style, date_created desc"))->mergeBindings($query);
		//dd($query->toSql());
		try {
			if ($key !== NULL) {
				if ($page == 1) {
					$query->take($limit);
				} else {
					$query->skip($limit * ($page - 1))->take($limit);
				}
				$data = $query->get();
				foreach ($data as $item) {
					$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
					$notebook_document = DB::table('notebook_document')->where('document_id', $item->id);
					$notebook = DB::table('notebook')->where('video_id', $item->id)
						->union($notebook_document)
						->get();
					if (isset($notebook) && count($notebook) > 0) {
						foreach ($notebook as $value) {
							if ($value->user_id == $uid) {
								$item->liked = 1;
								break;
							} else {
								$item->liked = 0;
							}
						}
					} else {
						$item->liked = 0;
					}
				}
				$result['data'] = $data;
				$result['status'] = 200;
			} else {
				$result['status'] = 404;
			}
		} catch (\Exception $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}
}