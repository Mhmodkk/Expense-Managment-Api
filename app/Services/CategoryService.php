<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    public function store(object $request): Category
    {
        $category = Category::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'icon' => $request->icon ?? null,
            'type' => $request->type ?? 'expense',
        ]);

        return $category;
    }

    public function update(object $request, $id): Category
    {
        $category = Category::where('id',$id)->where('user_id',Auth::id())->firstOrFail();

        $category->update([
            'name' => $request->name ?? $category->name,
            'icon' => $request->icon ?? $category->icon,
            'type' => $request->type ?? $category->type,
        ]);

        $category->updated_at = Carbon::now();

        return $category;
    }

    public function delete(int $id): bool
    {
        $category = Category::where('id',$id)->where('user_id',Auth::id())->FirstorFail();

        $category->delete();
        return true;
    }

    public function getall()
    {
        return Category::where('user_id', Auth::id())->latest()->get();
    }
}
