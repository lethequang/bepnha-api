<?php

namespace App\Http\Controllers\bepnha;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use DB;

class UserController extends Controller
{
    // Truyền vào user id, nếu user đã có trong db thì trả kết quả "user exists"
    // chưa có thì insert vào trả về kết quả "create success"
    public function index(Request $request) {
        $uid = $request->input('uid');
        $result = array('status'=>'');
        try {
            $user = DB::table('login_users')->where('id', $uid)->take(1)->get();
            if(count($user) > 0) {
                $result['user_status'] = 'user exists';
                $result['data'] = $user;
            }
            else {
                $id = DB::table('login_users')->insertGetId(['id'=>$uid]);
                $result['user_status'] = 'create success';
                $result['data'] = $id;
            }
            $result['status'] = 200;
        }
        catch(QueryException $e) {
            $result['status'] = $e->getCode();
            $result['errMsg'] = $e->getMessage();
        }
        return $result;
    }
}
