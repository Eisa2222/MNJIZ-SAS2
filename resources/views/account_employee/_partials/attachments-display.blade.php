  <ul class="timeline mb-0">
      @if (
          !$employee->resume &&
              !$employee->qualification_certificate &&
              !$employee->contract_attachment &&
              !$employee->id_attachment &&
              !$employee->bank_account_attachment &&
              !$employee->national_address_attachment &&
              (empty($employee->additional_attachments) || count($employee->additional_attachments) === 0))
          <div class="alert alert-primary mt-2">لا يوجد مرفقات</div>
      @else
          <div class="row g-3 small">
              @if ($employee->resume)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->resume) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  السيرة الذاتية
                              </a>
                              <a href="{{ Storage::url($employee->resume) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->qualification_certificate)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->qualification_certificate) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  مرفق شهادة المؤهل
                              </a>
                              <a href="{{ Storage::url($employee->qualification_certificate) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->contract_attachment)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->contract_attachment) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  مرفق عقد العمل
                              </a>
                              <a href="{{ Storage::url($employee->contract_attachment) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->id_attachment)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->id_attachment) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  مرفق الهوية
                              </a>
                              <a href="{{ Storage::url($employee->id_attachment) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->bank_account_attachment)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->bank_account_attachment) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  مرفق الحساب البنكي
                              </a>
                              <a href="{{ Storage::url($employee->bank_account_attachment) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->national_address_attachment)
                  <div class="col-12">
                      <div class="border p-3">
                          <li class="list-group-item d-flex align-items-center border-0 p-0">
                              <i class="ti ti-file ti-lg text-primary me-2"></i>
                              <a href="{{ Storage::url($employee->national_address_attachment) }}" target="_blank"
                                  class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                  مرفق العنوان الوطني
                              </a>
                              <a href="{{ Storage::url($employee->national_address_attachment) }}" download
                                  class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                  <i class="ti ti-download"></i>
                              </a>
                          </li>
                      </div>
                  </div>
              @endif

              @if ($employee->additional_attachments)
                  @foreach ($employee->additional_attachments as $attachment)
                      @if (isset($attachment['file']))
                          <div class="col-12">
                              <div class="border p-3">
                                  <li class="list-group-item d-flex align-items-center border-0 p-0">
                                      <i class="ti ti-file ti-lg text-primary me-2"></i>
                                      <a href="{{ Storage::url($attachment['file']) }}" target="_blank"
                                          class="flex-grow-1 text-decoration-none text-black fw-bold small">
                                          {{ $attachment['name'] }}
                                      </a>
                                      <a href="{{ Storage::url($attachment['file']) }}" download
                                          class="btn btn-outline-primary btn-sm" title="تنزيل المرفق">
                                          <i class="ti ti-download"></i>
                                      </a>
                                  </li>
                              </div>
                          </div>
                      @endif
                  @endforeach
              @endif
          </div>
      @endif
  </ul>
