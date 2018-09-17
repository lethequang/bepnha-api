<?php

namespace App\Http\Controllers\bepnha;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Category;
use DB;

class CategoryController extends Controller
{
    public function getCatType($type) {
        return DB::table('categories')->select('id', 'name')
            ->where('type', $type)
            ->orderBy('name')->get();
    }
}
