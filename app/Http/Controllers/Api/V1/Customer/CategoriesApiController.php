<?php

namespace App\Http\Controllers\api\v1\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;

class CategoriesApiController extends Controller
{
    public function index()
    {
        $categories = Category::select('id', 'code', 'name')->get();

        return response()->json([
            'success' => 'success login',
            'data' => $categories
        ]);
    }
}
