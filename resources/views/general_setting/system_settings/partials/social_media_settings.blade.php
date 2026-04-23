<div class="tab-pane fade" id="socialMedia">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-social text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات مواقع التواصل </h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="socialMedia" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" value="#socialMedia">

                <div class="row">


                    <div class="col-12 col-md-6 mb-4">
                        <label for="linkedin_client_id" class="form-label">Linkedin Client Id</label>
                        <input type="password" name="linkedin_client_id" id="linkedin_client_id" class="form-control"
                            required placeholder="••••••••" />
                        @error('linkedin_client_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 mb-4">
                        <label for="linkedin_client_secret" class="form-label">Linkedin Client Secret</label>
                        <input type="password" name="linkedin_client_secret" id="linkedin_client_secret" required
                            class="form-control" placeholder="••••••••" />
                        @error('linkedin_client_secret')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 mb-4">
                        <label for="linkedin_access_token" class="form-label">Linkedin Access Token</label>
                        <input type="password" name="linkedin_access_token" id="linkedin_access_token" required
                            class="form-control" placeholder="••••••••" />
                        @error('linkedin_access_token')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>



                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
