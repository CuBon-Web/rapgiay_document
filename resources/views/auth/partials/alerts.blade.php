@if (session('success'))
    <div class="auth-alert auth-alert--success" role="alert">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="auth-alert auth-alert--error" role="alert">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="auth-alert auth-alert--error" role="alert">
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
