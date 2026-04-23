@extends('admin.layout')
@section('title', 'Central Settings')

@section('content')
    <div class="admin-header"><h2>Central Settings</h2></div>

    @foreach ($groups as $group => $rows)
        <div class="card" style="margin-bottom:16px;">
            <h3 style="margin-top:0;text-transform:uppercase;color:#93c5fd;font-size:13px;">{{ $group }}</h3>

            @foreach ($rows as $row)
                <form method="POST" action="{{ route('admin.settings.update', $row->key) }}" style="display:flex;gap:8px;align-items:end;margin-bottom:8px;">
                    @csrf @method('PUT')
                    <div style="flex:0 0 260px;">
                        <label>{{ $row->key }}</label>
                        <small style="color:#64748b;">{{ $row->description ?? '—' }}</small>
                    </div>
                    <div style="flex:1;">
                        <input type="{{ $row->is_encrypted ? 'password' : 'text' }}" name="value" value="{{ $row->is_encrypted ? '••••••••' : $row->value }}">
                    </div>
                    <button type="submit" class="btn">Save</button>
                </form>
            @endforeach
        </div>
    @endforeach
@endsection
