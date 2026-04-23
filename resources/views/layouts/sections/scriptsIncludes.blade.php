@php
use Illuminate\Support\Facades\Vite;

$menuCollapsed = ($configData['menuCollapsed'] === 'layout-menu-collapsed') ? json_encode(true) : false;
@endphp
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'])
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  @vite(['resources/assets/vendor/js/template-customizer.js'])
@endif

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  @vite(['resources/assets/js/config.js'])

@if ($configData['hasCustomizer'])
<script type="module">
  window.templateCustomizer = new TemplateCustomizer({
    cssPath: '',
    themesPath: '',
    defaultStyle: "{{$configData['styleOpt']}}",
    defaultShowDropdownOnHover: "{{$configData['showDropdownOnHover']}}", // true/false (for horizontal layout only)
    displayCustomizer: "{{$configData['displayCustomizer']}}",
    lang: '{{ app()->getLocale() }}',
    pathResolver: function(path) {
      var resolvedPaths = {
        // Core stylesheets
        @foreach (['core'] as $name)
          '{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'.scss') }}',
          '{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'-dark.scss') }}',
        @endforeach

        // Themes
        @foreach (['default', 'bordered', 'semi-dark'] as $name)
          'theme-{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'.scss') }}',
          'theme-{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'-dark.scss') }}',
        @endforeach
      }
      return resolvedPaths[path] || path;
    },
    'controls': <?php echo json_encode($configData['customizerControls']); ?>,
  });
</script>



<script>
        function deleteUser(userId) {
            Swal.fire({
                title: '<span style="font-size: 24px;">هل أنت متأكد من حذف هذا السجل ؟</span>',
                html: '<span style="font-size: 20px;">لن تتمكن من التراجع عن هذا!</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span style="font-size: 20px;">نعم، احذف!</span>',
                cancelButtonText: '<span style="font-size: 20px;">إلغاء</span>',
                customClass: {
                    confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                    cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.value) {
                    // Submit the form to delete the user
                    document.getElementById('delete-form-' + userId).submit();

                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-size: 20px;">تم الحذف!</span>',
                        html: '<span style="font-size: 20px;">تم حذف البيانات بنجاح.</span>',
                        customClass: {
                            confirmButton: 'btn btn-success waves-effect waves-light'
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: '<span style="font-size: 20px;">تم الإلغاء</span>',
                        html: '<span style="font-size: 20px;">لم يتم حذفه البيانات.</span>',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-success waves-effect waves-light'
                        }
                    });
                }
            });
        }
    </script>

@endif



