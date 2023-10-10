<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessCsvFile; // Import the ProcessCsvFile job
use App\Models\Upload; // Import the Upload model
use App\Models\UploadHistory; // Import the UploadHistory model

class UploadController extends Controller
{
    public function index()
    {
        $uploadHistories = UploadHistory::orderBy('uploaded_at', 'desc')->get();

        return view('upload', ['uploadHistories' => $uploadHistories]);
    }

    public function store(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Store the uploaded file
        $file = $request->file('csv_file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('uploads');

        // Create an Upload record in the database
        $upload = new Upload();
        $upload->file_name = $fileName;
        $upload->file_path = $filePath;
        $upload->status = 'Pending'; // initial status
        $upload->user_id = 1; // hard coded id for testing, use auth()->id() if auth
        $upload->save();

        // After successfully processing the file
        $uploadHistory = new UploadHistory();
        $uploadHistory->file_name = $fileName;
        $uploadHistory->uploaded_at = now(); // Use the current timestamp
        $uploadHistory->status = 'Uploaded'; // Set the status as needed
        $uploadHistory->save();

        // Dispatch the ProcessCsvFile job for background processing
        ProcessCsvFile::dispatch($upload->id);

        return redirect('/upload/index')->with('success', 'File uploaded and is being processed.');
    }
}
