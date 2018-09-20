<?php
namespace App\Http\Controllers\bepnha;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;
class NotebookDocumentController extends Controller
{
	public function __construct()
	{
		Carbon::setLocale('vi');
	}
	public function addNote($id, $document_id) {
		$result = array('status'=>'');
		try {
			$user = DB::table('login_users')->where('uuid', $id)->take(1)->get();
			if(count($user) == 0) {
				DB::table('login_users')->insertGetId(['uuid'=>$id]);
			}
			$result['data'] = DB::table('notebook_document')->insertGetId(['user_id'=>$id, 'document_id'=>$document_id]);
			$result['status'] = 200;
		} catch(QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}
	public function checkNote($id, $document_id) {
		$result = array('status'=>'');
		try {
			$notes = DB::table('notebook_document')
				->where('user_id', $id)
				->where('document_id', $document_id)
				->get();
			$result['data'] = count($notes) > 0;
			$result['status'] = 200;
		} catch(QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}
	public function rmNote($id, $document_id) {
		$result = array('status'=>'');
		try {
			$is_del = DB::table('notebook_document')
				->where('user_id', $id)
				->where('document_id', $document_id)
				->delete();
			$result['data'] = $is_del > 0;
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


	public function getDocuments(Request $request, $uid) {
		//$kind = $request->input('kind');
		// Recently added
		//if($kind == -2)
			//return $this->getRecentVideos($request, $uid);
		$limit = $request->input('limit', 10);
		$page = $request->input('page', 1);
		$result = array('status'=>'');
		try {
			$query = $this->getQuery($uid);
			if($page == 1)
				$query->take($limit);
			else
				$query->skip($limit*($page-1))->take($limit);
			$data = $query->orderby('documents.date_created','desc')->get();
			foreach ($data as $item){
				$item->days = Carbon::createFromTimeStamp(strtotime($item->days))->diffForHumans();
			}
			$result['data'] = $data;
			$result['status'] = 200;
		} catch(QueryException $e) {
			$result['status'] = $e->getCode();
			$result['errMsg'] = $e->getMessage();
		}
		return $result;
	}

	private function getQuery($user_id) {
		$days = DB::raw("documents.date_created as days");
		$image = DB::raw('concat("'.env('MEDIA_URL_IMAGE').'/",documents.image_location) as image');
		$liked = DB::raw("1 as liked");
		$query = DB::table('notebook_document')
			->leftJoin('documents', 'notebook_document.document_id', '=', 'documents.id')
			->Join('categories','documents.category_id','=','categories.id')
			->select('documents.id', 'documents.title', $image, 'documents.content', 'documents.chef', 'documents.time_to_done', 'documents.level',
				$days, 'documents.view_count', $liked,'categories.name as category','categories.style')
			->where('documents.disable', 0)
			->where('notebook_document.user_id', $user_id);
		return $query;
	}
}