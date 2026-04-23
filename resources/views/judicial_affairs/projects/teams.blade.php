@extends('judicial_affairs/projects/layout')

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('sections')

    <div class=" ">
        <div class="col-12 col-lg-12">
            <div class="card mb-6">
                <div class="card-header align-items-center  border-bottom mb-5">
                    <div class="card-title mb-0">
                        <h5 class="card-action-title mb-0 text-primary fw-bold ">
                            <i class="ti ti-users-group  ti-lg text-body me-3 text-primary fw-bold"></i> فريق المشروع
                        </h5>
                    </div>
                </div>

                <div class="card-body">
                    @if ($project->teamMembers->isNotEmpty())
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>م</th>
                                    <th>اسم الموظف</th>
                                    <th>المسمى الوظيفي</th>
                                    <th> تاريخ الاضافة للفريق</th>
                                    <th>التواصل</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->teamMembers as $index => $member)
                                    <tr>
                                        <!-- رقم العضو -->
                                        <td>{{ $index + 1 }}</td>

                                        <!-- صورة واسم الموظف في عمود واحد -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $member->profile_picture ? Storage::url($member->profile_picture) : asset('assets/img/branding/Alburhan-Logo.png') }}"
                                                    alt="صورة الموظف" width="40" height="40"
                                                    class="rounded-circle me-3">
                                                <a href="{{ route('account.employee.profile', $member->id) }}"
                                                    class="text-decoration-none text-primary">
                                                    {{ $member->employee->name }}
                                                </a>
                                            </div>
                                        </td>

                                        <!-- الدور الخاص بالمستخدم -->
                                        <td class="">
                                            @if ($member->user && $member->user->roles->isNotEmpty())
                                                {{ $member->user->roles->pluck('name')->join(', ') }}
                                            @else
                                                غير محدد
                                            @endif
                                        </td>

                                        <td class="text-nowrap">
                                            {{ Alkoumi\LaravelHijriDate\Hijri::Date(
                                                'Y-m-d',
                                                Carbon\Carbon::parse($member->pivot->created_at)->format('Y-m-d'),
                                            ) }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-around">
                                                <!-- واتساب -->
                                                <a title="التواصل مع الموظف عبر واتساب" class="mx-1"
                                                    href="https://wa.me/{{ $member->mobile }}" target="_blank">
                                                    <i class="fab fa-whatsapp fa-lg"></i>
                                                </a>

                                                <!-- رقم الهاتف -->
                                                <a title="التواتصل مع الموظف عبر رقم الهاتف" class="mx-1"
                                                    href="tel:{{ $member->mobile }}">
                                                    <i class="fas fa-phone fa-lg"></i>
                                                </a>

                                                <!-- البريد الإلكتروني -->
                                                <a title="التواتصل مع الموظف عبر  البريد الالكتروني" class="mx-1"
                                                    href="mailto:{{ $member->work_email }}">
                                                    <i class="fas fa-envelope fa-lg"></i>
                                                </a>

                                                <!-- زر الدردشة -->
                                                <a title="التواتصل مع الموظف عبر الدردشة" class="mx-1"
                                                    href="{{ route('chat.index') }}">
                                                    <i class="fas fa-comment-dots fa-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>لا يوجد أعضاء فريق مضافين لهذا المشروع.</p>
                    @endif
                    <!-- نهاية جدول أعضاء فريق المشروع -->
                </div>

            </div>
        </div>

    </div>

@endsection
