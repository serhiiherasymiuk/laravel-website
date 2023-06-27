<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Validator;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

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
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *   security={{ "bearerAuth": {} }},
     *     @OA\Response(response="200", description="List Categories."),
     * @OA\Response(
     *    response=404,
     *    description="Wrong id",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Wrong category id")
     *        )
     *     )
     * )
     */
    public function getById($id) {
        $category = Category::findOrFail($id);
        return response()->json($category, 200,
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
    *                     type="file"
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
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = '150_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $resizedImage = Image::make($image)->resize(150, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode();
            $path = public_path('uploads/' . $filename);
            file_put_contents($path, $resizedImage);
            $input['image'] = $filename;
        }
        $category = Category::create($input);
        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "image", "description"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file"
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
    public function update($id, Request $request)
    {
        $category = Category::findOrFail($id);
        $input = $request->all();
        $message = array(
            'name.unique' => "Name must be unique",
            'name.required'=>"Name is required",
            'image.required'=>"Image is required",
            'description.required'=>"Description is required",
        );

        $validator = Validator::make($input, [
            'name' => 'required|unique:categories,name,' . $category->id,
            'image' => 'required',
            'description' => 'required'
        ], $message);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldImagePath = public_path('uploads/' . $category->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $filename = '150_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $resizedImage = Image::make($image)->resize(150, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode();
            $path = public_path('uploads/' . $filename);
            file_put_contents($path, $resizedImage);
            $input['image'] = $filename;
        }

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
    *         description="Category deleted"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Category not found"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Not authorized"
    *     )
    * )
    */
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        if ($category->image) {
            $oldImagePath = public_path('uploads/' . $category->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $category->delete();
        return response()->json(null, 204);
    }
}
