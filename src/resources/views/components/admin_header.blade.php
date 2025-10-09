<header class="header">
    <div class="header__logo">
        <img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>
    @if( !in_array(Route::currentRouteName(), ['register', 'login', 'admin.login', 'verification.notice']))
    <nav class="header__nav">
        <ul>
            <li><a href="/admin/attendances">勤怠一覧</a></li>
            <li><a href="/admin/users">スタッフ一覧</a></li>
            <li><a href="/admin/requests">申請一覧</a></li>
            @if(Auth::check())
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button type="submit" class="header__logout">ログアウト</button>
                </form>
            @endif
            </li>
        </ul>
    </nav>
    @endif
</header>