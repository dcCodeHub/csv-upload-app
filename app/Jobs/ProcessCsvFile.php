<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Models\Upload; // Import the Upload model
use App\Notifications\CsvProcessingComplete; // Import the CsvProcessingComplete notification

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        // Read and process the CSV file
        $fileContents = Storage::get($this->filePath);
        // Add your CSV processing logic here
        // For example, you can parse and process data, calculate results, etc.

        // Update the upload record with processing status and results
        $upload = Upload::where('file_path', $this->filePath)->first();
        if ($upload) {
            $upload->status = 'Processed';
            $upload->processing_result = 'Your processing result goes here';
            $upload->save();

            // Notify the user when processing is complete
            $user = $upload->user; // Assuming there's a relationship between Upload and User
            if ($user) {
                $user->notify(new CsvProcessingComplete());
            }
        }
    }
}
