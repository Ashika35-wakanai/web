<header class="site-header">
  <div class="site-top">
    <!-- Brand on the left (logo + watermark behind) - linked to welcome page -->
    <a href="welcome.php" style="text-decoration:none; display:block;">
      <div class="brand" style="position:relative; width:460px; margin:0 auto; height:70px;">
        <div class="watermark" style="font-family:'Viga';font-size:90px;color:#673709;opacity:0.13;position:absolute;left:50%;top:-36px;transform:translateX(-50%);letter-spacing:7px;width:460px;text-align:center;z-index:0;pointer-events:none;white-space:nowrap;">ORDERS</div>
        <div class="title" style="font-family:'Seaweed Script',cursive;font-size:42px;color:#FDE5B7;text-shadow:0 3px 6px rgba(91,73,55,0.4);position:absolute;left:50%;top:14px;transform:translateX(-50%);z-index:2;width:460px;text-align:center;margin:0;white-space:nowrap;">Cafe Rencontre</div>
      </div>
    </a>

    <!-- Search in the center -->
    <div class="search-box">
      <span class="search-icon" aria-hidden="true">🔍</span>
      <input id="main-search" class="search-input" type="search" placeholder="search for products" onkeypress="if(event.key==='Enter') location.href='index.php?filter='+encodeURIComponent(this.value)">
    </div>

    <!-- Account on the right -->
    <div class="account">
      <div class="date"><?= date('D F j') ?></div>
      <div class="avatar">GA</div>
    </div>
  </div>
</header>
