<?php
// ── register.php ─────────────────────────────────────────────
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/countries.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $phone            = trim($_POST['phone']            ?? '');
    $nationality      = trim($_POST['nationality']      ?? '');
    $service          = trim($_POST['service']          ?? '');
    $password         = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';

    if (empty($full_name))
        $errors[] = 'Full name is required.';
    elseif (strlen($full_name) < 3)
        $errors[] = 'Full name must be at least 3 characters.';

    if (empty($email))
        $errors[] = 'Email address is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email address.';

    global $COUNTRIES;
    if (!empty($nationality) && !in_array($nationality, $COUNTRIES, true))
        $errors[] = 'Please select a valid nationality.';

    $allowed_services = ['hotel','rent_car','school','company'];
    if (!in_array($service, $allowed_services, true))
        $errors[] = 'Please select a valid service.';

    if (empty($password))
        $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)
        $errors[] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))
        $errors[] = 'Password must contain at least one uppercase letter and one number.';

    if ($password !== $confirm_password)
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM clients WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch())
            $errors[] = 'An account with this email already exists.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare('
            INSERT INTO clients (full_name, email, phone, nationality, password_hash, service)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $full_name,
            $email,
            $phone ?: null,
            $nationality ?: null,
            $hash,
            $service,
        ]);

        $newId = $pdo->lastInsertId();
        $_SESSION['client_id']      = $newId;
        $_SESSION['client_name']    = $full_name;
        $_SESSION['client_email']   = $email;
        $_SESSION['client_service'] = $service;

        header('Location: dashboard.php?welcome=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — LuxeStay Group</title>
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
      <p>"The world is a book, and those who do not explore read only one page."</p>
      <cite>— Saint Augustine</cite>
    </div>
  </div>

  <div class="auth-form-side">
    <h1>Create Account</h1>
    <p class="auth-sub">
      Already a member? <a href="login.php">Sign in here</a>
    </p>

    <?php if ($errors): ?>
      <div class="form-error">
        <?php foreach ($errors as $e): ?>
          <div>• <?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name"
            placeholder="Jean-Pierre Moreau"
            value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
            required autocomplete="name">
        </div>
        <div class="form-group">
          <label for="phone">Phone (optional)</label>
          <input type="tel" id="phone" name="phone"
            placeholder="+1 555 000 0000"
            value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
            autocomplete="tel">
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
          placeholder="you@example.com"
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          required autocomplete="email">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="nationality">Nationality</label>
          <select id="nationality" name="nationality">
            <?php renderCountryOptions($old['nationality'] ?? ''); ?>
          </select>
        </div>
        <div class="form-group">
          <label for="service">Service of Interest</label>
          <select id="service" name="service" required>
            <option value="" disabled <?= empty($old['service']) ? 'selected' : '' ?>>— Select service —</option>
            <option value="hotel"    <?= ($old['service'] ?? '') === 'hotel'    ? 'selected' : '' ?>>🏨 Hotel & Resorts</option>
            <option value="rent_car" <?= ($old['service'] ?? '') === 'rent_car' ? 'selected' : '' ?>>🚗 Car Rental</option>
            <option value="school"   <?= ($old['service'] ?? '') === 'school'   ? 'selected' : '' ?>>🎓 Academy</option>
            <option value="company"  <?= ($old['service'] ?? '') === 'company'  ? 'selected' : '' ?>>🏢 Corporate</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">
            Password
            <span id="pwd-meter" style="float:right;font-size:.7rem;letter-spacing:.1em"></span>
          </label>
          <div class="input-eye-wrap">
            <input type="password" id="password" name="password"
              placeholder="Min 8 chars, A, 1"
              required autocomplete="new-password">
            <button type="button" class="eye-btn" data-target="password" aria-label="Toggle password visibility">
              <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-eye-wrap">
            <input type="password" id="confirm_password" name="confirm_password"
              placeholder="Repeat password"
              required autocomplete="new-password">
            <button type="button" class="eye-btn" data-target="confirm_password" aria-label="Toggle password visibility">
              <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        Create My Account →
      </button>

    </form>
  </div>

</div>

<script src="js/main.js"></script>
</body>
</html>
