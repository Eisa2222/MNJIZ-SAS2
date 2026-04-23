<nav class="layout-navbar shadow-none py-0">
    <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
            <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-4 me-xl-8">
                <a href="#" class="app-brand-link">
                    <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20])</span>

                    <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">
                        {{ App\Helpers\SettingsHelper::get('logo_text') }}
                    </span>
                </a>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <li>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="btn btn-primary">
                        <span class="d-none d-md-block">تسجيل الخروج</span>
                        <span class="tf-icons ti ti-logout scaleX-n1-rtl ms-md-2"></span>
                    </a>

                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                    </form>


                </li>
            </ul>
        </div>
    </div>
</nav>
