    {{-- Employee Selection Modal --}}
    <div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="employeeSelectionForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center">
                            تحديد الموظف المعتمد
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق">
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="employeeSelect" class="form-label">
                                اختر الموظف المسؤول عن الاعتماد
                            </label>
                            <select id="employeeSelect" class="form-select select2" name="employee_id" required>
                                <option value="">-- اختر الموظف --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>
                            تأكيد
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            إلغاء
                        </button>
                    </div>    </form>
            </div>
        </div>
    </div>
