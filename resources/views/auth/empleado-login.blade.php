<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Login Empleado</title>
</head>

<body>
    <h1>Iniciar Sesión - Empleados</h1>

    @if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email:</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus />
        <br />
        <label>Contraseña:</label>
        <input type="password" name="password" required />
        <br />
        <label>
            <input type="checkbox" name="remember" /> Recordarme
        </label>
        <br />
        <button type="submit">Ingresar</button>
    </form>
</body>

</html>