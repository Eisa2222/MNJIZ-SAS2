<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>الوصف</th>
                <td>{{ $activity->description }}</td>
            </tr>
            <tr>
                <th>المستخدم</th>
                <td>{{ $activity->causer ? $activity->causer->name : '-' }}</td>
            </tr>
            <tr>
                <th>الموضوع</th>
                <td>
                    @if ($activity->subject)
                        @php
                            $modelName = class_basename($activity->subject_type);
                            $arabicModelName = __('activitylog.models.' . $modelName, [], 'ar') ?: $modelName;
                        @endphp
                        {{ $activity->description }} في {{ $arabicModelName . ' #' . $activity->subject_id }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <th>التاريخ والوقت</th>
                <td>{{ \Carbon\Carbon::parse($activity->created_at)->locale('ar')->diffForHumans() }}</td>
            </tr>
            <tr>
                <th>التغييرات</th>
                <td>
                    @if ($activity->properties->isNotEmpty())
                        @php
                            $newAttributes = $activity->properties->get('attributes') ?? [];
                            $oldAttributes = $activity->properties->get('old') ?? [];
                            $changedAttributes = array_diff_assoc($newAttributes, $oldAttributes);
                            unset($changedAttributes['updated_at']);
                        @endphp

                        @if (!empty($changedAttributes))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>الحقل</th>
                                            <th>القيمة القديمة</th>
                                            <th>القيمة الجديدة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($changedAttributes as $key => $newValue)
                                            <tr>
                                                @php
                                                    $modelName = class_basename($activity->subject_type);
                                                    $arabicFieldName =
                                                        __('activitylog.fields.' . $key, [], 'ar') ?? ucfirst($key);
                                                @endphp
                                                <td>{{ $arabicFieldName }}</td>
                                                <td class="text-danger">
                                                    {{ array_key_exists($key, $oldAttributes) ? $oldAttributes[$key] : '-' }}
                                                </td>
                                                <td class="text-success">{{ $newValue }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            لا توجد تغييرات.
                        @endif
                    @else
                        لا توجد تغييرات.
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
