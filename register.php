<?php
include 'database.php';
$msg = $_GET['msg'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        // Check if username exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->bind_param('ss', $username, $hash);
            if ($stmt->execute()) {
                header('Location: login.php?msg=' . urlencode('Account created! Please log in.'));
                exit;
            } else {
                $msg = 'Registration failed.';
            }
        }
        $stmt->close();
    } else {
        $msg = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #D8BC97;
            --panel: rgba(216,188,151,0.6);
            --accent: #A17851;
            --accent-dark: #6F5A47;
            --text: #5B4937;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Viga', sans-serif; background: var(--bg); color: var(--text); }
        body { display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .watermark {
            font-family: 'Viga';
            font-size: 80px;
            color: rgba(139,115,85,0.15);
            position: absolute;
            left: 50%;
            top: 32px;
            transform: translateX(-50%);
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
            letter-spacing: 5px;
            font-weight: 700;
            width: 520px;
            text-align: center;
        }
        .login-container {
            max-width: 460px;
            width: 90%;
            background: var(--panel);
            backdrop-filter: blur(8px);
            padding: 50px 40px 40px 40px;
            border-radius: 28px;
            box-shadow: 0 16px 48px rgba(91,73,55,0.3);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .login-container h2 {
            font-family: 'Seaweed Script', cursive;
            text-align: center;
            margin-bottom: 40px;
            font-size: 54px;
            color: #FDE5B7;
            text-shadow: 0 3px 6px rgba(91,73,55,0.4);
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
        }
        .login-container label {
            display: block;
            font-size: 15px;
            color: var(--text);
            margin-bottom: 8px;
            font-weight: 600;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: 14px;
            background: rgba(139,115,85,0.25);
            color: var(--text);
            font-size: 15px;
            font-family: 'Viga', sans-serif;
            margin-bottom: 20px;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .login-container input::placeholder {
            color: rgba(91,73,55,0.4);
        }
        .login-container input:focus {
            outline: none;
            background: rgba(139,115,85,0.35);
            box-shadow: 0 0 0 3px rgba(111,90,71,0.2);
        }
        .button-row {
            display: flex;
            justify-content: center;
            margin-top: 24px;
            margin-bottom: 16px;
        }
        .login-container button {
            padding: 14px 36px;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            font-family: 'Viga', sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            transition: transform 0.15s, box-shadow 0.15s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .login-container button[type="submit"] {
            background: linear-gradient(180deg, #8B7355, #6F5A47);
            color: #FDF7D7;
        }
        .login-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.28);
        }
        .login-container button:active {
            transform: translateY(0);
        }
        .cancel-row {
            display: flex;
            justify-content: center;
        }
        .login-container .btn-cancel {
            padding: 12px 32px;
            background: linear-gradient(180deg, #6F5A47, #5B4937);
            color: #FDF7D7;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            font-family: 'Viga', sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            transition: transform 0.15s, box-shadow 0.15s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .login-container .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.28);
        }
        .login-container .msg {
            color: #d04b4b;
            text-align: center;
            margin-bottom: 16px;
            padding: 10px;
            background: rgba(208,75,75,0.15);
            border-radius: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="watermark">REGISTER</div>
        <h2>Cafe Rencontre</h2>
        <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <form action="register.php" method="post">
            <label>Create Username</label>
            <input type="text" name="username" required autofocus>
            <label>Create Password</label>
            <input type="password" name="password" required>
            <div class="button-row">
                <button type="submit">Create</button>
            </div>
        </form>
        <div class="cancel-row">
            <button type="button" class="btn-cancel" onclick="window.location.href='login.php'">Cancel</button>
        </div>
    </div>
</body>
</html>
