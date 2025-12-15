<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    /**
     * Handle async file upload
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Validate the incoming file
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400', // 100MB max
            'file_type' => 'sometimes|string',
            'directory_path' => 'sometimes|string',
            'relative_path' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $fileType = $request->input('file_type', 'misc');
            $directoryPath = $request->input('directory_path', '');
            $relativePath = $request->input('relative_path', '');
            
            // Get predetermined storage paths based on file type
            $basePath = $this->getStoragePath($fileType);
            
            // Append the original directory structure if provided
            if (!empty($directoryPath)) {
                // Sanitize directory path to prevent directory traversal attacks
                $sanitizedPath = $this->sanitizePath($directoryPath);
                $storagePath = $basePath . '/' . $sanitizedPath;
            } else {
                $storagePath = $basePath;
            }
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $uniqueFilename = $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Store the file in the predetermined location with directory structure preserved
            $path = $file->storeAs($storagePath, $uniqueFilename, 'public');
            
            // Get file info
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            // Optional: Save file metadata to database
            // $fileRecord = File::create([
            //     'original_name' => $originalName,
            //     'stored_name' => $uniqueFilename,
            //     'path' => $path,
            //     'directory_path' => $directoryPath,
            //     'relative_path' => $relativePath,
            //     'size' => $fileSize,
            //     'mime_type' => $mimeType,
            //     'file_type' => $fileType,
            // ]);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'original_name' => $originalName,
                    'stored_name' => $uniqueFilename,
                    'path' => $path,
                    'url' => Storage::url($path),
                    'size' => $fileSize,
                    'mime_type' => $mimeType,
                    'file_type' => $fileType,
                    'directory_path' => $directoryPath,
                    'relative_path' => $relativePath,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sanitize directory path to prevent directory traversal attacks
     * 
     * @param string $path
     * @return string
     */
    private function sanitizePath(string $path): string
    {
        // Remove any ../ or .\ sequences
        $path = str_replace(['../', '..\\'], '', $path);
        
        // Remove leading and trailing slashes
        $path = trim($path, '/\\');
        
        // Replace backslashes with forward slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove any remaining dangerous characters
        $path = preg_replace('/[^a-zA-Z0-9\/_\-]/', '_', $path);
        
        return $path;
    }
    
    /**
     * Get predetermined storage path based on file type
     * 
     * @param string $fileType
     * @return string
     */
    private function getStoragePath(string $fileType): string
    {
        $paths = [
            'images' => 'uploads/images',
            'videos' => 'uploads/videos',
            'audio' => 'uploads/audio',
            'documents' => 'uploads/documents',
            'spreadsheets' => 'uploads/spreadsheets',
            'presentations' => 'uploads/presentations',
            'text' => 'uploads/text',
            'misc' => 'uploads/misc'
        ];
        
        return $paths[$fileType] ?? 'uploads/misc';
    }
    
    /**
     * Optional: Get all uploaded files
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // If you're storing metadata in database
        // $files = File::latest()->paginate(20);
        
        // Or list files from storage
        $directories = [
            'uploads/images',
            'uploads/videos',
            'uploads/audio',
            'uploads/documents',
            'uploads/spreadsheets',
            'uploads/presentations',
            'uploads/text',
            'uploads/misc'
        ];
        
        $files = [];
        foreach ($directories as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                $directoryFiles = Storage::disk('public')->files($directory);
                foreach ($directoryFiles as $file) {
                    $files[] = [
                        'path' => $file,
                        'url' => Storage::url($file),
                        'size' => Storage::disk('public')->size($file),
                        'modified' => Storage::disk('public')->lastModified($file),
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'files' => $files
        ]);
    }
    
    /**
     * Optional: Delete uploaded file
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $path = $request->input('path');
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
