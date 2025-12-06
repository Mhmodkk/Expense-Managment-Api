<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService) {
        $this->categoryService = $categoryService;
    }


    public function index()
    {
        $categories = $this->categoryService->getall();

        return response()->json([
            'message' => __('app.categories_listed'),
            'category' => CategoryResource::collection($categories->load('user')),
        ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:cate$category,expense',
            'icon' => 'nullable|string|max:255',
        ]);

        $category = $this->categoryService->store($request);

        return response()->json([
            'message'=>__('app.category_created'),
            'Category'=>new CategoryResource($category),

        ],201);
    }


    public function update(Request $request,$id)
    {

        $user_id=Auth::user()->id;
        $category = Category::findOrfail($id);

        if($category->user_id != $user_id)
        return response()->json(['message'=>__('app.invalid_act_update_category')],403);

        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|in:SYP,USD,GBP'
        ]);

        $category = $this->categoryService->update($request,$id);

        return response()->json([
            'message'=>__('app.category_updated'),
            'Category'=>new CategoryResource($category),
        ],200);
    }


    public function destroy($id)
    {
        try {
            $this->categoryService->delete($id);

            return response()->json([
            'message' => __('app.category_deleted')
        ]);
        }   catch (Exception $e) {
            return response()->json([
            'message' => __('app.invalid_act_delete_category'),

            ], 500);
        }
    }
}
