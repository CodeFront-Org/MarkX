<?php

namespace App\Http\Controllers;

use App\Models\CompanyFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $files = CompanyFile::orderBy('created_at', 'desc')->get();
        $categories = CompanyFile::CATEGORIES;
        return view('company-files.index', compact('files', 'categories'));
    }    public function store(Request $request)
    {
        // Ensure user is authenticated and is a manager
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to upload files.');
        }

        if (auth()->user()->role !== 'manager') {
            return back()->with('error', 'Only managers can upload files.');
        }

        $userId = auth()->id();
        if (!$userId) {
            return back()->with('error', 'User authentication error. Please try logging in again.');
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|string',
            'category' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store file in the appropriate category folder
        $categoryPath = Str::snake($request->category);
        $path = $file->storeAs("company-files/{$categoryPath}", $fileName, 'public');
        
        // Verify user is still authenticated
        $userId = auth()->id();
        if (!$userId) {
            // Delete the uploaded file if user is not authenticated
            Storage::disk('public')->delete($path);
            return back()->with('error', 'Authentication error occurred. Please try again.');
        }

        try {
            $file = CompanyFile::create([
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_type' => $request->type,
                'category' => $request->category,
                'description' => $request->description,
                'path' => $path,
                'user_id' => $userId,
            ]);

            if (!$file) {
                // Delete the uploaded file if database insert fails
                Storage::disk('public')->delete($path);
                return back()->with('error', 'Failed to create file record. Please try again.');
            }

            return back()->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            // Delete the uploaded file if an error occurs
            Storage::disk('public')->delete($path);
            Log::error('CompanyFile creation failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while uploading the file. Please try again.');
        }
    }

    public function download($fileName)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to download files.');
        }

        $file = CompanyFile::where('file_name', $fileName)->firstOrFail();
        
        if (!Storage::disk('public')->exists($file->path)) {
            return back()->with('error', 'File not found.');
        }

        return response()->download(
            storage_path('app/public/' . $file->path), 
            $file->original_name
        );
    }

    public function destroy($fileName)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to delete files.');
        }

        if (auth()->user()->role !== 'manager') {
            return back()->with('error', 'Only managers can delete files.');
        }

        $file = CompanyFile::where('file_name', $fileName)->firstOrFail();
        
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        
        $file->delete();
        return back()->with('success', 'File deleted successfully.');
    }
}
