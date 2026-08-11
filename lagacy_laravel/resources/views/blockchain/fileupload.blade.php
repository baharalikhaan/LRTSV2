<form action="{{ route('blockchainuploadpost') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="file">Upload a ZIP file:</label>
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>
