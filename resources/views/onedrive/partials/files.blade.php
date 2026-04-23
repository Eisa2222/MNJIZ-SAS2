<div class="row gy-6 mb-6" id="filesContainer">
    @forelse($files as $file)
        <div class="col-sm-6 col-lg-4 file-card">
            <div class="card p-3 h-100 shadow-none border">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-label-{{ isset($file['folder']) && $file['folder'] ? 'primary' : 'secondary' }}">
                            {{ isset($file['folder']) && $file['folder'] ? 'مجلد' : 'ملف' }}
                        </span>
                        <p class="d-flex align-items-center mb-0">
                            @php
                                // تحديد أيقونة بناءً على نوع الملف
                                $icon = 'ti-file';
                                if(isset($file['folder']) && $file['folder']) {
                                    $icon = 'ti-folder';
                                } else {
                                    switch($file['file_extension'] ?? '') {
                                        case 'txt':
                                            $icon = 'ti-file-text';
                                            break;
                                        case 'docx':
                                            $icon = 'ti-file-word';
                                            break;
                                        case 'xlsx':
                                            $icon = 'ti-file-excel';
                                            break;
                                        case 'pptx':
                                            $icon = 'ti-file-powerpoint';
                                            break;
                                        default:
                                            $icon = 'ti-file';
                                    }
                                }
                            @endphp
                            <i class="ti {{ $icon }} ti-lg me-1"></i>
                            @if(isset($file['folder']) && $file['folder'])
                                <a href="{{ route('onedrive.viewFolder', $file['id']) }}" class="text-decoration-none">{{ $file['name'] }}</a>
                            @else
                                <a href="#" class="text-decoration-none open-preview" data-embed-link="{{ $file['embedLink'] }}" data-file-name="{{ $file['name'] }}">{{ $file['name'] }}</a>
                            @endif
                        </p>
                    </div>
                    @if(!$file['folder'])
                        <p class="mb-2">نوع: {{ $file['mimeType'] ?? 'N/A' }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        @if(!isset($file['folder']) || !$file['folder'])
                            <div class="d-flex gap-2">
                                <a href="{{ route('onedrive.downloadFile', $file['id']) }}" class="btn btn-sm btn-icon btn-primary" title="تنزيل"><i class="ti ti-download ti-xs"></i></a>
                                <a href="{{ route('onedrive.editFile', $file['id']) }}" class="btn btn-sm btn-icon btn-warning" title="تحرير"><i class="ti ti-edit ti-xs"></i></a>
                                <a href="#" class="btn btn-sm btn-icon btn-info open-preview" title="عرض" data-embed-link="{{ $file['embedLink'] }}" data-file-name="{{ $file['name'] }}"><i class="ti ti-eye ti-xs"></i></a>
                            </div>
                        @endif
                        <form action="{{ route('onedrive.deleteItem', $file['id']) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-icon btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')" title="حذف"><i class="ti ti-trash ti-xs"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                لا توجد ملفات أو مجلدات.
            </div>
        </div>
    @endforelse
</div>

