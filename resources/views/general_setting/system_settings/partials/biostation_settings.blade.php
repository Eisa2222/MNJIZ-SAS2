<div class="tab-pane fade" id="biometric">
    <div class="card mb-6">
        <div class="card-header d-flex align-items-center py-4">
            <i class="ti ti-scan text-warning me-2"></i>
            <h6 class="card-title mb-0">إعدادات جهاز البصمة</h6>
        </div>
        <div class="border-1 border-light border-dashed mb-2"></div>
        <div class="card-body">
            <form id="biometric" method="POST" action="{{ route('general-settings.system-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" id="active_tab" value="#biometric">
                <div class="row">

                    <!-- الحقول السابقة -->
                    <div class="mb-4 col-md-6">
                        <label for="biostation_api_key" class="form-label">مفتاح API</label>
                        <input type="text" name="biostation_api_key" id="biostation_api_key" class="form-control"
                            value="{{ old('biostation_api_key', $settings->biostation_api_key ?? '') }}">
                        @error('biostation_api_key')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_api_url" class="form-label">عنوان API</label>
                        <input type="url" name="biostation_api_url" id="biostation_api_url" class="form-control"
                            value="{{ old('biostation_api_url', $settings->biostation_api_url ?? 'https://api.biostation.com') }}">
                        @error('biostation_api_url')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_device_ip" class="form-label">عنوان IP للجهاز</label>
                        <input type="text" name="biostation_device_ip" id="biostation_device_ip" class="form-control"
                            value="{{ old('biostation_device_ip', $settings->biostation_device_ip ?? '') }}">
                        @error('biostation_device_ip')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_device_port" class="form-label">منفذ الجهاز</label>
                        <input type="number" name="biostation_device_port" id="biostation_device_port"
                            class="form-control"
                            value="{{ old('biostation_device_port', $settings->biostation_device_port ?? 80) }}">
                        @error('biostation_device_port')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_timezone" class="form-label">المنطقة الزمنية</label>
                        <input type="text" name="biostation_timezone" id="biostation_timezone" class="form-control"
                            value="{{ old('biostation_timezone', $settings->biostation_timezone ?? 'UTC') }}">
                        @error('biostation_timezone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_device_name" class="form-label">اسم الجهاز</label>
                        <input type="text" name="biostation_device_name" id="biostation_device_name"
                            class="form-control"
                            value="{{ old('biostation_device_name', $settings->biostation_device_name ?? '') }}">
                        @error('biostation_device_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="biostation_sync_interval" class="form-label">فترة التزامن (دقائق)</label>
                        <span title="فترة التزامن بين الجهاز والنظام (مثلاً كل 5 دقائق)" style="color: #d5a047;">
                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                        </span>
                        <input type="number" name="biostation_sync_interval" id="biostation_sync_interval"
                            class="form-control"
                            value="{{ old('biostation_sync_interval', $settings->biostation_sync_interval ?? 5) }}"
                            min="1" max="60" step="1">
                        @error('biostation_sync_interval')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- إضافة Switch لتفعيل الحضور اليدوي -->
                    <div class="mb-4 col-md-6">
                        <label for="manual_attendance_enabled" class="form-label">تفعيل الحضور اليدوي</label>
                        <span title="سيتم تفعيل الحضور اليدوي لكل مستخدم على حسابه الخاص" style="color: #d5a047;">
                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                        </span>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="manual_attendance_enabled" name="manual_attendance_enabled"
                                {{ old('manual_attendance_enabled', $settings->manual_attendance_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label" for="manual_attendance_enabled">
                                تفعيل الحضور اليدوي
                            </label>
                        </div>
                        @error('manual_attendance_enabled')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- إضافة حقول الموقع الجغرافي للشركة -->
                    <div class="mb-4 col-md-6">
                        <label for="company_latitude" class="form-label">خط العرض (Latitude) لموقع الشركة</label>
                        <span title="يستخدم الموقع للتحقق مما إذا كان الموظف يسجل الدخول من مقر الشركة أم لا"
                            style="color: #d5a047;">
                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                        </span>
                        <!-- استخدام القيمة من قاعدة البيانات -->
                        <input type="text" name="company_latitude" id="company_latitude" class="form-control"
                            value="{{ old('company_latitude', $settings->company_latitude ?? '21.435257') }}">
                        @error('company_latitude')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4 col-md-6">
                        <label for="company_longitude" class="form-label">خط الطول (Longitude) لموقع الشركة</label>
                        <!-- استخدام القيمة من قاعدة البيانات -->
                        <input type="text" name="company_longitude" id="company_longitude" class="form-control"
                            value="{{ old('company_longitude', $settings->company_longitude ?? '40.499364') }}">
                        @error('company_longitude')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- إضافة خريطة لاختيار الموقع -->
                    <div class="mb-4 col-12">
                        <label class="form-label">اختر موقع الشركة على الخريطة</label>
                        <div id="map" style="height: 400px; width: 100%; border: 1px solid #ccc;"></div>
                    </div>


                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary me-3">حفظ التغييرات</button>
                </div>
            </form>
            <!-- سكريبت جافاسكريبت لتحديد الموقع بدقة -->
            <script>
                function initMap() {
                    // الحصول على عناصر حقول الإدخال
                    var latField = document.getElementById('company_latitude');
                    var lngField = document.getElementById('company_longitude');

                    // التحقق من وجود قيم في الحقول
                    if (latField.value && lngField.value &&
                        !isNaN(parseFloat(latField.value)) &&
                        !isNaN(parseFloat(lngField.value))) {
                        // إذا كانت القيم موجودة وصحيحة، استخدمها لتحديد الموقع
                        var companyLocation = {
                            lat: parseFloat(latField.value),
                            lng: parseFloat(lngField.value)
                        };

                        // إنشاء الخريطة باستخدام الموقع المخزن
                        initializeMap(companyLocation);
                    } else {
                        // إذا كانت الحقول فارغة أو غير صالحة، استخدم Geolocation لتحديد موقع المستخدم
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    var userLocation = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude
                                    };

                                    // تحديث الحقول بقيم الموقع الحالي
                                    latField.value = parseFloat(userLocation.lat.toFixed(6));
                                    lngField.value = parseFloat(userLocation.lng.toFixed(6));

                                    // إنشاء الخريطة باستخدام موقع المستخدم
                                    initializeMap(userLocation);
                                },
                                function(error) {
                                    console.error("حدث خطأ أثناء الحصول على الموقع:", error);
                                }, {
                                    enableHighAccuracy: true,
                                    timeout: 10000,
                                    maximumAge: 0
                                }
                            );
                        } else {
                            console.error("المتصفح لا يدعم Geolocation.");
                        }
                    }
                }

                // دالة لإنشاء الخريطة والـ Marker وتحديث الحقول عند التغيير
                function initializeMap(location) {
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 15,
                        center: location
                    });

                    var marker = new google.maps.Marker({
                        position: location,
                        map: map,
                        draggable: true
                    });

                    function updatePosition(latLng) {
                        document.getElementById('company_latitude').value = parseFloat(latLng.lat().toFixed(6));
                        document.getElementById('company_longitude').value = parseFloat(latLng.lng().toFixed(6));
                    }

                    // عند سحب الـ Marker
                    marker.addListener('dragend', function(event) {
                        updatePosition(event.latLng);
                    });

                    // عند النقر على الخريطة
                    map.addListener('click', function(event) {
                        marker.setPosition(event.latLng);
                        updatePosition(event.latLng);
                    });
                }
            </script>




            <!-- تضمين Google Maps JavaScript API مع مفتاح API الخاص بك -->
            <script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap"></script>

        </div>
    </div>
</div>
