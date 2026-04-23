<!-- resources/views/onedrive/file_details.blade.php -->

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل الملف</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">تفاصيل الملف</h1>

    <a href="{{ route('onedrive.files') }}" class="btn btn-secondary mb-3">عودة إلى قائمة الملفات</a>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $file['name'] }}</h5>
            <p class="card-text"><strong>الحجم:</strong> {{ isset($file['size']) ? number_format($file['size'] / 1024, 2) . ' KB' : 'غير متوفر' }}</p>
            <p class="card-text"><strong>آخر تعديل:</strong> {{ isset($file['lastModifiedDateTime']) ? \Carbon\Carbon::parse($file['lastModifiedDateTime'])->format('Y-m-d H:i') : 'غير متوفر' }}</p>
            <p class="card-text"><strong>نوع الملف:</strong> {{ isset($file['file']['mimeType']) ? $file['file']['mimeType'] : 'غير متوفر' }}</p>
            <a href="{{ route('onedrive.file.download', ['fileId' => $file['id']]) }}" class="btn btn-success">تحميل الملف</a>
            <form action="{{ route('onedrive.file.createEmbedLink', ['fileId' => $file['id']]) }}" method="POST" style="display:inline-block;">
                @csrf
                <button type="submit" class="btn btn-warning">إنشاء رابط تضمين</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
