@component('mail::message')
    # تذكير بالمهمة

    لقد تم تعينك على مهمة جديدة: **{{ $task->task_name }}**

    **الوصف:**
    {{ $task->description }}

    **تاريخ الاستحقاق:**
    {{ $task->due_date }}

    @component('mail::button', ['url' => route('organization-center.tasks.show', $task->id)])
        عرض المهمة
    @endcomponent

    شكراً لاستخدامك تطبيقنا!
@endcomponent
