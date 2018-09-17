<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    public function getCats($type) {
        return $this->select('id', 'name')
            ->where('type', $type)
            ->orderBy('name')->get();
    }
}
