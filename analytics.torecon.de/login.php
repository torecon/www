<?php
session_start();
require_once dirname(__FILE__) . '/config.php';

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASSWORD) {
        $_SESSION['torecon_analytics_auth'] = true;
        session_write_close();
        header('Location: https://analytics.torecon.de/index.php');
        exit;
    }
    $error = true;
}

if (!empty($_SESSION['torecon_analytics_auth'])) {
    session_write_close();
    header('Location: https://analytics.torecon.de/index.php');
    exit;
}

$username_val = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – torecon Analytics</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
</head>
<body>
<script>window.TORECON_ROOT = 'https://www.torecon.de/';</script>

<div class="login-page">
  <div class="login-card">
    <span class="login-logo">tore<span>con</span></span>
    <h2>Analytics</h2>
    <p class="login-sub">Bitte melden Sie sich an, um fortzufahren.</p>

    <?php if ($error) { ?>
    <div class="login-error" style="display:block;">
      Benutzername oder Passwort falsch. Bitte erneut versuchen.
    </div>
    <?php } ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label class="form-label" for="username">Benutzername</label>
        <input class="form-control" type="text" id="username" name="username"
          required placeholder="Benutzername" autocomplete="username"
          value="<?php echo $username_val; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Passwort</label>
        <input class="form-control" type="password" id="password" name="password"
          required placeholder="Ihr Passwort" autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary">Anmelden</button>
    </form>

    <div style="margin-top:28px;">
      <a href="https://www.torecon.de/" style="font-size:14px;color:var(--text-secondary);">← Zurück zur Website</a>
    </div>
  </div>
</div>

<script src="https://www.torecon.de/js/i18n.js"></script>
</body>
</html>
