<?php
// ── index.php ────────────────────────────────────────────────
require_once 'includes/session.php';
$client = getCurrentClient();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LuxeStay Group — Hotel · Rent Car · School · Company</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══════ NAVBAR ══════ -->
<nav class="navbar">
  <a class="nav-logo" href="index.php">Luxe<span>Stay</span></a>
  <ul class="nav-links">
    <li><a href="#services">Services</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="#contact">Contact</a></li>
    <?php if ($client): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="logout.php" class="nav-cta">Sign Out</a></li>
    <?php else: ?>
      <li><a href="login.php">Sign In</a></li>
      <li><a href="register.php" class="nav-cta">Register</a></li>
    <?php endif; ?>
  </ul>
  <div class="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- ══════ HERO ══════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <p class="hero-eyebrow">Est. 2008 · Premium Services Group</p>
    <h1 class="hero-title">
      Excellence<br>
      in Every<br>
      <em>Experience</em>
    </h1>
    <p class="hero-sub">
      LuxeStay Group unites world-class hospitality, premium car rental,
      elite education, and corporate solutions under one distinguished name.
    </p>
    <div class="hero-actions">
      <a href="register.php" class="btn btn-primary">Begin Your Journey</a>
      <a href="#services" class="btn btn-outline">Explore Services</a>
    </div>
  </div>
  <div class="hero-scroll">
    <div class="hero-scroll-line"></div>
    Scroll
  </div>
</section>

<!-- ══════ SERVICES ══════ -->
<section id="services">
  <p class="section-label reveal">What We Offer</p>
  <h2 class="section-title reveal">Four Pillars of <em style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--gold)">Distinction</em></h2>
  <div class="section-line reveal"></div>

  <div class="services-grid">

    <div class="service-card reveal">
      <span class="service-num">01</span>
      <span class="service-icon">🏨</span>
      <h3>Hotel & Resorts</h3>
      <p>Curated luxury stays across premier destinations. From intimate boutique hotels to grand resorts — every night a masterclass in comfort.</p>
    </div>

    <div class="service-card reveal">
      <span class="service-num">02</span>
      <span class="service-icon">🚗</span>
      <h3>Premium Car Rental</h3>
      <p>A meticulously maintained fleet spanning executive sedans to adventure SUVs. Drive in style, arrive in confidence, return inspired.</p>
    </div>

    <div class="service-card reveal">
      <span class="service-num">03</span>
      <span class="service-icon">🎓</span>
      <h3>LuxeStay Academy</h3>
      <p>Hospitality and business education programmes designed for the next generation of industry leaders. Theory meets real-world excellence.</p>
    </div>

    <div class="service-card reveal">
      <span class="service-num">04</span>
      <span class="service-icon">🏢</span>
      <h3>Corporate Solutions</h3>
      <p>End-to-end business travel management, event venues, and executive memberships tailored to discerning organisations worldwide.</p>
    </div>

  </div>
</section>

<!-- ══════ ABOUT ══════ -->
<section id="about">
  <div class="about-layout">
    <div>
      <p class="section-label reveal">Our Story</p>
      <h2 class="section-title reveal">Built on<br><em style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--gold)">Passion &amp; Precision</em></h2>
      <div class="section-line reveal"></div>
      <p class="reveal" style="color:var(--muted);font-size:.9rem;line-height:1.9;max-width:480px">
        Founded in 2008, LuxeStay Group was born from a singular belief:
        that every client interaction should be extraordinary. Today, we operate
        across 28 countries, guided by the same founding principle — uncompromising
        quality, personal attention, and a relentless pursuit of the remarkable.
      </p>

      <div class="about-stats">
        <div class="stat reveal">
          <div class="stat-num" data-count="28">0</div>
          <div class="stat-label">Countries</div>
        </div>
        <div class="stat reveal">
          <div class="stat-num" data-count="14000">0</div>
          <div class="stat-label">Happy Clients</div>
        </div>
        <div class="stat reveal">
          <div class="stat-num" data-count="16">0</div>
          <div class="stat-label">Years of Excellence</div>
        </div>
        <div class="stat reveal">
          <div class="stat-num" data-count="97">0</div>
          <div class="stat-label">% Satisfaction</div>
        </div>
      </div>
    </div>

    <div class="about-visual reveal">
      <div class="about-shape">
        <div class="about-shape-inner">
          <span class="about-monogram">L</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════ CTA ══════ -->
<div class="cta-banner" id="contact">
  <p class="section-label reveal">Join Us</p>
  <h2 class="section-title reveal">Your Story Begins <em style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--gold)">Here</em></h2>
  <p class="reveal">Create your complimentary client account and unlock a world of privileges across all LuxeStay services.</p>
  <div class="reveal">
    <?php if ($client): ?>
      <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    <?php else: ?>
      <a href="register.php" class="btn btn-primary">Create Account</a>
      &nbsp;&nbsp;
      <a href="login.php" class="btn btn-outline">Sign In</a>
    <?php endif; ?>
  </div>
</div>

<!-- ══════ FOOTER ══════ -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-logo">LuxeStay</div>
      <p class="footer-desc">A global group committed to elevating every encounter — from accommodation and transport to education and corporate excellence.</p>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <ul>
        <li><a href="#services">Hotel & Resorts</a></li>
        <li><a href="#services">Car Rental</a></li>
        <li><a href="#services">Academy</a></li>
        <li><a href="#services">Corporate</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#about">About Us</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Press</a></li>
        <li><a href="#">Sustainability</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Account</h4>
      <ul>
        <?php if ($client): ?>
          <li><a href="dashboard.php">My Dashboard</a></li>
          <li><a href="includes/logout.php">Sign Out</a></li>
        <?php else: ?>
          <li><a href="register.php">Register</a></li>
          <li><a href="login.php">Sign In</a></li>
        <?php endif; ?>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Use</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> LuxeStay Group. All rights reserved.</span>
    <span>Developed by <a href="https://www.github.com/ibrahimf90" target="_blank">Ibrahim Fayyad</a>.</span>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
