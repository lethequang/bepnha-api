<?php

namespace App\Http\Controllers\bepnha;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Documents;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Exception;


class DocumentController extends Controller
{
	public static $model;
	public function __construct()
	{
		$this::$model = new Documents();
		Carbon::setLocale('vi');
	}
	private function getQuery($uid) {
		$days = DB::raw("documents.date_created as days");
		$image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as image');
		$liked = DB::raw('notebook_document.document_id IS NOT NULL as liked');
		$query = DB::table('documents')
			->leftJoin('notebook_document', 'documents.id', '=', 'notebook_document.document_id', 'and', 'notebook_document.user_id', '=', $uid)
			->select('documents.id', 'documents.title', $image, 'documents.content', 'documents.chef', 'documents.time_to_done', 'documents.level',
				$days, 'documents.view_count', $liked)
			->where('documents.disable', '=', '0')->orderby('documents.date_created', 'desc')->distinct();
		return $query;
	}
	public function Search(Request $request){
		$key = $request->input('key');
		$uid = $request->input('uid');
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$result = array('status'=>'');
		try{
			if ($key !== null){
				$query = $this->getQuery($uid)
					->where('documents.title','like',"%$key%")
					->orWhere('documents.content','like',"%$key%");
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
				$query = DB::table('documents')->where('title','like',"%$key%")
					->orWhere('content','like',"%$key%")
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
