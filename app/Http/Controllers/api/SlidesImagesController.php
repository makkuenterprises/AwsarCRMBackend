<?php
namespace App\Http\Controllers;
use App\Models\SlidesImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlidesImagesController extends Controller
{
    public function storeMultiple(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'images' => 'required|array',
            'images.*.image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*.title' => 'required|string|max:255',
            'images.*.link' => 'nullable|url',
        ]);

        $uploadedImages = [];

        try {
            foreach ($request->images as $imageData) {
                // Store the image
                if (isset($imageData['image'])) {
                    $path = $imageData['image']->store('slider_images', 'public');
                    $title = $imageData['title'];
                    $link = $imageData['link'] ?? null;

                    // Save the data in the SlidesImages table
                    $slideImage = SlidesImages::create([
                        'path' => $path,
                        'title' => $title,
                        'link' => $link,
                    ]);

                    $uploadedImages[] = [
                        'path' => $path,
                        'title' => $title,
                        'link' => $link,
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Images uploaded successfully',
                'data' => $uploadedImages,
            ], 200);
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
            $images = SlidesImage::all();

            // Format the data with URLs to the images
            $formattedImages = $images->map(function($image) {
                return [
                    'id' => $image->id,
                    'path' => Storage::url($image->path),
                    'title' => $image->title,
                    'link' => $image->link,
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
