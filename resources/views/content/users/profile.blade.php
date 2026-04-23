@extends('layouts.layoutMaster')

@section('title', 'عرض المستخدم - الصفحات')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-user-view.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/modal-edit-user.js',
  'resources/assets/js/app-user-view.js',
  'resources/assets/js/app-user-view-account.js',
  'resources/assets/js/pages-profile.js'
])
@endsection

@section('content')
<div class="row">
  <!-- الشريط الجانبي للمستخدم -->
  <div class="col-xl-4 col-lg-5 order-1 order-md-0">
    <!-- بطاقة المستخدم -->
    <div class="card mb-6">
      <div class="card-body pt-12">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <img class="img-fluid rounded mb-4" src="{{ asset('storage/' . $user->image) }}" height="120" width="120" alt="صورة المستخدم" />
            <div class="user-info text-center">
              <h5>{{ $user->name }}</h5>
              <span class="badge bg-label-secondary">{{ $user->role }}</span>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
          <div class="d-flex align-items-center me-5 gap-4">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class='ti ti-checkbox ti-lg'></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">1.23k</h5>
              <span>المهام المكتملة</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-4">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class='ti ti-briefcase ti-lg'></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">568</h5>
              <span>المشاريع المكتملة</span>
            </div>
          </div>
        </div>
        <h5 class="pb-4 border-bottom mb-4">التفاصيل</h5>
        <div class="info-container">
          <ul class="list-unstyled mb-6">
            <li class="mb-2">
              <span class="h6">اسم المستخدم:</span>
              <span>{{ $user->name }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">البريد الإلكتروني:</span>
              <span>{{ $user->email }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">الحالة:</span>
              <span>{{ $user->status == 'active' ? 'نشط' : ($user->status == 'inactive' ? 'غير نشط' : 'معلق') }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">الدور:</span>
              <span>{{ $user->role }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">رقم الاتصال:</span>
              <span>{{ $user->phone }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">اللغات:</span>
              <span>{{ $user->languages ?? 'غير متوفر' }}</span>
            </li>
            <li class="mb-2">
              <span class="h6">البلد:</span>
              <span>{{ $user->country ?? 'غير متوفر' }}</span>
            </li>
          </ul>
          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">تعديل</a>
            <a href="javascript:;" class="btn btn-label-danger suspend-user">تعليق الحساب</a>
          </div>
        </div>
      </div>
    </div>
    <!-- /بطاقة المستخدم -->
    <!-- بطاقة الخطة -->
    <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <span class="badge bg-label-primary">قياسية</span>
          <div class="d-flex justify-content-center">
            <sub class="h5 pricing-currency mb-auto mt-1 text-primary">$</sub>
            <h1 class="mb-0 text-primary">99</h1>
            <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">شهريًا</sub>
          </div>
        </div>
        <ul class="list-unstyled g-2 my-6">
          <li class="mb-2 d-flex align-items-center"><i class="ti ti-circle-filled ti-10px text-secondary me-2"></i><span>10 مستخدمين</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="ti ti-circle-filled ti-10px text-secondary me-2"></i><span>حتى 10 جيجابايت تخزين</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="ti ti-circle-filled ti-10px text-secondary me-2"></i><span>دعم أساسي</span></li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="h6 mb-0">الأيام</span>
          <span class="h6 mb-0">26 من 30 يوم</span>
        </div>
        <div class="progress mb-1 bg-label-primary" style="height: 6px;">
          <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small>تبقى 4 أيام</small>
        <div class="d-grid w-100 mt-6">
          <button class="btn btn-primary" data-bs-target="#upgradePlanModal" data-bs-toggle="modal">ترقية الخطة</button>
        </div>
      </div>
    </div>
    <!-- /بطاقة الخطة -->
  </div>
  <!--/ الشريط الجانبي للمستخدم -->

  <!-- محتوى المستخدم -->
  <div class="col-xl-8 col-lg-7 order-0 order-md-1">
    <!-- علامات التبويب للمستخدم -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2">
        <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="ti ti-user-check ti-sm me-1_5"></i>الحساب</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('app/user/view/security')}}"><i class="ti ti-lock ti-sm me-1_5"></i>الأمان</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('app/user/view/billing')}}"><i class="ti ti-bookmark ti-sm me-1_5"></i>الفواتير والخطط</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('app/user/view/notifications')}}"><i class="ti ti-bell ti-sm me-1_5"></i>الإشعارات</a></li>
        <li class="nav-item"><a class="nav-link" href="{{url('app/user/view/connections')}}"><i class="ti ti-link ti-sm me-1_5"></i>الاتصالات</a></li>
      </ul>
    </div>
    <!--/ علامات التبويب للمستخدم -->

    <!-- جدول المشاريع -->
    <div class="card mb-6">
      <div class="card-datatable table-responsive">
        <table class="datatables-projects table border-top">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>المشروع</th>
              <th>القائد</th>
              <th>الفريق</th>
              <th class="w-px-200">التقدم</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
    <!-- /جدول المشاريع -->

    <!-- جدول النشاطات -->
    <div class="card mb-6">
      <h5 class="card-header">خط زمني لنشاط المستخدم</h5>
      <div class="card-body pt-1">
        <ul class="timeline mb-0">
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-primary"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-3">
                <h6 class="mb-0">تم دفع 12 فاتورة</h6>
                <small class="text-muted">قبل 12 دقيقة</small>
              </div>
              <p class="mb-2">
                تم دفع الفواتير للشركة
              </p>
              <div class="d-flex align-items-center mb-2">
                <div class="badge bg-lighter rounded d-flex align-items-center">
                  <img src="{{asset('assets/img/icons/misc/pdf.png')}}" alt="img" width="15" class="me-2">
                  <span class="h6 mb-0 text-body">invoices.pdf</span>
                </div>
              </div>
            </div>
          </li>
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-success"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-3">
                <h6 class="mb-0">اجتماع مع العميل</h6>
                <small class="text-muted">قبل 45 دقيقة</small>
              </div>
              <p class="mb-2">
                اجتماع المشروع مع جون @10:15 صباحًا
              </p>
              <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex flex-wrap align-items-center mb-50">
                  <div class="avatar avatar-sm me-2">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="صورة" class="rounded-circle" />
                  </div>
                  <div>
                    <p class="mb-0 small fw-medium">ليستر مكارثي (العميل)</p>
                    <small>الرئيس التنفيذي لـ {{ config('variables.creatorName') }}</small>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-info"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-3">
                <h6 class="mb-0">إنشاء مشروع جديد للعميل</h6>
                <small class="text-muted">قبل يومين</small>
              </div>
              <p class="mb-2">
                6 أعضاء فريق في المشروع
              </p>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">
                  <div class="d-flex flex-wrap align-items-center">
                    <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="فيني موستو" class="avatar pull-up">
                        <img class="rounded-circle" src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar" />
                      </li>
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="آلين ريسكي" class="avatar pull-up">
                        <img class="rounded-circle" src="{{ asset('assets/img/avatars/12.png') }}" alt="Avatar" />
                      </li>
                      <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="جولي روسينيول" class="avatar pull-up">
                        <img class="rounded-circle" src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" />
                      </li>
                      <li class="avatar">
                        <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="bottom" title="3 أكثر">+3</span>
                      </li>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <!-- /جدول النشاطات -->
  </div>
  <!--/ محتوى المستخدم -->
</div>

<!-- النوافذ المنبثقة -->
@include('_partials/_modals/modal-edit-user')
@include('_partials/_modals/modal-upgrade-plan')
<!-- /النوافذ المنبثقة -->
@endsection
