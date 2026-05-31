// ── js/main.js ───────────────────────────────────────────────
'use strict';

/* ── Navbar scroll effect ──────────────────────────────────── */
const navbar = document.querySelector('.navbar');
if (navbar) {
  const onScroll = () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
}

/* ── Mobile hamburger ──────────────────────────────────────── */
const hamburger = document.querySelector('.hamburger');
const navLinks  = document.querySelector('.nav-links');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    const spans = hamburger.querySelectorAll('span');
    if (navLinks.classList.contains('open')) {
      spans[0].style.transform = 'rotate(45deg) translate(4px, 5px)';
      spans[1].style.opacity   = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(4px, -5px)';
    } else {
      spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
    }
  });
  // Close on link click
  navLinks.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => navLinks.classList.remove('open'))
  );
}

/* ── Scroll reveal ─────────────────────────────────────────── */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
      revealObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

/* ── Counter animation ─────────────────────────────────────── */
function animateCounter(el, target, duration = 1600) {
  const start = performance.now();
  const update = (now) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.round(ease * target).toLocaleString();
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const target = parseInt(e.target.dataset.count, 10);
      if (!isNaN(target)) animateCounter(e.target, target);
      counterObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

/* ── Smooth scroll for anchor links ───────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

/* ── Show / Hide password eye buttons ─────────────────────── */
document.querySelectorAll('.eye-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const targetId = btn.dataset.target;
    const input    = document.getElementById(targetId);
    if (!input) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    const eyeOn  = btn.querySelector('.eye-icon');
    const eyeOff = btn.querySelector('.eye-off-icon');
    if (eyeOn)  eyeOn.style.display  = isHidden ? 'none'  : '';
    if (eyeOff) eyeOff.style.display = isHidden ? ''      : 'none';
  });
});

/* ── Password strength (also works for new_password field) ── */
const pwdInputAlt = document.getElementById('new_password');
const pwdMeterEl  = document.getElementById('pwd-meter');
const activePwd   = pwdInputAlt || document.getElementById('password');
if (activePwd && pwdMeterEl) {
  activePwd.addEventListener('input', () => {
    const v = activePwd.value;
    let score = 0;
    if (v.length >= 8)           score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[0-9]/.test(v))         score++;
    if (/[^A-Za-z0-9]/.test(v))  score++;
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#e57373', '#ffa726', '#66bb6a', '#42a5f5'];
    pwdMeterEl.textContent = labels[score] || '';
    pwdMeterEl.style.color = colors[score] || '';
  });
}

/* ── Flash message auto-dismiss ──────────────────────────── */
const flash = document.querySelector('.form-success, .form-error');
if (flash) {
  setTimeout(() => {
    flash.style.transition = 'opacity .6s';
    flash.style.opacity = '0';
    setTimeout(() => flash.remove(), 700);
  }, 5000);
}
