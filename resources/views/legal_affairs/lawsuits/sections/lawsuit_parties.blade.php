<!-- resources/views/judicial_affairs/lawsuits/sections/lawsuit_parties.blade.php -->


<div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="card card-action mb-6">
        <div class="card-header align-items-center bg-white border-bottom mb-5">
            <h5 class="card-action-title mb-0 text-primary fw-bold">
                <i class="ti ti-users ti-lg me-1_5 text-body me-3 text-primary fw-bold"></i> أطراف الدعوى
            </h5>
        </div>
        <div class="card-body pt-4">
            <div class="row">
                <!-- عمود المدعين -->
                <div class="col-12 col-md-6">
                    <p class="mb-4 text-primary fw-bold ">المدعين:</p>
                    @if ($lawsuit->allPlaintiffs->isNotEmpty())
                        @foreach ($lawsuit->allPlaintiffs as $plaintiff)
                            <div class="card shadow-sm bg-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">
                                            <i class="ti ti-user-circle me-2"></i>{{ $plaintiff->name }}
                                        </span>
                                        @if ($plaintiff->plaintiff_type === 'App\Models\OperationsCenter\Customer')
                                            <i class='ti ti-rosette-discount-check-filled text-success'
                                                title='عميل'></i>
                                        @elseif($plaintiff->plaintiff_type === 'App\Models\LegalAffair\Opponent')
                                            <div>
                                                <i class='ti ti-rosette-discount-check-filled text-danger'
                                                    title='خصم'></i>
                                                @if ($plaintiff->type == 'company')
                                                    <i class="ti ti-building-bank"></i>
                                                @else
                                                    <i class="ti ti-user"></i>
                                                @endif
                                            </div>
                                        @endif
                                    </h5>
                                    <hr>
                                    @if ($plaintiff->email)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-mail me-2"></i>
                                            {{ $plaintiff->email }}
                                        </p>
                                    @endif
                                    @if ($plaintiff->phone)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-phone me-2"></i>
                                            {{ $plaintiff->phone }}
                                        </p>
                                    @endif
                                    @if ($plaintiff->contact_number)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-phone me-2"></i>
                                            {{ $plaintiff->contact_number }}
                                        </p>
                                    @endif
                                    {{-- @if ($plaintiff->region)
                                            <p class="card-text mb-2">
                                                <i class="ti ti-map-pin me-2"></i><strong>العنوان:</strong>
                                                {{ $plaintiff->region->name }}
                                            </p>
                                        @endif --}}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="">لا يوجد مدعين.</p>
                    @endif
                </div>

                <!-- عمود المدعى عليهم -->
                <div class="col-12 col-md-6">
                    <p class="mb-4 text-primary fw-bold">المدعى عليهم:</p>
                    @if ($lawsuit->allDefendants->isNotEmpty())
                        @foreach ($lawsuit->allDefendants as $defendant)
                            <div class="card shadow-sm bg-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">
                                            <i class="ti ti-user-circle me-2"></i>{{ $defendant->name }}
                                        </span>
                                        @if ($defendant->defendant_type === 'App\Models\OperationsCenter\Customer')
                                            <i class='ti ti-rosette-discount-check-filled text-success'
                                                title='عميل'></i>
                                        @elseif($defendant->defendant_type === 'App\Models\LegalAffair\Opponent')
                                            <div>
                                                <i class='ti ti-rosette-discount-check-filled text-danger'
                                                    title='خصم'></i>
                                                @if ($defendant->type == 'company')
                                                    <i class="ti ti-building-bank"></i>
                                                @else
                                                    <i class="ti ti-user"></i>
                                                @endif

                                                {{-- <span
                                                        class="badge bg-secondary">{{ $defendant->type == 'company' ? 'مؤسسة' : 'فرد' }}</span> --}}
                                            </div>
                                        @endif
                                    </h5>
                                    <hr>
                                    @if ($defendant->email)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-mail me-2"></i>
                                            {{ $defendant->email }}
                                        </p>
                                    @endif
                                    @if ($defendant->phone)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-phone me-2"></i>
                                            {{ $defendant->phone }}
                                        </p>
                                    @endif
                                    @if ($defendant->contact_number)
                                        <p class="card-text mb-2">
                                            <i class="ti ti-phone me-2"></i>
                                            {{ $defendant->contact_number }}
                                        </p>
                                    @endif
                                    {{-- @if ($defendant->region)
                                            <p class="card-text mb-2">
                                                <i class="ti ti-map-pin me-2"></i><strong>العنوان:</strong>
                                                {{ $defendant->region->name }}
                                            </p>
                                        @endif --}}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>لا يوجد مدعى عليهم.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{--
    <script>
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    </script> --}}
