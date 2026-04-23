 <!-- شاشة Offcanvas لإضافة مهمة جديدة -->
 <div class="offcanvas offcanvas-end" tabindex="-1" id="addTaskOffcanvas" aria-labelledby="addTaskOffcanvasLabel"
     style="width:600px;">
     <div class="offcanvas-header d-flex justify-content-between" dir="ltr">
         <h5 id="addTaskOffcanvasLabel" class="order-2">إضافة مهمة جديدة</h5>
         <button type="button" class="btn-close m-0 text-reset order-1" data-bs-dismiss="offcanvas"
             aria-label="إغلاق"></button>
     </div>
     <div class="offcanvas-body">
         <form action="{{ route('organization-center.tasks.store') }}" method="POST" id="addTaskForm"
             enctype="multipart/form-data" class="needs-validation" novalidate>
             @csrf
             <input type="hidden" name="task_field" value="lawsuits">
             <input type="hidden" name="lawsuit_id" value="{{ $lawsuit->id }}">
             <div class="row">
                 <div class="col-6 mb-3">
                     <label for="task_name" class="form-label">عنوان المهمة</label>
                     <input type="text" class="form-control" id="task_name" name="task_name" required>
                     <p id="taskNameHint" class="small text-danger"></p>

                     <div class="invalid-feedback">عنوان المهمة مطلوب</div>
                 </div>
                 <div class="col-6 mb-3">
                     <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                     <input type="datetime-local" name="due_date" id="due_date" class="form-control" required>
                     <div class="invalid-feedback">تاريخ الاستحقاق مطلوب</div>
                 </div>
                 <div class="mb-3">
                     <div class="mb-2">
                         <label for="assigned_user_id" class="form-label">مكلف بها</label>
                         @if ($currentEmployee)
                             <button type="button" class="btn btn-outline-secondary btn-sm" style="margin-right: 10px;"
                                 id="assign-myself-btn-create">
                                 لنفسي
                             </button>
                         @else
                             <button type="button" class="btn btn-outline-secondary" id="assign-myself-btn-create"
                                 disabled title="لا يوجد موظف مرتبط بحسابك">
                                 لنفسي
                             </button>
                         @endif
                     </div>
                     <select id="assigned_user_id" name="assigned_user_ids[]" class="form-select select2" required
                         multiple data-placeholder="اختر الموظفين">
                         @foreach ($users as $user)
                             <option value="{{ $user->id }}"
                                 data-image="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}">
                                 {{ $user->employee->name }}
                             </option>
                         @endforeach
                     </select>
                     <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل.</div>
                 </div>
                 <!-- حقل أولوية المهمة -->
                 <div class="mb-3">
                     <label class="form-label">أولوية المهمة</label>
                     <div class="d-flex align-items-start" style="gap: 10px;">
                         <div class="d-flex align-items-center" style="gap: 5px;">
                             <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                 id="priorityHigh" value="high" required>
                             <span style="background-color: red; width: 15px; height: 15px; border-radius: 50%;"></span>
                             <span>مرتفعة</span>
                         </div>
                         <div class="d-flex align-items-center" style="gap: 5px;">
                             <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                 id="priorityMedium" value="medium" required>
                             <span
                                 style="background-color: orange; width: 15px; height: 15px; border-radius: 50%;"></span>
                             <span>متوسطة</span>
                         </div>
                         <div class="d-flex align-items-center" style="gap: 5px;">
                             <input class="form-check-input priority-checkbox" type="radio" name="priority"
                                 id="priorityLow" value="low" required>
                             <span
                                 style="background-color: green; width: 15px; height: 15px; border-radius: 50%;"></span>
                             <span>منخفضة</span>
                         </div>
                     </div>
                 </div>
                 <div class="mb-3">
                     <label for="taskDescription" class="form-label">وصف المهمة</label>
                     <textarea class="form-control" id="taskDescription" name="description" rows="4"></textarea>
                 </div>
                 <div class="col-8 mb-3">
                     <label for="attachment" class="form-label">مرفق</label>
                     <input type="file" class="form-control" id="attachment" name="attachment"
                         accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                 </div>
                 <div class="col-4 mb-3">
                     <label for="hasSteps" class="form-label">المهمة تحتوي على خطوات</label>
                     <div class="form-check form-switch">
                         <input class="form-check-input" type="checkbox" id="hasSteps" name="hasSteps">
                         <label class="form-check-label mx-2" for="hasSteps">اضافة خطوات</label>
                     </div>
                 </div>
                 <!-- حاوية الخطوات لنموذج إضافة مهمة -->
                 <div id="stepsContainer" style="display: none;">
                     <div class="step-block mb-3 border p-3" data-index="0">
                         <div class="step-header mb-2">
                             <strong class="step-number">الخطوة 1</strong>
                         </div>
                         <div class="row">
                             <div class="col-9">
                                 <label class="form-label">اسم الخطوة</label>
                                 <input type="text" name="step_name[]" class="form-control"
                                     placeholder="اسم الخطوة">
                                 <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                             </div>
                             <div class="col-3">
                                 <label class="form-label">تحتاج لاعتماد؟</label>
                                 <span
                                     title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض وعند الرفض سيتم ارجاع المهمة لك مرة اخرى لمراجعتها"
                                     style="color: var(--primary-color);">
                                     <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                 </span>
                                 <div class="form-check">
                                     <input type="checkbox" name="needs_approval[0]" class="form-check-input"
                                         value="1">
                                     <label class="form-check-label">نعم</label>
                                 </div>
                             </div>
                             <div class="col-12">
                                 <label class="form-label mt-2">المكلفين</label>
                                 <select name="step_assigned_user_ids[0][]" class="form-select select2" multiple
                                     data-placeholder="اختر الموظفين">
                                     @foreach ($users as $user)
                                         <option value="{{ $user->id }}"
                                             data-image="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}">
                                             {{ $user->employee->name }}
                                         </option>
                                     @endforeach
                                 </select>
                                 <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل.</div>
                             </div>
                         </div>
                         <div class="mt-2 text-end">
                             <button type="button" class="btn btn-danger btn-sm remove-step-btn">حذف الخطوة</button>
                         </div>
                     </div>
                     <button type="button" class="btn btn-secondary btn-sm" id="addStepButton">إضافة خطوة
                         أخرى</button>
                 </div>
                 <div class="d-flex justify-content-end" style="margin-top: 100px">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="offcanvas">إلغاء</button>
                     <button type="submit" class="btn btn-primary">إضافة المهمة</button>
                 </div>
             </div>
         </form>
     </div>
 </div>
