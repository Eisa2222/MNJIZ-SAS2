{{-- <!-- resources/views/judicial_affairs/lawsuits/notes/edit.blade.php -->

@extends('judicial_affairs.lawsuits.layout_next')

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('styles')
    <style>
        /* تحسين مظهر النموذج */
        .edit-note-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .edit-note-form h5 {
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('sections')
<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <!-- زر العودة إلى صفحة الملاحظات -->
        <div class="d-flex justify-content-start mb-4 p-5">
                <i class="bi bi-arrow-left me-2"></i> العودة إلى الملاحظات
        </div>

        <!-- نموذج تعديل الملاحظة -->
        <div class="card-body px-5 pb-5">
            <h5 class="mb-4">تعديل الملاحظة</h5>
            <form action="{{ route('lawsuits.notes.update', [$lawsuit->id, $note->id]) }}" method="POST" class="edit-note-form">
                @csrf
                @method('PUT')

                <!-- حقل عنوان الملاحظة -->
                <div class="mb-3">
                    <label for="title" class="form-label">عنوان الملاحظة</label>
                    <input
                        type="text"
                        class="form-control @error('title') is-invalid @enderror"
                        id="title"
                        name="title"
                        value="{{ old('title', $note->title) }}"
                        required
                        maxlength="255"
                        placeholder="أدخل عنوان الملاحظة"
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل نص الملاحظة -->
                <div class="mb-3">
                    <label for="text" class="form-label">نص الملاحظة</label>
                    <textarea
                        class="form-control @error('text') is-invalid @enderror"
                        id="text"
                        name="text"
                        rows="5"
                        required
                        placeholder="أدخل نص الملاحظة"
                    >{{ old('text', $note->text) }}</textarea>
                    @error('text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- حقل نوع الملاحظة -->
                <div class="mb-3">
                    <label for="type" class="form-label">نوع الملاحظة</label>
                    <select
                        class="form-select @error('type') is-invalid @enderror"
                        id="type"
                        name="type"
                        required
                    >
                        <option value="private" {{ old('type', $note->type) == 'private' ? 'selected' : '' }}>خاصة</option>
                        <option value="requires_manager_reply" {{ old('type', $note->type) == 'requires_manager_reply' ? 'selected' : '' }}>تحتاج رد المدير</option>
                        <option value="public" {{ old('type', $note->type) == 'public' ? 'selected' : '' }}>عامة</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- زر تحديث الملاحظة -->
                <button type="submit" class="btn btn-primary">تحديث الملاحظة</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // يمكنك إضافة جافاسكريبت إضافي هنا إذا لزم الأمر
        });
    </script>
@endsection --}}
