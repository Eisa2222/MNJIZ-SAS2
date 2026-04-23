<div class="tab-pane fade" id="custody_tab">
    <style>
        .small.text-muted {
            display: none !important;
        }
    </style>
    <div class="row g-3">
        @forelse ($custodies as $custody)
            <div class="col-xl-2 col-lg-6 col-md-6">
                <a href="{{ route('account.electronic-services.custody-requests.show', $custody->id) }}">
                    <div class="card h-100">
                        <div class="card-body text-center">

                            <span class="small">{{ Str::limit($custody->item->name ?? '', 10, '...') }}</span>

                            <span
                                class="mt-5 badge  bg-{{ $custody->request_type->color() }}">{{ $custody->request_type->label() }}</span>

                            <span
                                class="mt-5 badge  bg-{{ $custody->status->color() }}">{{ $custody->status->label() }}</span>

                        </div>
                    </div>
                </a>

            </div>
        @empty
            <div class="card">
                <div class="card-body text-center">
                    <small>لا توجد عهد حاليا</small>
                </div>
            </div>
        @endforelse

    </div>
    {{-- @if ($customer_relations->hasPages())
        <div class="card mt-5">
            <div class="d-flex justify-content-center mt-4">
                {{ $customer_relations->appends(['tab' => 'customers_tab'])->links() }}
            </div>
        </div>
    @endif --}}

</div>
