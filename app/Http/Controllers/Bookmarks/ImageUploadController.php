<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        $image = $request->file('image');
        
        // Get image dimensions using native PHP
        $imageInfo = getimagesize($image->getRealPath());
        
        if ($imageInfo === false) {
            return response()->json([
                'error' => 'Invalid image file'
            ], 422);
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Minimum dimensions: 1920x1080
        if ($width < 1920 || $height < 1080) {
            return response()->json([
                'error' => 'Image must be at least 1920x1080 pixels. Current size: ' . $width . 'x' . $height
            ], 422);
        }
        
        // Generate unique filename
        $userId = Auth::id();
        $filename = 'backgrounds/' . $userId . '/' . uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Store the image
        $path = Storage::disk('public')->putFileAs(
            dirname($filename),
            $image,
            basename($filename)
        );
        
        // Return the public URL
        $url = Storage::disk('public')->url($path);
        
        return response()->json([
            'url' => $url,
            'path' => $path,
            'dimensions' => [
                'width' => $width,
                'height' => $height,
            ]
        ]);
    }
    
    public function delete(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);
        
        // Ensure the path belongs to the current user
        $userId = Auth::id();
        if (!str_contains($validated['path'], 'backgrounds/' . $userId . '/')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        Storage::disk('public')->delete($validated['path']);
        
        return response()->json(['success' => true]);
    }
}
