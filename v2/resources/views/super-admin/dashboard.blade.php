@extends('super-admin.layout')
@section('title', 'لوحة التحكم')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900 mb-6">نظرة عامة</h1>

    <!-- Stats grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach ([
            ['المستأجرون',         $stats['tenants_total'],     'bg-sky-50 text-sky-700'],
            ['نشطون',              $stats['tenants_active'],    'bg-emerald-50 text-emerald-700'],
            ['موقوفون',            $stats['tenants_suspended'], 'bg-red-50 text-red-700'],
            ['اشتراكات نشطة',      $stats['subs_active'],       'bg-indigo-50 text-indigo-700'],
            ['تجريبية',           $stats['subs_trialing'],     'bg-amber-50 text-amber-700'],
            ['تنتهي خلال 7 أيام', $stats['subs_expiring_soon'], 'bg-orange-50 text-orange-700'],
            ['إيراد الشهر',        number_format($stats['revenue_month']).' SAR',  'bg-emerald-50 text-emerald-700'],
            ['إيراد إجمالي',       number_format($stats['revenue_total']).' SAR',  'bg-sky-50 text-sky-700'],
        ] as [$label, $value, $cls])
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <div class="text-xs text-slate-500 mb-2">{{ $label }}</div>
                <div class="text-2xl font-bold {{ explode(' ',$cls)[1] ?? 'text-slate-900' }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <!-- Revenue chart (simple bar) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-8">
        <h2 class="text-lg font-bold text-slate-900 mb-4">الإيرادات — آخر 12 شهر</h2>
        @php $maxRev = max(array_values($revenueChart) ?: [1]); @endphp
        <div class="flex items-end gap-2 h-40">
            @foreach ($revenueChart as $month => $rev)
                <div class="flex-1 flex flex-col items-center" title="{{ $month }}: {{ number_format($rev) }} SAR">
                    <div class="w-full bg-sky-500 rounded-t-lg hover:bg-sky-600 transition" style="height: {{ $maxRev > 0 ? ($rev / $maxRev * 100) : 0 }}%; min-height: 2px;"></div>
                    <div class="text-[10px] text-slate-500 mt-1">{{ substr($month, 5) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent tenants -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-900">أحدث المستأجرين</h2>
            <a href="#" class="text-sm text-sky-600 hover:underline">عرض الكل ←</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="text-right p-3 text-slate-600 font-semibold">الشركة</th>
                    <th class="text-right p-3 text-slate-600 font-semibold">المالك</th>
                    <th class="text-right p-3 text-slate-600 font-semibold">الحالة</th>
                    <th class="text-right p-3 text-slate-600 font-semibold">انضم</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentTenants as $t)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="p-3 font-semibold">{{ $t->company_name }}</td>
                        <td class="p-3 text-slate-600">{{ $t->owner_name }} <span class="text-xs">({{ $t->owner_email }})</span></td>
                        <td class="p-3">
                            <span @class([
                                'inline-block px-2 py-1 rounded text-xs font-semibold',
                                'bg-emerald-100 text-emerald-700' => $t->status === 'active',
                                'bg-red-100 text-red-700' => $t->status === 'suspended',
                                'bg-slate-100 text-slate-700' => $t->status === 'pending',
                            ])>{{ $t->status }}</span>
                        </td>
                        <td class="p-3 text-slate-500 text-xs">{{ $t->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-slate-500">لا يوجد مستأجرون بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
