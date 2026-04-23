@isset($pageConfigs)
    {!! App\Helpers\Helpers::updatePageConfig($pageConfigs) !!}
@endisset
@php
    $configData = App\Helpers\Helpers::appClasses();
@endphp

@if (!isset($excludeJquery) || !$excludeJquery)
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endif


@isset($configData['layout'])
    @include(
        $configData['layout'] === 'horizontal'
            ? 'layouts.horizontalLayout'
            : ($configData['layout'] === 'blank'
                ? 'layouts.blankLayout'
                : ($configData['layout'] === 'front'
                    ? 'layouts.layoutFront'
                    : 'layouts.contentNavbarLayout')))
@endisset

<script>
    var csrfToken = "{{ csrf_token() }}";
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif

        @if (Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif

        @if (Session::has('info'))
            toastr.info("{{ Session::get('info') }}");
        @endif

        @if (Session::has('warning'))
            toastr.warning("{{ Session::get('warning') }}");
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif
    });
</script>


<div id="bookmarkModal" class="bookmark-modal" style="display: none;">
    <div class="bookmark-modal-content">
        <div class="bookmark-modal-header">
            <h5>المفضلة</h5>
            <span class="bookmark-close">&times;</span>
        </div>
        <div class="bookmark-modal-body">
            <div class="bookmark-icon-container">
                <i class="ti ti-star" style="color: #ff9800; font-size: 24px;"></i>
            </div>
            <div class="bookmark-form">
                <div class="bookmark-form-group">
                    <label for="bookmarkName">الاسم</label>
                    <input type="text" id="bookmarkName" class="bookmark-input" value="">
                </div>
            </div>
        </div>
        <div class="bookmark-modal-footer">
            <button id="bookmarkDone" class="bookmark-btn bookmark-btn-primary">تم</button>
            <button id="bookmarkRemove" class="bookmark-btn bookmark-btn-secondary">إزالة</button>
        </div>
    </div>
</div>

<style>
    .bookmark-modal {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 350px;
        background-color: white;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        border-radius: 8px;
        font-family: inherit;
        direction: rtl;
        border: 1px solid #ddd;
    }

    .bookmark-modal-content {
        display: flex;
        flex-direction: column;
    }

    .bookmark-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
    }

    .bookmark-modal-header h5 {
        margin: 0;
        font-size: 16px;
    }

    .bookmark-close {
        cursor: pointer;
        font-size: 20px;
    }

    .bookmark-modal-body {
        padding: 15px;
        display: flex;
    }

    .bookmark-icon-container {
        margin-left: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
    }

    .bookmark-form {
        flex: 1;
    }

    .bookmark-form-group {
        margin-bottom: 15px;
    }

    .bookmark-form-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .bookmark-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .bookmark-modal-footer {
        padding: 10px 15px;
        display: flex;
        justify-content: flex-end;
        border-top: 1px solid #eee;
    }

    .bookmark-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        margin-right: 10px;
        cursor: pointer;
    }

    .bookmark-btn-primary {
        background-color: #1a73e8;
        color: white;
    }

    .bookmark-btn-secondary {
        background-color: #f1f3f4;
        color: #444;
    }

    .bookmark-modal {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const favoriteIcons = document.querySelectorAll('.favorite-icon');

        favoriteIcons.forEach(function(icon) {
            const pageUrl = icon.getAttribute('data-page-url');

            if (pageUrl) {
                $.ajax({
                    url: '/employees/favorites/check',
                    type: 'GET',
                    data: {
                        page_url: pageUrl
                    },
                    success: function(response) {
                        if (response.is_favorite) {
                            icon.classList.remove('ti-star');
                            icon.classList.add('ti-star-filled');
                            icon.classList.add('text-warning');

                        } else {
                            icon.classList.add('ti-star');
                            icon.classList.remove('ti-star-filled');
                            icon.classList.remove('text-warning');
                        }
                    },
                    error: function(xhr) {
                        console.error('خطأ في التحقق من حالة المفضلة:', xhr.responseText);
                    }
                });
            }
        });
    });

    function toggleFavorite(event, element) {
        event.preventDefault();
        event.stopPropagation();

        const pageName = element.getAttribute('data-page-name');
        const pageUrl = element.getAttribute('data-page-url');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // متغير لتخزين معرف المفضلة
        let currentFavoriteId = null;

        const modal = document.getElementById('bookmarkModal');
        const nameInput = document.getElementById('bookmarkName');
        const doneBtn = document.getElementById('bookmarkDone');
        const removeBtn = document.getElementById('bookmarkRemove');
        const closeBtn = document.querySelector('.bookmark-close');

        const rect = element.getBoundingClientRect();
        const scrollTop = window.scrollY || document.documentElement.scrollTop;

        nameInput.setAttribute('autocomplete', 'off');
        modal.style.position = 'absolute';
        modal.style.top = (rect.bottom + scrollTop + 5) + 'px';
        modal.style.left = (rect.left - 175) + 'px';
        modal.style.right = 'auto';

        $.ajax({
            url: '/employees/favorites/check',
            type: 'GET',
            data: {
                page_name: pageName,
                page_url: pageUrl
            },
            success: function(response) {

                if (response.is_favorite && response.favorite) {
                    currentFavoriteId = response.favorite.id;
                    nameInput.value = response.favorite.page_name;

                    removeBtn.style.display = 'inline-block';
                } else {
                    nameInput.value = pageName;

                    removeBtn.style.display = 'none';
                }

                modal.style.display = 'block';


                if (response.is_favorite) {
                    element.classList.add('text-warning');
                }
            },
            error: function(xhr) {
                console.error('خطأ في طلب التحقق من المفضلة:', xhr.responseText);
                nameInput.value = pageName;
                modal.style.display = 'block';
                removeBtn.style.display = 'none';
            }
        });

        doneBtn.onclick = function() {

            const updatedName = nameInput.value.trim();

            if (!updatedName) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('يجب إدخال اسم للمفضلة');
                }
                nameInput.focus();
                return;
            }

            $.ajax({
                url: '/employees/favorites/toggle',
                type: 'POST',
                data: {
                    page_name: updatedName,
                    page_url: pageUrl,
                    _token: csrfToken,
                    favorite_id: currentFavoriteId
                },
                success: function(response) {
                    if (response.status === 'added' || response.status === 'updated') {
                        if (response.favorite_id) {
                            currentFavoriteId = response.favorite_id;
                        }
                        element.classList.remove('ti-star');
                        element.classList.add('ti-star-filled');
                        element.classList.add('text-warning');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        }
                    }

                    modal.style.display = 'none';
                },
                error: function(xhr) {
                    console.error('خطأ في طلب AJAX:', xhr.responseText);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('حدث خطأ أثناء تحديث المفضلة');
                    }
                    modal.style.display = 'none';
                }
            });
        };

        removeBtn.onclick = function() {

            $.ajax({
                url: '/employees/favorites/toggle',
                type: 'POST',
                data: {
                    page_name: pageName,
                    page_url: pageUrl,
                    _token: csrfToken,
                    action: 'remove',
                    favorite_id: currentFavoriteId
                },
                success: function(response) {
                    element.classList.add('ti-star');
                    element.classList.remove('ti-star-filled');
                    element.classList.remove('text-warning');
                    if (typeof toastr !== 'undefined') {
                        toastr.info(response.message);
                    }

                    modal.style.display = 'none';
                },
                error: function(xhr) {
                    console.error('خطأ في طلب AJAX لإزالة المفضلة:', xhr.responseText);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('حدث خطأ أثناء إزالة المفضلة');
                    }
                    modal.style.display = 'none';
                }
            });
        };

        closeBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            modal.style.display = 'none';
        };

        document.addEventListener('click', handleOutsideClick);

        function handleOutsideClick(e) {
            if (modal.style.display === 'block' && !modal.contains(e.target) && e.target !== element) {
                modal.style.display = 'none';
                document.removeEventListener('click', handleOutsideClick);
            }
        }

        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const favoriteIcons = document.querySelectorAll('.favorite-icon');
        favoriteIcons.forEach(icon => {
            const pageName = icon.getAttribute('data-page-name');
            const pageUrl = icon.getAttribute('data-page-url');

            if (!pageName && !pageUrl) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                'content');
            if (!csrfToken) return;

            $.ajax({
                url: '/employees/favorites/check',
                type: 'GET',
                data: {
                    page_name: pageName,
                    page_url: pageUrl
                },
                success: function(response) {
                    if (response.is_favorite) {
                        icon.classList.remove('ti-star');
                        icon.classList.add('ti-star-filled');
                        icon.classList.add('text-warning');

                    }
                }
            });
        });
    });
</script>


<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
            text: " لا يمكن التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'custom-popup',
                title: 'custom-title',
                text: 'custom-text',
                confirmButton: 'btn btn-success custom-confirm',
                cancelButton: 'btn btn-danger custom-cancel'
            },
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>

<script>
    function confirmDeleteSelected(selectedIds, deleteUrl) {
        if (selectedIds.length === 0) {
            toastr.warning('لم تقم بتحديد أي صفوف.');
            return;
        }

        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
            text: "لا يمكن التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'custom-popup',
                title: 'custom-title',
                text: 'custom-text',
                confirmButton: 'btn btn-success custom-confirm',
                cancelButton: 'btn btn-danger custom-cancel'
            },
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: selectedIds,
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('تم حذف المحدد بنجاح.');
                            location.reload();
                        } else {
                            toastr.error('فشل في حذف المحدد.');
                        }
                    },
                    error: function(xhr) {
                        console.error('خطأ في طلب الحذف:', xhr);
                        toastr.error('حدث خطأ أثناء حذف المحدد.');
                    }
                });
            }
        });
    }
</script>

{{-- مسح المحدد --}}
<script>
    function confirmDeleteSelectedmss(selectedIds, deleteUrl, modelName) {
        if (selectedIds.length === 0) {
            toastr.warning('لم تقم بتحديد أي صفوف.');
            return;
        }

        Swal.fire({
            title: 'هل أنت متأكد من عملية الحذف؟',
            text: "لا يمكن التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'custom-popup',
                title: 'custom-title',
                text: 'custom-text',
                confirmButton: 'btn btn-success custom-confirm',
                cancelButton: 'btn btn-danger custom-cancel'
            },
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: selectedIds,
                        model: modelName
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('تم حذف المحدد بنجاح.');
                            location.reload();
                        } else {
                            if (response.message) {
                                toastr.error(response.message);
                            } else {
                                toastr.error('فشل في حذف المحدد.');
                            }
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء حذف المحدد.');
                    }
                });
            }
        });
    }
</script>

<script>
    const userName = "{{ Auth::user()->name ?? '' }}";
</script>
