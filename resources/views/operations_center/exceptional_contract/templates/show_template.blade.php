<p>حرر هذا النموذج بتاريخ: {{ $current_date->format('Y-m-d') }}</p>
<p>بناءً على اتفاق الشركاء تم قبول مشروع: <strong>{{ $project_name }}</strong> مع العميل<strong>
        {{ $customer_name }}&nbsp; </strong>استثناء من تحرير عقد مكتوب، وذلك للأسباب التالية:</p>
<p>{!! $reasons !!}</p>
<p>نطاق العمل:<br>{!! $scope_of_work !!}</p>
<p>وذلك مقابل: {!! $equivalent !!}</p>
<p style="margin-bottom: 50px"></p>
<p class="MsoNormal" dir="RTL" style="text-align: center; direction: rtl; unicode-bidi: embed;" align="center">
    <strong><span lang="AR-SA">توقيع الشركاء</span></strong><strong><span dir="LTR">:</span></strong>
</p>
<br>

<div style="display: flex; flex-wrap: wrap; justify-content: space-around; padding-bottom: 50px;">
    @foreach ($approvals as $approval)
        @if ($approval->status === 'approved' && $approval->approver && $approval->approver->signature)
            <div style="margin: 0 20px; text-align: center;">
                {{-- صورة التوقيع --}}
                @php
                    $signaturePath = storage_path('app/public/' . $approval->approver->signature);
                @endphp

                @if (file_exists($signaturePath))
                    <img src="{{ $signaturePath }}" alt="توقيع {{ $approval->approver->name }}"
                        style="height: 80px; object-fit: contain; max-width: 150px;">
                @endif

                {{-- اسم صاحب التوقيع --}}
                <p style="margin-top: 10px; font-weight: bold; margin-bottom: 0;">
                    {{ $approval->approver->raw_name ?? $approval->approver->name }}
                </p>
            </div>
        @endif
    @endforeach

    {{-- في حال عدم وجود أي توقيع معتمد --}}
    @if ($approvals->where('status', 'approved')->where('approver.signature', '!=', null)->isEmpty())
        <p style="color: #666; text-align: center;">لا توجد تواقيع معتمدة بعد.</p>
    @endif
</div>
