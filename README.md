I had a personal problem where I had one of my projects running on two different servers and I needed to move files from one to another. the second server was using custom S3 infrastructure and I needed to move ~19000 files from one server to another. I couldn't connect with WinSCP and I didn't feel like using a CLI so I made this staggered asynchronous file uploader with Claude. it's made for Laravel but with some tweaks you could make it work with regular PHP as well. I decided to share this in case someone could use it.

# 📦 Async File Uploader for Laravel

A beautiful, production-ready file uploader with folder support, automatic retries, and progress tracking. Built for Laravel with a stunning dark-themed UI.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Laravel](https://img.shields.io/badge/laravel-9.x%20%7C%2010.x%20%7C%2011.x-red.svg)

## ✨ Features

### 🎯 Core Functionality
- **Async Staggered Uploads** - Files upload one at a time for better control and reliability
- **Folder Upload Support** - Select entire folders with recursive directory traversal
- **Directory Structure Preservation** - Maintains original folder hierarchy in storage
- **Drag & Drop** - Support for both files and folders
- **Real-time Progress Tracking** - Live percentage updates for each file
- **Smart File Categorization** - Automatic sorting by file type

### 🔄 Advanced Features
- **Automatic Retry Logic** - 3 automatic retries with exponential backoff
- **Manual Retry Button** - Unlimited manual retries for failed uploads
- **Upload Statistics** - Real-time tracking of total, completed, and failed uploads
- **Error Handling** - Comprehensive error messages and status indicators
- **Path Sanitization** - Security protection against directory traversal attacks
- **No Form Required** - Direct file handling without traditional form inputs

### 🎨 Beautiful UI
- **Dark Theme** - Modern, eye-friendly interface
- **Smooth Animations** - Polished micro-interactions and transitions
- **Status Indicators** - Color-coded states (pending, uploading, retrying, complete, failed)
- **Responsive Design** - Works on desktop and mobile devices
- **Custom Fonts** - Syne + JetBrains Mono for a distinctive look

## 📸 Screenshots

```
┌─────────────────────────────────────────────┐
│  📦 Upload Files                            │
│  Async staggered uploader · One at a time  │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │   📦                                 │   │
│  │   Drop files/folders here or click  │   │
│  │   Files will be uploaded one by one │   │
│  │                                      │   │
│  │  [Select Files] [Select Folder]     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Total Files: 5  Completed: 3  Failed: 0   │
│                                             │
│  🖼️ image.jpg                               │
│  📁 AllFiles/Photos · 2.3 MB               │
│  ████████████████████░░░░ 78%              │
│                                             │
│  📄 document.pdf                            │
│  📁 AllFiles/Documents · 1.1 MB            │
│  ████████████████████████ 100% ✓           │
└─────────────────────────────────────────────┘
```

## 🚀 Quick Start

### 1. Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/async-file-uploader.git

# Navigate to your Laravel project
cd your-laravel-project
```

### 2. Backend Setup

**Create the controller:**
```bash
php artisan make:controller FileUploadController
```

Copy the contents of `FileUploadController.php` to:
```
app/Http/Controllers/FileUploadController.php
```

**Add routes** to `routes/web.php`:
```php
use App\Http\Controllers\FileUploadController;

Route::post('/upload', [FileUploadController::class, 'upload'])->name('file.upload');
Route::get('/files', [FileUploadController::class, 'index'])->name('file.index');
Route::delete('/files', [FileUploadController::class, 'delete'])->name('file.delete');
```

**Create storage link:**
```bash
php artisan storage:link
```

### 3. Frontend Setup

**Option A: Blade Template**

Create `resources/views/upload.blade.php`:
```blade
@extends('layouts.app')

@section('content')
    <!-- Copy the contents of async-uploader.html here -->
@endsection
```

Add route:
```php
Route::get('/upload', function () {
    return view('upload');
});
```

**Option B: Standalone**

Place `async-uploader.html` in your `public` directory and access directly.

### 4. Configuration

Update the CSRF token in the HTML file:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

That's it! Visit `/upload` to start using the uploader.

## 📁 File Structure

```
async-file-uploader/
├── async-uploader.html           # Frontend UI with JavaScript
├── FileUploadController.php      # Laravel backend controller
├── routes.php                     # Route definitions
├── README.md                      # This file
├── FOLDER_UPLOAD_EXAMPLE.md      # Detailed folder upload documentation
└── RETRY_FUNCTIONALITY.md        # Retry feature documentation
```

## 🎯 Usage

### Uploading Individual Files

1. Click **"Select Files"** button
2. Choose one or multiple files
3. Files upload automatically one by one
4. Monitor progress in real-time

### Uploading Folders

1. Click **"Select Folder"** button
2. Choose a folder from your computer
3. All files are extracted recursively
4. Directory structure is preserved

**Example:**
```
Selected: AllFiles/
├── UserFiles1/
│   ├── doc.pdf
│   └── image.jpg
└── UserFiles2/
    └── video.mp4
```

**Stored as:**
```
storage/app/public/
└── uploads/
    ├── documents/
    │   └── AllFiles/UserFiles1/doc_timestamp.pdf
    ├── images/
    │   └── AllFiles/UserFiles1/image_timestamp.jpg
    └── videos/
        └── AllFiles/UserFiles2/video_timestamp.mp4
```

### Drag & Drop

Simply drag files or folders onto the dropzone - works exactly like the buttons!

## 🔄 Retry Functionality

### Automatic Retries
- **3 automatic attempts** per file
- Exponential backoff (1s, 2s, 3s delays)
- Status shows: `Retrying (1/3)`, `Retrying (2/3)`

### Manual Retries
- Appears after automatic retries fail
- Click **🔄 Retry Upload** button
- Resets retry counter for fresh attempt
- Unlimited manual retries allowed

### Visual States

| Status | Color | Description |
|--------|-------|-------------|
| Pending | Gray | Waiting in queue |
| Uploading | Orange | Currently uploading |
| Retrying | Yellow | Automatic retry in progress |
| Complete | Green | Successfully uploaded |
| Failed | Red | All retries failed (manual retry available) |

## ⚙️ Configuration

### Adjust Max Retries

In `async-uploader.html`, modify the file object:
```javascript
maxRetries: 5,  // Change from 3 to 5 automatic retries
```

### Change Storage Paths

In `FileUploadController.php`:
```php
private function getStoragePath(string $fileType): string
{
    $paths = [
        'images' => 'custom/path/images',
        'videos' => 'custom/path/videos',
        // ... customize as needed
    ];
    
    return $paths[$fileType] ?? 'uploads/misc';
}
```

### Modify File Size Limit

In `FileUploadController.php`:
```php
'file' => 'required|file|max:204800', // 200MB (in KB)
```

In `php.ini`:
```ini
upload_max_filesize = 200M
post_max_size = 200M
max_execution_time = 600
```

### Customize Upload Endpoint

In `async-uploader.html` (line ~820):
```javascript
xhr.open('POST', '/your-custom-endpoint');
```

## 📂 File Type Categories

Files are automatically categorized and stored in predetermined locations:

| Category | File Types | Storage Path |
|----------|------------|--------------|
| Images | jpg, png, gif, webp, svg | `uploads/images/` |
| Videos | mp4, avi, mov, wmv | `uploads/videos/` |
| Audio | mp3, wav, ogg, flac | `uploads/audio/` |
| Documents | pdf, doc, docx, txt | `uploads/documents/` |
| Spreadsheets | xls, xlsx, csv | `uploads/spreadsheets/` |
| Presentations | ppt, pptx, key | `uploads/presentations/` |
| Text | txt, md, log | `uploads/text/` |
| Misc | Everything else | `uploads/misc/` |

## 🗄️ Database Storage (Optional)

### Migration

```bash
php artisan make:migration create_files_table
```

```php
Schema::create('files', function (Blueprint $table) {
    $table->id();
    $table->string('original_name');
    $table->string('stored_name');
    $table->string('path');
    $table->string('directory_path')->nullable();
    $table->string('relative_path')->nullable();
    $table->bigInteger('size');
    $table->string('mime_type');
    $table->string('file_type');
    $table->timestamps();
    
    $table->index('directory_path');
    $table->index('file_type');
});
```

Uncomment the database save code in `FileUploadController.php` (around line 73).

### Querying Files

```php
// Get all files from a specific directory
$files = File::where('directory_path', 'AllFiles/UserFiles1')->get();

// Get all files from a folder and subfolders
$files = File::where('directory_path', 'LIKE', 'AllFiles/%')->get();

// Get files by type
$images = File::where('file_type', 'images')->get();

// Group by directory
$filesByDirectory = File::all()->groupBy('directory_path');
```

## 🔒 Security Features

### Path Sanitization
Automatically prevents directory traversal attacks:
```php
// Input: "../../../etc/passwd"
// Sanitized: "etc/passwd"
```

### CSRF Protection
Automatically handled by Laravel for web routes.

### File Type Validation
Add mime type restrictions:
```php
'file' => 'required|file|mimes:jpeg,png,pdf,docx|max:10240'
```

### Authentication
Protect routes with middleware:
```php
Route::middleware(['auth'])->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload']);
});
```

## 🌐 Browser Compatibility

| Feature | Chrome | Firefox | Safari | Edge | Opera |
|---------|--------|---------|--------|------|-------|
| File Upload | ✅ | ✅ | ✅ | ✅ | ✅ |
| Folder Upload | ✅ | ✅ | ✅ 11.1+ | ✅ | ✅ |
| Drag & Drop | ✅ | ✅ | ✅ | ✅ | ✅ |
| Progress Tracking | ✅ | ✅ | ✅ | ✅ | ✅ |

## 🐛 Troubleshooting

### CSRF Token Mismatch
- Ensure meta tag has correct token: `<meta name="csrf-token" content="{{ csrf_token() }}">`
- For API routes, use Sanctum or disable CSRF

### Upload Fails Silently
- Check PHP error logs
- Verify storage permissions: `chmod -R 775 storage/`
- Check file size limits in `php.ini`

### Progress Not Showing
- Must use `XMLHttpRequest`, not `fetch`
- Check browser console for errors

### Files Not Accessible
- Run `php artisan storage:link`
- Verify `APP_URL` in `.env` matches your domain

### Folder Selection Not Working
- Check browser compatibility (Safari 11.1+ required)
- Ensure `webkitdirectory` attribute is present

## 🎨 Customization

### Change Colors

In `async-uploader.html`, modify CSS variables:
```css
:root {
    --primary: #FF6B35;        /* Orange accent */
    --secondary: #004E89;      /* Blue accent */
    --background: #0A0E27;     /* Dark background */
    --success: #06D6A0;        /* Green for success */
    --error: #EF476F;          /* Red for errors */
}
```

### Change Fonts

Replace Google Fonts import:
```html
<link href="https://fonts.googleapis.com/css2?family=YourFont:wght@400;600&display=swap" rel="stylesheet">
```

Update CSS:
```css
body {
    font-family: 'YourFont', sans-serif;
}
```

### Modify Animation Speed

```css
.file-item {
    animation: slideIn 0.3s ease-out backwards;  /* Change 0.3s */
}
```

## 📊 Performance

- **Handles hundreds of files** efficiently
- **One-at-a-time uploads** prevent server overload
- **Progress tracking** uses minimal resources
- **Automatic retries** reduce manual intervention
- **Exponential backoff** prevents server hammering

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 API Response Format

### Success Response
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "original_name": "document.pdf",
    "stored_name": "document_1734307200_abc123.pdf",
    "path": "uploads/documents/AllFiles/document_1734307200_abc123.pdf",
    "url": "/storage/uploads/documents/AllFiles/document_1734307200_abc123.pdf",
    "size": 245680,
    "mime_type": "application/pdf",
    "file_type": "documents",
    "directory_path": "AllFiles",
    "relative_path": "AllFiles/document.pdf"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Upload failed",
  "error": "File too large"
}
```

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: Laravel 9+, PHP 8+
- **Storage**: Laravel Storage (local or cloud)
- **Design**: Custom dark theme with Syne + JetBrains Mono fonts
- **Icons**: Unicode emoji icons

## 📋 Requirements

- PHP 8.0+
- Laravel 9.x, 10.x, or 11.x
- Modern web browser with JavaScript enabled
- Storage write permissions

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Built with Laravel's elegant file handling
- Inspired by modern file upload UX patterns
- Dark theme design inspired by developer tools
- Typography uses Google Fonts (Syne & JetBrains Mono)

## 📞 Support

- 📖 [Documentation](./README.md)
- 🔄 [Retry Functionality Guide](./RETRY_FUNCTIONALITY.md)
- 📁 [Folder Upload Examples](./FOLDER_UPLOAD_EXAMPLE.md)
- 🐛 [Issue Tracker](https://github.com/yourusername/async-file-uploader/issues)

<!-- ## 🗺️ Roadmap

- [ ] Chunked uploads for very large files (>100MB)
- [ ] Resume capability for interrupted uploads
- [ ] Parallel uploads option (configurable)
- [ ] Cloud storage integration (S3, Dropbox)
- [ ] Image compression before upload
- [ ] Virus scanning integration
- [ ] Multi-language support
- [ ] Video preview generation
- [ ] Batch operations (delete multiple, retry all)
- [ ] Upload history and analytics -->

## 📈 Changelog

### Version 1.0.0 (2024)
- ✨ Initial release
- 📁 Folder upload with recursive traversal
- 🔄 Automatic retry with exponential backoff
- 🎯 Manual retry button
- 📊 Real-time statistics
- 🎨 Beautiful dark theme UI
- 🔒 Path sanitization security
- 📱 Responsive design

---

Made with ❤️ for the Laravel community

**Star ⭐ this repo if you find it useful!**
