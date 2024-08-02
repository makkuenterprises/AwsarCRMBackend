<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:1', 'max:250'],
            'summary' => ['required', 'string', 'min:1', 'max:500'],
            'description' => ['nullable', 'string', 'min:1', 'max:1000000'],
            'thumbnail' => ['required', 'file', 'mimes:png,jpg,jpeg,webp'],
            'slug' => ['nullable', 'string', 'unique:blogs'],
            'meta_title' => ['nullable', 'string', 'min:5', 'max:250'],
            'meta_description' => ['nullable', 'string', 'min:1', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $blog = new Blog();
            $blog->title = $request->input('title');
            $blog->summary = $request->input('summary');
            $blog->description = $request->input('description');
            
            if ($request->hasFile('thumbnail')) {
                $blog->thumbnail = $request->file('thumbnail')->store('blogs', 'public');
            }
            
            $blog->slug = Str::slug($request->input('slug'));
            $blog->meta_title = $request->input('meta_title');
            $blog->meta_description = $request->input('meta_description');
            
            $blog->save();

            return response()->json([
                'status' => 'success',
                'message' => 'The Community is successfully created.',
                'data' => $blog
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the blog.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
      public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);
            
            // Delete the blog thumbnail if it exists
            if ($blog->thumbnail) {
                Storage::delete($blog->thumbnail);
            }
            
            // Delete the blog record
            $blog->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'The Community is successfully deleted'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Community not found',
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the blog',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        // Find the blog by ID
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Blog not found',
            ], 404);
        }

        // Validate the request data
        $validation = Validator::make($request->all(), [
           'title' => ['required', 'string', 'min:1', 'max:250'],
            'summary' => ['required', 'string', 'min:1', 'max:500'],
            'description' => ['required', 'string', 'min:1', 'max:1000000'],
            'thumbnail' => ['required', 'file', 'mimes:png,jpg,jpeg,webp'],
            'slug' => ['required', 'string', Rule::unique('blogs')->ignore($blog->id, 'id')],
            'meta_title' => ['nullable', 'string', 'min:5', 'max:250'],
            'meta_description' => ['nullable', 'string', 'min:1', 'max:500'],
           
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->errors(),
            ], 422);
        } 

        // Update blog details
        $blog->title = $request->input('title');
        $blog->summary = $request->input('summary');
        $blog->description = $request->input('description');

        // Handle thumbnail update
        if ($request->hasFile('thumbnail')) {
            if ($blog->thumbnail) {
                Storage::delete($blog->thumbnail);
            }
            $blog->thumbnail = $request->file('thumbnail')->store('blogs');
        }

        $blog->slug = $request->input('slug');
        $blog->meta_title = $request->input('meta_title');
        $blog->meta_description = $request->input('meta_description');

        // Save the changes
        $result = $blog->save();

        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'The Community was successfully updated.',
                'data' => $blog,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the blog.',
            ], 500);
        }
    }

      public function list()
    {
        try {
            // Retrieve all blogs
            $blogs = Blog::all();

            // Return a success response with the blog data
            return response()->json([
                'status' => 'success',
                'message' => 'Community list retrieved successfully',
                'data' => $blogs
            ], 200);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the blog list',
                'error' => $e->getMessage()
            ], 500);
        }
    }
        public function show($id)
    {
        try {
            // Retrieve the blog by ID
            $blog = Blog::findOrFail($id);

            // Return a success response with the blog data
            return response()->json([
                'status' => 'success',
                'message' => 'Community retrieved successfully',
                'data' => $blog
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return an error response if the blog is not found
            return response()->json([
                'status' => 'error',
                'message' => 'Community not found',
            ], 404);
        } catch (\Exception $e) {
            // Return an error response for other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the blog',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
