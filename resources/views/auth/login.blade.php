
<!-- resources/views/auth/login.blade.php -->
<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required autofocus>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
    @if(session('error'))
        <div>{{ session('error') }}</div>
    @endif
</form>