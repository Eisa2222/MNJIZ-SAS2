@extends('layouts.layoutMaster')
@php
    $configData = App\Helpers\Helpers::appClasses();
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إدارة الملفات</a>
        <i class="ti ti-star favorite-icon" data-page-name="إدارة الملفات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('title', 'إدارة الملفات - OneDrive')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/plyr/plyr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <!-- إضافة Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-pV84EeVdGEM9+cOzF4fPz3+OZXovRGe9LcPU2M+E6MzG6T1mt+GcPG1cY1VQ0CcbvNFKjAQKQwLjqPZxkULVNg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-academy.scss')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/plyr/plyr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite('resources/assets/js/app-academy-course.js')
@endsection

@section('content')
    <div class="drive">
        <!-- قسم العنوان الرئيسي والوصف -->
        <div class="card p-0 mb-4 shadow-sm border-0 rounded">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
                <!-- الصورة الأولى -->
                <div class="text-center text-md-left mb-4 mb-md-0">
                    <img src="{{ asset('assets/img/illustrations/bulb-' . $configData['style'] . '.png') }}" 
                         class="img-fluid" 
                         alt="مصباح في اليد" 
                         data-app-light-img="illustrations/bulb-light.png"
                         data-app-dark-img="illustrations/bulb-dark.png"
                         style="max-width: 120px;">
                </div>
                <!-- النص الرئيسي -->
                <div class="text-center mb-4 mb-md-0 flex-grow-1">
                    <h2 class="mb-2" style="font-size: 1.5rem;">إدارة ملفاتك ومجلداتك في OneDrive</h2>
                    <p class="text-muted mb-3" style="font-size: 0.95rem;">
                        كل شيء في مكان واحد. قم برفع، إنشاء، وإدارة ملفاتك ومجلداتك بسهولة باستخدام واجهة OneDrive المدمجة لدينا.
                    </p>
                    <div class="input-group mt-3" style="max-width: 400px; margin: 0 auto;">
                        <input type="search" id="searchInput" 
                               placeholder="ابحث عن ملف أو مجلد" 
                               class="form-control" 
                               aria-label="Search">
                        <button type="button" id="searchButton" class="btn btn-primary">
                            <i class="ti ti-search ti-sm"></i>
                        </button>
                    </div>
                </div>
                <!-- الصورة الثانية -->
                <div class="d-flex align-items-end justify-content-center">
                    <img src="{{ asset('assets/img/illustrations/pencil-rocket.png') }}" 
                         alt="صاروخ قلم" 
                         height="140" 
                         class="scaleX-n1-rtl">
                </div>
            </div>
        </div>
        
        <!-- قسم نماذج العمليات -->
        <div class="card mb-6 shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">ملفاتي في OneDrive</h5>
                    <p class="mb-0 text-muted">إدارة ملفاتك ومجلداتك بكفاءة</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <!-- أزرار لفتح النوافذ المنبثقة -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal"><i
                            class="ti ti-upload me-1"></i> رفع ملف</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i
                            class="ti ti-folder-plus me-1"></i> إنشاء مجلد</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFileModal"><i
                            class="ti ti-file-plus me-1"></i> إنشاء ملف</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadLargeFileModal"><i
                            class="ti ti-upload-cloud me-1"></i> رفع ملف كبير</button>
                </div>
            </div>
            <div class="card-body">
                <!-- قسم عرض الملفات والمجلدات بشكل بطاقات -->
                <div class="row gy-4" id="filesContainer">
                    @forelse($files as $file)

                        <div class="col-sm-6 col-lg-3 file-card">
                            <div class="card h-100 shadow-sm border-0 rounded-lg hover-shadow transition position-relative">
                                <div class="card-body d-flex flex-column">
                                    <!-- قسم العنوان والأيقونة مع زر العمليات -->
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            @if (isset($file['folder']) && $file['folder'])
                                                <i class="fa fa-folder fa-2x" style="color: #ffcf43;"></i>
                                                <a href="{{ route('onedrive.viewFolder', $file['id']) }}"
                                                    class="text-decoration-none text-dark fw-bold">{{ $file['name'] }}</a>
                                            @else
                                                @php
                                                    $fileExtension = $file['extension'] ?? '';
                                                @endphp

                                                @switch($fileExtension)
                                                    @case('folder')
                                                        <i class="fas fa-folder fa-2x text-primary"></i> مجلد
                                                    @break

                                                    {{-- مستندات --}}
                                                    @case('.doc')
                                                    @case('.docx')

                                                    @case('.dotx')
                                                        {{-- إضافة امتداد .dotx --}}
                                                    @case('.odt')
                                                        <i class="fas fa-file-word fa-2x text-primary"></i>
                                                    @break

                                                    @case('.pdf')
                                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                    @break

                                                    @case('.rtf')
                                                    @case('.txt')

                                                    @case('.md')
                                                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                                                    @break

                                                    @case('.html')
                                                    @case('.htm')
                                                        <i class="fas fa-file-code fa-2x text-info"></i>
                                                    @break

                                                    {{-- جداول بيانات --}}
                                                    @case('.xls')
                                                    @case('.xlsx')

                                                    @case('.ods')
                                                        <i class="fas fa-file-excel fa-2x text-success"></i>
                                                    @break

                                                    {{-- عروض تقديمية --}}
                                                    @case('.ppt')
                                                    @case('.pptx')

                                                    @case('.odp')
                                                        <i class="fas fa-file-powerpoint fa-2x text-warning"></i>
                                                    @break

                                                    {{-- صور --}}
                                                    @case('.jpg')
                                                    @case('.jpeg')

                                                    @case('.png')
                                                    @case('.gif')

                                                    @case('.bmp')
                                                    @case('.svg')

                                                    @case('.tif')
                                                    @case('.tiff')
                                                        <i class="fas fa-file-image fa-2x text-info"></i>
                                                    @break

                                                    {{-- فيديوهات --}}
                                                    @case('.mp4')
                                                    @case('.avi')

                                                    @case('.mov')
                                                    @case('.wmv')

                                                    @case('.flv')
                                                    @case('.mkv')
                                                        <i class="fas fa-file-video fa-2x text-warning"></i>
                                                    @break

                                                    {{-- ملفات صوتية --}}
                                                    @case('.mp3')
                                                    @case('.wav')

                                                    @case('.aac')
                                                    @case('.flac')

                                                    @case('.ogg')
                                                        <i class="fas fa-file-audio fa-2x text-muted"></i>
                                                    @break

                                                    {{-- أرشيفات --}}
                                                    @case('.zip')
                                                    @case('.rar')

                                                    @case('.7z')
                                                    @case('.tar')

                                                    @case('.gz')
                                                    @case('.bz2')
                                                        <i class="fas fa-file-archive fa-2x text-secondary"></i>
                                                    @break

                                                    {{-- ملفات برمجية --}}
                                                    @case('.php')
                                                    @case('.js')

                                                    @case('.css')
                                                    @case('.py')

                                                    @case('.java')
                                                    @case('.c')

                                                    @case('.cpp')
                                                    @case('.rb')

                                                    @case('.go')
                                                    @case('.json')

                                                    @case('.xml')
                                                        <i class="fas fa-file-code fa-2x text-info"></i>
                                                    @break

                                                    {{-- تنفيذيّة --}}
                                                    @case('.exe')
                                                    @case('.bat')

                                                    @case('.sh')
                                                    @case('.dll')
                                                        <i class="fas fa-file fa-2x text-dark"></i>
                                                    @break

                                                    {{-- أخرى --}}
                                                    @case('.iso')
                                                    @case('.bak')

                                                    @case('.log')
                                                    @case('.tmp')
                                                        <i class="fas fa-file fa-2x text-muted"></i>
                                                    @break

                                                    {{-- حالة افتراضية لأي امتداد غير معروف --}}

                                                    @default
                                                        <i class="fa fa-file fa-2x" style="color: #d3d3d3;"></i>
                                                @endswitch
                                                <a href="{{ route('onedrive.viewFile', $file['id']) }}"
                                                    class="text-decoration-none text-wrap text-dark fw-bold"
                                                    style="word-break: break-all;">{{ $file['name'] }}</a>
                                            @endif
                                        </div>
                                        <!-- زر العمليات -->
                                        <div class="dropdown">
                                            <button type="button" id="dropdownMenuButton{{ $file['id'] }}"
                                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="More options"
                                                style="background-color: transparent; border: none; padding: 0; font-size: 1rem; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-ellipsis-v" style="font-size: 1.25rem; color: inherit;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="dropdownMenuButton{{ $file['id'] }}">
                                                @if (!isset($file['folder']) || !$file['folder'])
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('onedrive.downloadFile', $file['id']) }}"><i
                                                                class="fa fa-download me-2"></i> تنزيل</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('onedrive.editFile', $file['id']) }}"><i
                                                                class="fa fa-edit me-2"></i> تحرير</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('onedrive.viewFile', $file['id']) }}"><i
                                                                class="fa fa-eye me-2"></i> عرض</a></li>
                                                @endif
                                                <li>
                                                    <form action="{{ route('onedrive.deleteItem', $file['id']) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        {{-- <button type="submit" class="dropdown-item" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                    <i class="fa fa-trash me-2"></i> حذف
                                                </button> --}}

                                                        <!-- Removed the onclick attribute and added 'delete-button' class -->
                                                        <button type="submit" class="dropdown-item delete-button">
                                                            <i class="fa fa-trash me-2"></i> حذف
                                                        </button>

                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- نهاية قسم الأيقونة والاسم مع الزر -->

                                </div>
                            </div>
                        </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center" id="noResultsMessage" style="display: none;">
                                    لا توجد نتائج مطابقة للبحث.
                                </div>
                                <div class="alert alert-info text-center" id="noItemsMessage">
                                    لا توجد ملفات أو مجلدات.
                                </div>
                            </div>
                        @endforelse
                    </div>

                </div>

                <!-- نوافذ منبثقة لنماذج العمليات -->

                <!-- نموذج رفع ملف -->
                <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('onedrive.uploadFile') }}" method="POST" enctype="multipart/form-data"
                            class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadFileModalLabel"><i class="ti ti-upload me-2"></i> رفع ملف
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="parent_id"
                                    value="{{ request()->routeIs('onedrive.viewFolder') ? request()->route('folderId') : 'root' }}">
                                <div class="mb-3">
                                    <label for="file" class="form-label">اختر ملف</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-2"></i>
                                    رفع</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- نموذج إنشاء مجلد -->
                <div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('onedrive.createFolder') }}" method="POST" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="createFolderModalLabel"><i class="ti ti-folder-plus me-2"></i>
                                    إنشاء
                                    مجلد
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="parent_id"
                                    value="{{ request()->routeIs('onedrive.viewFolder') ? request()->route('folderId') : 'root' }}">
                                <div class="mb-3">
                                    <label for="folder_name" class="form-label">اسم المجلد</label>
                                    <input type="text" name="folder_name" class="form-control"
                                        placeholder="أدخل اسم المجلد" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary"><i class="ti ti-folder-plus me-2"></i>
                                    إنشاء</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- نموذج إنشاء ملف جديد -->
                <div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('onedrive.createFile') }}" method="POST" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="createFileModalLabel"><i class="ti ti-file-plus me-2"></i> إنشاء
                                    ملف
                                    جديد
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="parent_id"
                                    value="{{ request()->routeIs('onedrive.viewFolder') ? request()->route('folderId') : 'root' }}">
                                <div class="mb-3">
                                    <label for="file_name" class="form-label">اسم الملف</label>
                                    <input type="text" name="file_name" class="form-control" placeholder="أدخل اسم الملف"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="file_type" class="form-label">نوع الملف</label>
                                    <select name="file_type" class="form-select" required>
                                        <option value="" disabled selected>اختر نوع الملف</option>
                                        <option value="txt">نص (.txt)</option>
                                        <option value="docx">Word (.docx)</option>
                                        <option value="xlsx">Excel (.xlsx)</option>
                                        <option value="pptx">PowerPoint (.pptx)</option>
                                        <!-- يمكنك إضافة أنواع أخرى حسب الحاجة -->
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary"><i class="ti ti-file-plus me-2"></i>
                                    إنشاء</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- نموذج رفع ملف كبير -->
                <div class="modal fade" id="uploadLargeFileModal" tabindex="-1" aria-labelledby="uploadLargeFileModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="uploadLargeFileForm" action="{{ route('onedrive.uploadLargeFile') }}" method="POST"
                            enctype="multipart/form-data" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadLargeFileModalLabel"><i
                                        class="ti ti-upload-cloud me-2"></i>
                                    رفع
                                    ملف كبير</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="parent_id"
                                    value="{{ request()->routeIs('onedrive.viewFolder') ? request()->route('folderId') : 'root' }}">
                                <div class="mb-3">
                                    <label for="large_file" class="form-label">اختر ملف كبير</label>
                                    <input type="file" name="file" id="large_file" class="form-control" required>
                                </div>
                                <!-- شريط تقدم -->
                                <div class="progress mt-3" style="height: 25px; display: none;" id="uploadProgress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%;" id="progressBar">0%</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-2"></i> رفع
                                    الملف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- سكريبت رفع الملفات الكبيرة وتحسينات أخرى -->
        <script>
            document.getElementById('uploadLargeFileForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);
                const fileInput = document.getElementById('large_file');
                const file = fileInput.files[0];

                if (!file) {
                    toastr.error('يرجى اختيار ملف للرفع.');
                    return;
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                // إظهار شريط التقدم
                document.getElementById('uploadProgress').style.display = 'block';

                // تحديث شريط التقدم
                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        const percentComplete = Math.round((event.loaded / event.total) * 100);
                        const progressBar = document.getElementById('progressBar');
                        progressBar.style.width = percentComplete + '%';
                        progressBar.textContent = percentComplete + '%';
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        toastr.success('تم رفع الملف بنجاح.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toastr.error('فشل في رفع الملف. تحقق من السجلات للمزيد من التفاصيل.');
                    }
                };

                xhr.onerror = function() {
                    toastr.error('حدث خطأ أثناء الرفع. يرجى المحاولة مرة أخرى.');
                };

                xhr.send(formData);
            });

            // سكريبت البحث المباشر
            document.getElementById('searchInput').addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const fileCards = document.querySelectorAll('.file-card');
                let visibleCount = 0;

                fileCards.forEach(card => {
                    const fileNameElement = card.querySelector('a');
                    const fileName = fileNameElement ? fileNameElement.textContent.toLowerCase() : '';
                    if (fileName.includes(query)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                const noResultsMessage = document.getElementById('noResultsMessage');
                const noItemsMessage = document.getElementById('noItemsMessage');

                if (query !== '') {
                    noItemsMessage.style.display = 'none';
                    if (visibleCount === 0) {
                        noResultsMessage.style.display = 'block';
                    } else {
                        noResultsMessage.style.display = 'none';
                    }
                } else {
                    noResultsMessage.style.display = 'none';
                    @if (empty($files))
                        noItemsMessage.style.display = 'block';
                    @else
                        noItemsMessage.style.display = 'none';
                    @endif
                }
            });

            // سكريبت SweetAlert للتأكيد على الحذف
            document.querySelectorAll('.delete-button').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'هل أنت متأكد من عملية الحذف؟',
                        text: "لا يمكن التراجع عن هذا الإجراء!",
                        icon: 'warning',
                        showCancelButton: true, // يعرض زر الإلغاء
                        showConfirmButton: true, // يعرض زر التأكيد
                        showDenyButton: false, // لا يعرض زر الرفض
                        buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                        customClass: {
                            popup: 'custom-popup', // تخصيص شكل النافذة
                            title: 'custom-title', // تخصيص شكل العنوان
                            text: 'custom-text', // تخصيص شكل النص
                            confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                            cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                        },
                        confirmButtonText: 'تأكيد',
                        cancelButtonText: 'إلغاء',
                        reverseButtons: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // تحسين تجربة النوافذ المنبثقة بعد الرفع الناجح
            @if (session('success'))
                <
                script >
                    toastr.success("{{ session('success') }}");
        </script>
        @endif

        @if (session('error'))
            <script>
                toastr.error("{{ session('error') }}");
            </script>
        @endif
        </script>
    @endsection
