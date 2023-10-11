<!DOCTYPE html>
<html>

<head>
    <title>CSV File Upload</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="container mt-5">
        <h1>CSV File Upload</h1>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ url('/upload/store') }}" enctype="multipart/form-data" class="dropzone" id="mydropzone">
            @csrf
            <div class="fallback">
                <input type="file" id="csv_file" name="csv_file">
            </div>
        </form>
        <div class="text-center mt-3">
            <!-- Add an "ONCLICK upload" function to the button -->
            <button disabled="true" id="submit" class="btn btn-primary" onclick="uploadFile()">Upload CSV</button>
        </div>
    </div>
    <div class="container">
        <h1>Upload History</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uploadHistories as $upload)
                <tr>
                    <td>{{ $upload->file_name }}</td>
                    <td>{{ $upload->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $upload->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS and jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        Dropzone.autoDiscover = false;
        var uploadBtn = document.getElementById("submit");

        var myDropzone = new Dropzone('#mydropzone', {
            url: "{{ url('/upload/store') }}",
            paramName: "csv_file", // Name of the file input field
            maxFilesize: 50, // Maximum file size in MB
            acceptedFiles: ".csv", // Allowed file types
            dictDefaultMessage: "Drag & drop a CSV file here",
            dictInvalidFileType: "Invalid file type. Only CSV files are allowed.",
            autoProcessQueue: false,
            parallelUploads: 10
        })

        // Function to manually trigger the form submission when the button is clicked
        function uploadFile() {
            myDropzone.processQueue()
        }

        myDropzone.on("addedfile", function(file) {
            // update button state
            uploadBtn.disabled = false;
        });

        myDropzone.on('queuecomplete', function() {
            toastr.success('Successfully uploaded!')
            uploadBtn.disabled = true;
            location.reload();
        })
    </script>
</body>

</html>