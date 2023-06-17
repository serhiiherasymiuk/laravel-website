<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
     /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    public function index() {
        $list = Category::all();
        return response()->json($list, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
    * @OA\Post(
    *     tags={"Category"},
    *     path="/api/category",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 required={"name", "image", "description"},
    *                 @OA\Property(
    *                     property="image",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="description",
    *                     type="string"
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(response="200", description="Add Category.")
    * )
    */
    public function store(Request $request) {
        $input = $request->all();
        $message = array(
            'name.unique' => "Name must be unique",
            'name.required'=>"Name is required",
            'image.required'=>"Image is required",
            'description.required'=>"Description is required",
        );
        $validator = Validator::make($input,[
            'name' => 'required|unique:categories',
            'image'=>'required',
            'description'=>'required'
        ], $message);
        if($validator->fails()) {
            return response()->json($validator->errors(), 400,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }
        $category = Category::create($input);
        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
    * @OA\Post(
    *     tags={"Category"},
    *     path="/api/category/edit",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 required={"id", "name", "image", "description"},
    *                 @OA\Property(
    *                     property="id",
    *                     type="number",
    *                     format="int64",
    *                 ),
    *                 @OA\Property(
    *                     property="image",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="description",
    *                     type="string"
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(response="200", description="Update Category.")
    * )
    */
    public function update(Request $request)
    {
        $input = $request->all();
        $message = array(
            'name.unique' => "Name must be unique",
            'name.required' => "Name is required",
            'image.required' => "Image is required",
            'description.required' => "Description is required",
        );
        $validator = Validator::make($input, [
            'id' => 'required|exists:categories',
            'name' => 'required|unique:categories,name,' . $input['id'],
            'image' => 'required',
            'description' => 'required'
        ], $message);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $category = Category::findOrFail($input['id']);
        $category->update($input);

        return response()->json($category, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
    * @OA\Delete(
    *     path="/api/category/{id}",
    *     tags={"Category"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(
    *             type="number",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Успішне видалення категорії"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Категорії не знайдено"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Не авторизований"
    *     )
    * )
    */
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
