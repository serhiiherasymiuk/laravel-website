<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $list = Category::all();
        return response()->json($list, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:categories|max:255',
            'image' => 'required',
            'description' => 'required',
        ]);

        $category = Category::create($validatedData);

        return response()->json($category, 201);
    }

    public function update(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $validatedData = $request->validate([
            'name' => 'required|unique:categories,name,' . $category->id . '|max:255',
            'image' => 'required',
            'description' => 'required',
        ]);
        $category->update($validatedData);
        return response()->json($category, 200);
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
