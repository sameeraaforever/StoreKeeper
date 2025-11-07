<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <style>
    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }


  </style>
</head>
<body>

  <div class="login-container">
    <h2>Welcome to StoreKeeper 👋</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
      <div class="input-group">
        <input type="email" id="email" name="email" placeholder="Email address" required />
      </div>
      <div class="input-group">
        <input type="password" id="password" name="password" placeholder="Password" required />
      </div>
      
      <button type="submit" class="btn-login">Login</button>
      <div class="options">
        <p>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </p>

      </div>
    </form>
  </div>

</body>
</html>
