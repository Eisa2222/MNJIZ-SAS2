@extends('admin.layout')
@section('title', __('landing.admin.faqs.title'))

@section('content')
    <div class="admin-header">
        <div>
            <h2>{{ __('landing.admin.faqs.title') }}</h2>
            <p style="color:#94a3b8;font-size:13px;margin:4px 0 0;">{{ __('landing.admin.faqs.subtitle') }}</p>
        </div>
        <a class="btn" href="{{ route("{$prefix}.landing-faqs.create") }}">+ {{ __('landing.admin.faqs.create') }}</a>
    </div>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <p style="color:#64748b;font-size:13px;margin-bottom:12px;">↕ {{ __('landing.admin.common.drag_hint') }}</p>

    <div class="card" style="padding:0;overflow:hidden;">
        <table id="faqs-table">
            <thead>
                <tr>
                    <th style="width:40px;">↕</th>
                    <th>#</th>
                    <th>{{ __('landing.admin.faqs.fields.question') }}</th>
                    <th>{{ __('landing.admin.faqs.fields.is_active') }}</th>
                    <th>{{ __('landing.admin.faqs.fields.sort_order') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="faqs-sortable">
            @forelse ($faqs as $f)
                <tr data-id="{{ $f->id }}">
                    <td style="cursor:grab;color:#64748b;text-align:center;">≡</td>
                    <td>{{ $f->id }}</td>
                    <td>
                        <strong>{{ $f->question }}</strong><br>
                        <small style="color:#94a3b8;">{{ \Illuminate\Support\Str::limit($f->answer, 100) }}</small>
                    </td>
                    <td>
                        <span style="padding:3px 8px;border-radius:4px;font-size:12px;background:{{ $f->is_active ? '#065f46' : '#991b1b' }};">
                            {{ $f->is_active ? __('landing.admin.common.active') : __('landing.admin.common.inactive') }}
                        </span>
                    </td>
                    <td>{{ $f->sort_order }}</td>
                    <td style="text-align:end;white-space:nowrap;">
                        <a class="btn" href="{{ route("{$prefix}.landing-faqs.edit", $f) }}">{{ __('landing.admin.common.edit') }}</a>
                        <form method="POST" action="{{ route("{$prefix}.landing-faqs.destroy", $f) }}" style="display:inline;"
                              onsubmit="return confirm('{{ __('landing.admin.faqs.confirm_delete') }}');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" type="submit">{{ __('landing.admin.common.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#64748b;padding:24px;">{{ __('landing.admin.faqs.empty') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $faqs->links() }}</div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        (function () {
            var el = document.getElementById('faqs-sortable');
            if (! el || typeof Sortable === 'undefined') return;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            Sortable.create(el, {
                handle: 'td',
                animation: 150,
                onEnd: function () {
                    var order = Array.prototype.map.call(el.querySelectorAll('tr[data-id]'), function (tr) {
                        return tr.getAttribute('data-id');
                    });
                    fetch('{{ route("{$prefix}.landing-faqs.sort") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify({ order: order }),
                    }).then(function (r) { return r.json(); })
                      .then(function (j) {
                          if (j.ok) {
                              var f = document.querySelector('.flash');
                              if (! f) {
                                  f = document.createElement('div');
                                  f.className = 'flash';
                                  document.querySelector('.admin-main').insertBefore(f, document.querySelector('.admin-header').nextSibling);
                              }
                              f.textContent = j.message;
                          }
                      });
                }
            });
        })();
    </script>
@endsection
