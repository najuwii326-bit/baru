<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .default-credentials {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-size: 13px;
        }

        .default-credentials h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .default-credentials p {
            color: #666;
            margin: 5px 0;
        }

        .default-credentials strong {
            color: #333;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>🔐 Login Admin</h1>
        <p>Masukkan kredensial Anda untuk melanjutkan</p>
    </div>

    <div id="alert" class="alert"></div>

    <!-- SATU FORM SAJA -->
    <form id="loginForm">
        <div class="form-group">
            <label>Username</label>
            <input type="text" id="username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" required>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
    </form>

    <div class="default-credentials">
        <h3>SELAMAT DATANG ADMIN</h3>
        <!-- <p><strong>Username:</strong> admin</p>
        <p><strong>Password:</strong> admin123</p> -->
    </div>
</div>

<script>
    const defaultCredentials = {
        username: 'admin',
        password: 'polda123admin'
    };

    const loginForm = document.getElementById('loginForm');
    const alertBox = document.getElementById('alert');

    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = `alert alert-${type} show`;
        setTimeout(() => {
            alertBox.className = 'alert';
        }, 3000);
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (username === defaultCredentials.username && password === defaultCredentials.password) {
            showAlert('Login berhasil! Mengalihkan...', 'success');

            setTimeout(() => {
                window.location.href = 'kelola_akun.php';
            }, 1500);
        } else {
            showAlert('Username atau password salah!', 'error');
        }
    });

</script>

</body>
</html>
