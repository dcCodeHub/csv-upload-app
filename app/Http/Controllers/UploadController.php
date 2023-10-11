<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessCsvFile;
use App\Models\Upload;
use App\Models\UploadHistory;
use App\Models\CsvField;
use Illuminate\Http\RedirectResponse;

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
        $fileHash = md5_file($file->getRealPath());
    
        // Check if the file already exists
        $existingUpload = Upload::where('file_hash', $fileHash)->first();
    
        if ($existingUpload) {
            $existingUpload->touch();
            $upload = $existingUpload;
        } else {
            // Create a new Upload record
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('uploads');
    
            $upload = new Upload();
            $upload->file_name = $fileName;
            $upload->file_path = $filePath;
            $upload->file_hash = $fileHash;
            $upload->status = 'Pending';
            $upload->user_id = 1; // Change this to use auth()->id() for the authenticated user
            $upload->save();
        }
    
        // Read and process the CSV file
        $fileContents = Storage::get($upload->file_path);
    
        // Clean non-UTF-8 characters
        $fileContents = iconv('UTF-8', 'UTF-8//IGNORE', $fileContents);
    
        // Split the CSV into lines
        $lines = preg_split('/\r\n|\n|\r/', $fileContents);
    
        // Check if the file has at least one line
        if (count($lines) < 1) {
            return redirect('/upload/index')->with('error', 'The uploaded file is empty.');
        }
    
        // Determine field indexes from the header line
        $header = str_getcsv(array_shift($lines));

        // Remove BOM character from the header if it exists
        if (count($header) > 0 && strpos($header[0], "\u{FEFF}") === 0) {
            $header[0] = substr($header[0], 3); // Remove the BOM character
        }

        $fieldIndexes = array_flip($header);
    
        // Process the CSV data and populate $csvData with the data you want to store
        $csvData = [];
        foreach ($lines as $line) {
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
    
            $data = str_getcsv($line); // Parse line as CSV
    
            $csvData[] = [
                'UNIQUE_KEY' => $data[$fieldIndexes['UNIQUE_KEY']],
                'PRODUCT_TITLE' => $data[$fieldIndexes['PRODUCT_TITLE']],
                'PRODUCT_DESCRIPTION' => $data[$fieldIndexes['PRODUCT_DESCRIPTION']],
                'STYLE#' => $data[$fieldIndexes['STYLE#']],
                'SANMAR_MAINFRAME_COLOR' => $data[$fieldIndexes['SANMAR_MAINFRAME_COLOR']],
                'SIZE' => $data[$fieldIndexes['SIZE']],
                'COLOR_NAME' => $data[$fieldIndexes['COLOR_NAME']],
                'PIECE_PRICE' => $data[$fieldIndexes['PIECE_PRICE']],
            ];
        }
    
        // Store CSV data in the csv_fields table
        $csvFields = [];
        foreach ($csvData as $data) {
            CsvField::updateOrCreate(
                [
                    'unique_key' => $data['UNIQUE_KEY'],
                ],
                [
                    'upload_id' => $upload->id,
                    'product_title' => $data['PRODUCT_TITLE'],
                    'product_description' => $data['PRODUCT_DESCRIPTION'],
                    'style' => $data['STYLE#'],
                    'sanmar_mainframe_color' => $data['SANMAR_MAINFRAME_COLOR'],
                    'size' => $data['SIZE'],
                    'color_name' => $data['COLOR_NAME'],
                    'piece_price' => $data['PIECE_PRICE'],
                ]
            );
        }
    
        // Create an UploadHistory record
        $uploadHistory = new UploadHistory();
        $uploadHistory->file_name = $upload->file_name;
        $uploadHistory->uploaded_at = now();
        $uploadHistory->status = 'Uploaded';
        $uploadHistory->save();
    
        // Dispatch the ProcessCsvFile job for background processing
        ProcessCsvFile::dispatch($upload);
    
        return redirect('/upload/index')->with('success', 'File uploaded and is being processed.');
    }    
}
