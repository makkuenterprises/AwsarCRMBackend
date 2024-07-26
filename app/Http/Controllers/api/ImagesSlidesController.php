<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Image; 
use App\Models\Teacher;
use App\Rules\DateFormat;
use App\Models\SlidesImages;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;


class ImagesSlidesController extends Controller
{
 public function storeMultiple(Request $request)
{
    try {
        // Validate the incoming request data
        $request->validate([
            'images' => 'required|array',
            'images.*.image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*.title' => 'required|string|max:255',
            'images.*.link' => 'nullable|url',
            'images.*.role' => 'required|string',
        ]);

        $uploadedImages = []; 

        foreach ($request->images as $imageData) {
            if (isset($imageData['image'])) {
                // Process and store the image
                $file = $imageData['image'];
                $filename = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                $path = 'slider_images/' . $filename;

                // Resize and compress the image, convert to WebP
                $img = Image::make($file)
                    ->resize(800, 600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('webp', 75); // Convert to WebP format with 75% quality

                // Store the image using Laravel's Storage facade
                Storage::disk('public')->put($path, $img);

                $title = $imageData['title'];
                $role = $imageData['role'] ?? null;
                $link = $imageData['link'] ?? null;

                // Save the data in the SlidesImages table
                $slideImage = SlidesImages::create([
                    'path' => $path,
                    'title' => $title,
                    'link' => $link,
                    'role' => $role,
                ]);

                $uploadedImages[] = [
                    'path' => Storage::url($path), // Generate URL for the stored file
                    'title' => $title,
                    'link' => $link,
                    'role' => $role,
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages,
        ], 200);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $e->errors(), // Get validation errors
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Image upload failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function showImages()
    {
        try {
            // Fetch all images from the SlidesImages table
            $images = SlidesImages::all();
            dd($images);

            // Format the data with URLs to the images
            $formattedImages = $images->map(function($image) {
                return [
                    'id' => $image->id,
                    'image' => url(Storage::url($image->path)),
                    'title' => $image->title,
                    'link' => $image->link,
                    'role' =>$image->role,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Images retrieved successfully',
                'data' => $formattedImages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve images',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
