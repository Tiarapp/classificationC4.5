<!DOCTYPE html>
<html>
<head>
    <title>Simple File Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Simple Upload Test</h1>
    
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if (session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif
    
    <form action="{{ route('admin.import.test') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <p>
            <label>Select File:</label><br>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
        </p>
        <p>
            <button type="submit">Upload & Test</button>
        </p>
    </form>
    
    <script>
        // Simple debug
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.querySelector('input[type="file"]');
            console.log('Form submitting with file:', fileInput.files[0]);
            
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('Please select a file');
                return;
            }
            
            console.log('File details:', {
                name: fileInput.files[0].name,
                size: fileInput.files[0].size,
                type: fileInput.files[0].type
            });
        });
    </script>
</body>
</html>