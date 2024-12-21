<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-control {
            display: block;
            width: fit-content;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        button {
            float: right;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    {{-- <div class="center">
        <form id="uploadForm" action="{{ route('customer.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input class="form-control" type="file" name="file" id="file">
            <br>
            <button type="submit">Upload</button>
            <br>
            <div id="status"></div>
        </form>
    </div> --}}

    <div id="upload-container" class="flex flex-col items-center p-6 bg-gray-100 rounded-lg shadow-lg w-1/3 mx-auto">
        <!-- Title -->
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Upload Your File</h2>

        <!-- File Browse Button -->
        <button id="browse"
            class="px-4 py-2 bg-blue-500 text-white font-medium rounded-md shadow hover:bg-blue-600 transition">
            Choose File
        </button>

        <!-- Progress Bar Container -->
        <div id="progress-container" class="w-full mt-6">
            <div class="relative w-full h-4 bg-gray-300 rounded">
                <!-- Dynamic Progress Bar -->
                <div id="progress-bar" class="absolute top-0 left-0 h-full bg-blue-500 rounded" style="width: 0%;">
                </div>
            </div>
            <div id="progress-text" class="text-sm text-gray-600 mt-2 text-center">0% Complete</div>
        </div>
        <div id="status" class="mt-3"></div>
    </div>


    {{-- <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('status').innerHTML = 'please wait...';
            // Waktu mulai upload
            const startTime = new Date();

            const formData = new FormData();
            formData.append('file', document.getElementById('file').files[0]);

            fetch('{{ route('customer.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                const endTime = new Date();
                const duration = (endTime - startTime) / 1000; // Durasi dalam detik

                // Menampilkan durasi waktu upload
                document.getElementById('status').innerHTML = `File uploaded successfully! Upload took ${duration} seconds.`;
            })
            .catch(error => {
                document.getElementById('status').innerHTML = 'Error during file upload.';
            });
        });
    </script> --}}

    <script src="https://cdn.jsdelivr.net/npm/resumablejs"></script>
    <script>
        let startTime = new Date();
        let endTime = new Date();
        var r = new Resumable({
            target: '/upload-chunks',
            chunkSize: 2 * 1024 * 1024, // 2 MB per chunk
            testChunks: false,
            simultaneousUploads: 3,
            query: {
                _token: "{{ csrf_token() }}"
            }
        });

        r.assignBrowse(document.getElementById('browse'));

        r.on('fileAdded', function(file) {
            startTime = new Date()
            r.upload();
        });

        r.on('fileProgress', function(file) {
            // Hitung persentase progres
            const progressPercentage = Math.floor(file.progress() * 100);

            // Perbarui teks progres
            document.getElementById('progress-text').innerText = `${progressPercentage}% Complete`;

            // Perbarui lebar progress bar
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = `${progressPercentage}%`;

            // Tambahkan animasi pada progress bar (opsional)
            progressBar.style.transition = 'width 0.3s ease-in-out';
        });

        r.on('fileSuccess', function(file, message){
            console.log(JSON.parse(message));
        });


        r.on('complete', function(file, message) {
            endTime = new Date();
            const duration = (endTime - startTime) / 1000; // Durasi dalam detik
            document.getElementById('status').innerHTML = `File uploaded successfully! Upload took ${duration} seconds.`;
            // alert('Upload complete!');
        });
    </script>
</body>

</html>
