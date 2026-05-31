<?php
// ── dashboard.php ────────────────────────────────────────────
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/countries.php';
requireLogin();

$client  = getCurrentClient();
$welcome = isset($_GET['welcome']);
$errors  = [];
$success = '';

// ── Handle edit form POST ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name']   ?? '');
    $email       = trim($_POST['email']       ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $service     = trim($_POST['service']     ?? '');
    $new_password       = $_POST['new_password']        ?? '';
    $confirm_password   = $_POST['confirm_password']    ?? '';
    $current_password   = $_POST['current_password']    ?? '';

    // Validation
    if (empty($full_name) || strlen($full_name) < 3)
        $errors[] = 'Full name must be at least 3 characters.';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email address.';

    global $COUNTRIES;
    if (!empty($nationality) && !in_array($nationality, $COUNTRIES, true))
        $errors[] = 'Please select a valid nationality.';

    $allowed = ['hotel','rent_car','school','company'];
    if (!in_array($service, $allowed, true))
        $errors[] = 'Please select a valid service.';

    // Check email uniqueness (excluding current user)
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM clients WHERE email = ? AND id != ?');
        $stmt->execute([$email, $client['id']]);
        if ($stmt->fetch())
            $errors[] = 'That email is already used by another account.';
    }

    // Password change (optional – only if fields filled)
    $new_hash = null;
    if ($new_password !== '' || $confirm_password !== '') {
        // Verify current password first
        $stmt = $pdo->prepare('SELECT password_hash FROM clients WHERE id = ?');
        $stmt->execute([$client['id']]);
        $row = $stmt->fetch();
        if (!password_verify($current_password, $row['password_hash']))
            $errors[] = 'Current password is incorrect.';
        elseif (strlen($new_password) < 8)
            $errors[] = 'New password must be at least 8 characters.';
        elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password))
            $errors[] = 'New password needs at least one uppercase letter and one number.';
        elseif ($new_password !== $confirm_password)
            $errors[] = 'New passwords do not match.';
        else
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Commit changes
    if (empty($errors)) {
        if ($new_hash) {
            $stmt = $pdo->prepare('
                UPDATE clients
                SET full_name=?, email=?, phone=?, nationality=?, service=?, password_hash=?
                WHERE id=?
            ');
            $stmt->execute([$full_name, $email, $phone ?: null, $nationality ?: null, $service, $new_hash, $client['id']]);
        } else {
            $stmt = $pdo->prepare('
                UPDATE clients
                SET full_name=?, email=?, phone=?, nationality=?, service=?
                WHERE id=?
            ');
            $stmt->execute([$full_name, $email, $phone ?: null, $nationality ?: null, $service, $client['id']]);
        }

        // Refresh session
        $_SESSION['client_name']    = $full_name;
        $_SESSION['client_email']   = $email;
        $_SESSION['client_service'] = $service;

        $success = 'Your profile has been updated successfully.';
    }
}

// Fetch fresh record
$stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
$stmt->execute([$client['id']]);
$record = $stmt->fetch();

$serviceLabels = [
    'hotel'    => '🏨 Hotel & Resorts',
    'rent_car' => '🚗 Premium Car Rental',
    'school'   => '🎓 LuxeStay Academy',
    'company'  => '🏢 Corporate Solutions',
];
$serviceLabel = $serviceLabels[$record['service']] ?? ucfirst($record['service']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — LuxeStay Group</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar scrolled">
  <a class="nav-logo" href="index.php">Luxe<span>Stay</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#services">Services</a></li>
    <li><a href="#edit-section">Edit Profile</a></li>
    <li><a href="logout.php" class="nav-cta">Sign Out</a></li>
  </ul>
  <div class="hamburger" aria-label="Menu"><span></span><span></span><span></span></div>
</nav>

<div class="dashboard">

  <!-- Header -->
  <div class="dashboard-header">
    <?php if ($welcome): ?>
      <p class="section-label" style="margin-bottom:.5rem">Welcome to the family</p>
    <?php endif; ?>
    <h1>Hello, <span><?= htmlspecialchars($record['full_name']) ?></span></h1>
    <p style="color:var(--muted);margin-top:.5rem;font-size:.88rem">
      Member since <?= date('F j, Y', strtotime($record['created_at'])) ?>
    </p>
  </div>

  <div class="dashboard-body">

    <!-- Stat cards -->
    <div class="dash-cards">
      <div class="dash-card">
        <h4>Client ID</h4>
        <div class="dash-val">#<?= str_pad($record['id'], 5, '0', STR_PAD_LEFT) ?></div>
      </div>
      <div class="dash-card">
        <h4>Active Service</h4>
        <div style="margin-top:.3rem"><?= $serviceLabel ?></div>
      </div>
      <div class="dash-card">
        <h4>Nationality</h4>
        <div style="margin-top:.3rem;font-size:.9rem"><?= htmlspecialchars($record['nationality'] ?: '—') ?></div>
      </div>
      <div class="dash-card">
        <h4>Status</h4>
        <div style="margin-top:.5rem"><span class="badge">Active Member</span></div>
      </div>
    </div>

    <!-- ══ PROFILE VIEW ══ -->
    <div class="dash-panel">
      <div class="dash-panel-header">
        <h3>Profile Details</h3>
        <button class="btn btn-outline btn-sm" id="btn-edit-toggle">✏ Edit Profile</button>
      </div>

      <!-- Read-only view -->
      <div id="profile-view">
        <table class="profile-table">
          <?php
          $rows = [
              'Full Name'   => $record['full_name'],
              'Email'       => $record['email'],
              'Phone'       => $record['phone'] ?: '—',
              'Nationality' => $record['nationality'] ?: '—',
              'Service'     => $serviceLabel,
              'Joined'      => date('d M Y, H:i', strtotime($record['created_at'])),
              'Last Update' => date('d M Y, H:i', strtotime($record['updated_at'])),
          ];
          foreach ($rows as $label => $value): ?>
            <tr>
              <td class="tbl-label"><?= $label ?></td>
              <td class="tbl-value"><?= htmlspecialchars($value) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

      <!-- ══ EDIT FORM ══ -->
      <div id="profile-edit" style="display:none" id="edit-section">

        <?php if ($errors): ?>
          <div class="form-error" style="margin-bottom:1.5rem">
            <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="form-success" style="margin-bottom:1.5rem"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="dashboard.php" novalidate>

          <div class="edit-section-title">Personal Information</div>

          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Full Name</label>
              <input type="text" id="full_name" name="full_name"
                value="<?= htmlspecialchars($record['full_name']) ?>" required>
            </div>
            <div class="form-group">
              <label for="phone">Phone</label>
              <input type="tel" id="phone" name="phone"
                value="<?= htmlspecialchars($record['phone'] ?? '') ?>"
                placeholder="+1 555 000 0000">
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
              value="<?= htmlspecialchars($record['email']) ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="nationality">Nationality</label>
              <select id="nationality" name="nationality">
                <?php renderCountryOptions($record['nationality'] ?? ''); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="service">Service</label>
              <select id="service" name="service" required>
                <option value="hotel"    <?= $record['service'] === 'hotel'    ? 'selected' : '' ?>>🏨 Hotel & Resorts</option>
                <option value="rent_car" <?= $record['service'] === 'rent_car' ? 'selected' : '' ?>>🚗 Car Rental</option>
                <option value="school"   <?= $record['service'] === 'school'   ? 'selected' : '' ?>>🎓 Academy</option>
                <option value="company"  <?= $record['service'] === 'company'  ? 'selected' : '' ?>>🏢 Corporate</option>
              </select>
            </div>
          </div>

          <div class="edit-section-title" style="margin-top:2rem">Change Password <span style="font-size:.72rem;color:var(--muted);font-weight:300;letter-spacing:.05em;text-transform:none">(leave blank to keep current)</span></div>

          <div class="form-group">
            <label for="current_password">Current Password</label>
            <div class="input-eye-wrap">
              <input type="password" id="current_password" name="current_password"
                placeholder="Enter your current password"
                autocomplete="current-password">
              <button type="button" class="eye-btn" data-target="current_password" aria-label="Show password">
                <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="new_password">
                New Password
                <span id="pwd-meter" style="float:right;font-size:.7rem;letter-spacing:.1em"></span>
              </label>
              <div class="input-eye-wrap">
                <input type="password" id="new_password" name="new_password"
                  placeholder="Min 8 chars, A, 1"
                  autocomplete="new-password">
                <button type="button" class="eye-btn" data-target="new_password" aria-label="Show password">
                  <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <div class="input-eye-wrap">
                <input type="password" id="confirm_password" name="confirm_password"
                  placeholder="Repeat new password"
                  autocomplete="new-password">
                <button type="button" class="eye-btn" data-target="confirm_password" aria-label="Show password">
                  <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="eye-off-icon" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:1rem;margin-top:.5rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">Save Changes →</button>
            <button type="button" class="btn btn-outline" id="btn-cancel-edit">Cancel</button>
          </div>

        </form>
      </div><!-- /profile-edit -->
    </div><!-- /dash-panel -->

    <div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
      <a href="index.php" class="btn btn-outline">← Back to Home</a>
      <a href="logout.php" class="btn btn-primary">Sign Out</a>
    </div>

  </div>
</div>

<script src="js/main.js"></script>
<script>
// Toggle edit panel
const btnEdit   = document.getElementById('btn-edit-toggle');
const btnCancel = document.getElementById('btn-cancel-edit');
const view      = document.getElementById('profile-view');
const editPanel = document.getElementById('profile-edit');

function showEdit() {
  view.style.display      = 'none';
  editPanel.style.display = 'block';
  btnEdit.textContent     = '✕ Close';
  editPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function showView() {
  view.style.display      = 'block';
  editPanel.style.display = 'none';
  btnEdit.textContent     = '✏ Edit Profile';
}

btnEdit.addEventListener('click', () => {
  editPanel.style.display === 'none' ? showEdit() : showView();
});
btnCancel.addEventListener('click', showView);

// Auto-open edit if there were errors or success on POST
<?php if ($errors || $success): ?>
  showEdit();
<?php endif; ?>
</script>
</body>
</html>
