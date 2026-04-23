<div class="tab-pane fade" id="customers_tab">
    <style>
        .small.text-muted {
            display: none !important;
        }
    </style>
    <div class="row g-3">
        @forelse ($customer_relations as $customer)
            <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">

                        <div>
                            <img src="{{ asset('assets/img/branding/Alburhan-Logo.png') }}" alt="Customer Image"
                                class="rounded-circle w-px-100" />
                        </div>
                        <h5 class="mb-0 card-title small fw-bold" title="{{ $customer->name }}">
                            <a href="{{ route('operations-center.customers.show', $customer->id) }}">
                                {{ \Illuminate\Support\Str::limit($customer->name, 18, '...') }}
                            </a>

                        </h5>
                        <span class="small">{{ $customer->country->name ?? '' }}</span>

                        <div class="d-flex align-items-center justify-content-center pt-2">

                            @if ($customer->contact_number)
                                <a href="tel:{{ $customer->contact_number }}" class="btn btn-label-secondary btn-icon"
                                    title="الاتصال بالعميل">
                                    <i class="ti ti-phone ti-md"></i>
                                </a>
                            @endif

                            @if ($customer->email)
                                <a href="mailto:{{ $customer->email }}" class="btn btn-label-secondary btn-icon mx-2"
                                    title="إرسال بريد إلكتروني">
                                    <i class="ti ti-mail ti-md"></i>
                                </a>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center">
                    <small>لا يوجد عملاء حاليا</small>
                </div>
            </div>
        @endforelse

    </div>
    @if ($customer_relations->hasPages())
        <div class="card mt-5">
            <div class="d-flex justify-content-center mt-4">
                {{ $customer_relations->appends(['tab' => 'customers_tab'])->links() }}
            </div>
        </div>
    @endif

</div>
