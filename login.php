<?php
// ── login.php ────────────────────────────────────────────────
require_once 'includes/session.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$old   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $client = $stmt->fetch();

        if ($client && password_verify($password, $client['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['client_id']      = $client['id'];
            $_SESSION['client_name']    = $client['full_name'];
            $_SESSION['client_email']   = $client['email'];
            $_SESSION['client_service'] = $client['service'];

            $redirect = $_GET['redirect'] ?? 'dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — LuxeStay Group</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">

  <div class="auth-visual">
    <div class="auth-visual-grid"></div>
    <a href="index.php" class="auth-visual-logo">
      LuxeStay
      <span>Premium Group</span>
    </a>
    <div class="auth-visual-quote">
      <p>"Every journey begins with a single, deliberate step toward the exceptional."</p>
      <cite>— LuxeStay Ethos</cite>
    </div>
  </div>

  <div class="auth-form-side">
    <h1>Welcome Back</h1>
    <p class="auth-sub">
      New to LuxeStay? <a href="register.php">Create an account</a>
    </p>

    <?php if ($error): ?>
      <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
      <div class="form-success">✓ Account created! Please sign in below.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
      <div class="form-success">✓ Profile updated successfully.</div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
          placeholder="you@example.com"
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          required autofocus autocomplete="email">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-eye-wrap">
          <input type="password" id="password" name="password"
            placeholder="Your password"
            required autocomplete="current-password">
          <button type="button" class="eye-btn" data-target="password" aria-label="Toggle password visibility">
            <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        Sign In →
      </button>

    </form>

    <p style="margin-top:2.5rem;font-size:.78rem;color:var(--muted);text-align:center">
      By signing in you agree to our
      <a href="#" style="color:var(--gold)">Terms of Use</a> and
      <a href="#" style="color:var(--gold)">Privacy Policy</a>.
    </p>
  </div>

</div>

<script src="js/main.js"></script>
</body>
</html>
