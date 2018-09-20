<?php

namespace App\Http\Controllers\bepnha;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use DB;

class BachKhoaController extends Controller
{
    public function __construct(){
        Carbon::setLocale('vi');
    }

    // Lấy danh sách main cat (ẩm thực thế giới, ẩm thực việt nam)
    public function maincats() {
        $result = array('status'=>'');
        try {
            $result['data'] = DB::table('categories')
                ->select('id', 'name')
                ->where('disable', 0)
                ->where('type', 1)->orderby('date_created','desc')->get();
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
    // Lấy những sub_cat thuộc về main_cat của videos hiện có
    public function listcats($main_cat) {
        $result = array('status'=>'');
        $icon = DB::raw('CONCAT("'.env('MEDIA_URL_IMAGE').'/", icon_location) as icon');
        try {
            $result['data'] = DB::table('videos')
                ->where('videos.disable', 0)
                ->leftJoin('categories', 'categories.id', '=', 'videos.category_id', 'and', 'categories.disable', '=', 0)
                ->select('categories.id', 'categories.name', $icon)
                ->where('pcategory_id', $main_cat)
                ->distinct()->orderby('videos.date_created','desc')->get();
            $result['status'] = 200;
        } catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }

	// Lấy những sub_cat thuộc về main_cat của documents hiện có
	public function listcatsdocuments($main_cat) {
		$result = array('status'=>'');
		$icon = DB::raw('CONCAT("'.env('MEDIA_URL_IMAGE').'/", icon_location) as icon');
		try {
			$result['data'] = DB::table('documents')
				->where('documents.disable', 0)
				->leftJoin('categories', 'categories.id', '=', 'documents.category_id', 'and', 'categories.disable', '=', 0)
				->select('categories.id', 'categories.name', $icon)
				->where('pcategory_id', $main_cat)
				->distinct()->orderby('documents.date_created','desc')->get();
			$result['status'] = 200;
		} catch(QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}

    // Lấy videos kết hợp theo main_cat và sub_cat
    public function getVideos(Request $request, $main, $subcat) {
        // load 10 cái đầu tiên, sau đó load more
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $uid = $request->input('uid');
        $result = array('status'=>'');
        try {
            $query = $this->getQuery($uid)
                ->where('pcategory_id', $main)
                ->where('category_id', $subcat);
            if($page == 1)
                $query->take($limit);
            else
                $query->skip($limit * ($page-1))->take($limit);

            $data = $query->orderby('days','desc')->get();
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

	// Lấy documents kết hợp theo main_cat và sub_cat
	public function getDocuments(Request $request, $main, $subcat) {
		// load 10 cái đầu tiên, sau đó load more
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$uid = $request->input('uid');
		$result = array('status'=>'');
		try {
			$query = $this->getQueryDocuments($uid)
				->where('pcategory_id', $main)
				->where('category_id', $subcat);
			if($page == 1)
				$query->take($limit);
			else
				$query->skip($limit * ($page-1))->take($limit);
			$data = $query->orderby('days','desc')->get();
			foreach ($data as $item){
				$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
				$notebook = DB::table('notebook_document')->where('document_id',$item->id)->get();
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


	// Tìm kiếm video theo tên, bữa(bữa sáng, bữa trưa, bữa tối), lấy 10 kết quả đầu
    public function search(Request $request, $main, $subcat) {
        $key = $request->input('key');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $uid = $request->input('uid');
        $result = array('status'=>'');


        if($key !== null) {
            $query = $this->getQuery($uid)
                ->where('pcategory_id', $main)
                    ->where('category_id', $subcat)
                    ->where('name','like', '%'.$key.'%');
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
        return $result;
    }

	public function searchDocuments(Request $request, $main, $subcat) {
		$key = $request->input('key');
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$uid = $request->input('uid');
		$result = array('status'=>'');


		if($key !== null) {
			$query = $this->getQueryDocument($uid)
				->where('pcategory_id', $main)
				->where('category_id', $subcat)
				->where('title','like', '%'.$key.'%')
			->toSql();
			dd($query);
			if($page == 1)
				$query->take($limit);
			else
				$query->skip($limit * ($page-1))->take($limit);
			$data = $query->get();
			foreach ($data as $item){
				$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
				$notebook = DB::table('notebook_document')->where('document_id',$item->id)->get();
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
		return $result;
	}

    private function getQuery($uid) {
        $days = DB::raw("videos.date_created as days");
        $image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",videos.image_location) as image');
        $video = DB::raw('concat("'.env('MEDIA_URL_VIDEO').'/",videos.video_location) as video');
        $liked = DB::raw('notebook.video_id IS NOT NULL as liked');
        $query = DB::table('videos')
            ->select('videos.id', 'videos.name', $image,
                $video, 'videos.description', 'videos.chef',
                'videos.ingredients', 'videos.ingredients_2','videos.steps', 'videos.duration', 'videos.time_to_done',
                'videos.level', 'videos.note', $days, 'videos.video_type_id as kind', 'videos.view_count', $liked)
            ->where('videos.disable', 0)
            ->leftJoin('notebook', 'videos.id', '=', 'notebook.video_id', 'and', 'notebook.user_id', '=', $uid)->distinct();
        return $query;
    }
	private function getQueryDocument($uid) {
		$days = DB::raw("documents.date_created as days");
		$image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as image');
		$liked = DB::raw('notebook_document.document_id IS NOT NULL as liked');
		$query = DB::table('documents')
			->Join('categories','documents.category_id','=','categories.id')
			->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and', 'notebook_document.user_id', '=', $uid)
			->select('documents.id', 'documents.title', $image, 'documents.content', 'documents.chef', 'documents.time_to_done', 'documents.level',
				$days, 'documents.view_count', $liked,'categories.name as category','categories.style')
			->where('documents.disable', '=', '0')->orderby('documents.date_created', 'desc')->distinct();

		return $query;
	}
}