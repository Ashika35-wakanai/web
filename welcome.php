<?php
session_start();
?>
<!doctype html>
<html class="welcome-page">
<head>
<meta charset="utf-8">
<title>Welcome to Cafe Rencontre</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Viga&family=Seaweed+Script&family=Tangerine:wght@400;700&family=Teachers:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/design.css">
<style>
/* Welcome page full-screen layout */
html.welcome-page, body.welcome-page {
  height: 100vh;
  width: 100vw;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background: var(--bg);
}

.welcome-page-wrapper {
  position: relative;
  width: 100vw;
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg);
}

/* Header */
.welcome-header {
  position: relative;
  padding: 16px 0 18px 0;
  background: linear-gradient(90deg, var(--bg), #C7AD8A);
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  z-index: 100;
}

.welcome-header-top {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 12px;
  gap: 12px;
  min-height: 70px;
}

.welcome-brand {
  position: relative;
  width: 280px;
  margin: 0;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.welcome-brand .watermark {
  font-family: 'Viga';
  font-size: 60px;
  color: #673709;
  opacity: 0.13;
  position: absolute;
  left: 50%;
  top: -12px;
  transform: translateX(-50%);
  letter-spacing: 5px;
  width: 280px;
  text-align: center;
  z-index: 0;
  pointer-events: none;
  white-space: nowrap;
}

.welcome-brand .title {
  font-family: 'Seaweed Script', cursive;
  font-size: 32px;
  color: #FDE5B7;
  text-shadow: 0 3px 6px rgba(91,73,55,0.4);
  position: relative;
  z-index: 2;
  margin: 0;
}

.welcome-search-box {
  display: none;
}

.welcome-search-input {
  width: 420px;
  background: var(--bg);
  border: 4px solid rgba(109,54,13,0.45);
  border-radius: 14px;
  padding: 10px 18px;
  color: #FDE5B7;
  font-size: 15px;
  font-family: 'Viga', sans-serif;
}

.welcome-account {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-self: end;
}

.welcome-account-date {
  font-size: 13px;
  color: var(--accent-dark);
  margin-right: 6px;
  white-space: nowrap;
}

.welcome-avatar {
  width: 48px;
  height: 48px;
  background: var(--accent);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--panel);
  font-weight: 700;
  font-size: 14px;
}

/* Main content area */
.welcome-main {
  display: flex;
  flex: 1;
  position: relative;
  overflow: hidden;
}

/* Left sidebar nav */
.welcome-sidebar {
  position: fixed;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 90;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 12px 10px;
  background: linear-gradient(180deg, rgba(199,173,138,0.95), rgba(161,120,81,0.9));
  border-radius: 18px;
  box-shadow: 0 12px 24px rgba(0,0,0,0.18);
  align-items: center;
}

.welcome-sidebar a {
  width: 48px;
  height: 48px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: #5f412a;
  font-weight: 700;
  font-size: 11px;
  border-radius: 14px;
  background: rgba(255,255,255,0.08);
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.welcome-sidebar a:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.14);
  background: rgba(255,255,255,0.16);
}

.welcome-sidebar a.active {
  background: rgba(255,255,255,0.24);
  color: #3d291a;
  box-shadow: 0 8px 16px rgba(0,0,0,0.18);
}

/* Hero content area */
.welcome-hero {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 40px;
  margin-left: 0;
  margin-right: 0;
}

.welcome-hero-content {
  position: relative;
  width: 100%;
  max-width: 1000px;
  text-align: center;
}

.welcome-hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.3), rgba(0,0,0,0.2)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23D8BC97" width="1200" height="600"/><circle cx="150" cy="150" r="80" fill="%23C7AD8A" opacity="0.3"/><circle cx="1100" cy="500" r="120" fill="%236F5A47" opacity="0.15"/></svg>');
  background-size: cover;
  background-position: center;
  border-radius: 32px;
  z-index: 0;
}

.welcome-hero-inner {
  position: relative;
  z-index: 1;
  padding: 60px 40px;
}

.welcome-title {
  font-family: 'Seaweed Script', cursive;
  font-size: 96px;
  font-weight: 400;
  line-height: 1;
  color: #F8EED4;
  text-shadow: 0px 4px 7px #1F1B17;
  margin: 0 0 16px 0;
  letter-spacing: 0.5px;
}

.welcome-tagline {
  font-family: 'Tangerine', cursive;
  font-size: 48px;
  font-weight: 400;
  line-height: 1;
  color: #D8BC97;
  text-shadow: 0px 4px 4px #665642;
  margin: 0 0 28px 0;
  letter-spacing: 1px;
}

.welcome-description {
  font-family: 'Teachers', cursive;
  font-size: 26px;
  font-weight: 400;
  line-height: 1.5;
  color: #D8BC97;
  text-shadow: 0px 3px 4px #665642;
  margin: 0 0 32px 0;
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
}

.welcome-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  align-items: center;
}

.welcome-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 32px;
  background: linear-gradient(135deg, #6F5A47, #5a4737);
  color: #A78F72;
  border: none;
  border-radius: 10px;
  font-family: 'Viga', sans-serif;
  font-size: 18px;
  font-weight: 400;
  letter-spacing: -0.02em;
  text-decoration: none;
  text-shadow: 0px 1px 4px rgba(0, 0, 0, 0.25);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 6px 12px rgba(0,0,0,0.18);
}

.welcome-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.25);
}

/* Right cart preview area */
.welcome-cart-preview {
  display: none;
}

.welcome-cart-header {
  font-family: 'Viga', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #3d291a;
  margin-bottom: 12px;
  text-align: center;
  letter-spacing: 0.5px;
}

.welcome-cart-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(61,41,26,0.6);
  font-family: 'Viga', sans-serif;
  font-size: 14px;
  text-align: center;
}

@media (max-width: 1400px) {
  .welcome-hero { margin-right: 350px; }
  .welcome-title { font-size: 72px; }
  .welcome-description { font-size: 22px; }
}

@media (max-width: 1000px) {
  .welcome-hero { margin-left: 80px; margin-right: 280px; }
  .welcome-title { font-size: 56px; }
  .welcome-tagline { font-size: 36px; }
  .welcome-description { font-size: 18px; }
  .welcome-cart-preview { width: 280px; right: 16px; }
}
@media (max-width: 1024px) {
  .welcome-hero {
      margin: 0 !important;
      padding: 20px;
      justify-content: center;
  }

  .welcome-hero-content {
      max-width: 90%;
      margin: 0 auto;
  } 
       
  
  
  
}
</style>
</head>
<body class="welcome-page">
<div class="welcome-page-wrapper">
  <!-- Header -->
  <header class="welcome-header">
    <div class="welcome-header-top">
      <div class="welcome-brand">
        <div class="watermark">WELCOME</div>
        <div class="title">Cafe Rencontre</div>
      </div>
      <div class="welcome-search-box">
        <input class="welcome-search-input" type="search" placeholder="search for products" onkeypress="if(event.key==='Enter') location.href='index.php?filter='+encodeURIComponent(this.value)">
      </div>
      <div class="welcome-account">
        <div class="welcome-account-date"><?= date('D F j') ?></div>
        <div class="welcome-avatar"><?= isset($_SESSION['user_id']) ? 'GA' : 'GU' ?></div>
      </div>
    </div>
  </header>

  <!-- Main content -->
  <div class="welcome-main">
    <!-- Hero content -->
    <div class="welcome-hero">
      <div class="welcome-hero-content">
        <div class="welcome-hero-bg"></div>
        <div class="welcome-hero-inner">
          <h1 class="welcome-title">Cafe Rencontre</h1>
          <div class="welcome-tagline">Meet taste. Meet people. Meet the Expectations</div>
          <p class="welcome-description">Our café is built on simple ideas: meet people, meet flavors, meet experiences. We believe every sip should satisfy, and every visit should feel like a small reunion—with friends, with inspiration, or with yourself.</p>
          <div class="welcome-actions">
            <a href="login.php" class="welcome-btn">START NOW</a>
            <a href="admin/login.php" class="welcome-btn">LOGIN AS ADMIN</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
