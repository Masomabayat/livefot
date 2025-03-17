<?php
/*
Template Name: LiveFoot API Subscription
*/
get_header(); // Loads your theme header
?>

<!-- Inline CSS from LiveFootTheme.css -->
<style>
    <?php echo file_get_contents( get_template_directory() . '/assets/LiveFootTheme.css' ); ?>
</style>

<div class="livefoot-container">
  <div class="livefoot-header">
    <div class="livefoot-logo">
      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logo-icon"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
      <h1>LiveFoot<span>API</span></h1>
    </div>
    <p class="livefoot-tagline">Professional Football Data API for Developers and Businesses</p>
    <div class="livefoot-version">WordPress Plugin Version 1.0</div>
  </div>

  <div class="livefoot-hero">
    <div class="hero-content">
      <h2>Access Real-Time Football Data</h2>
      <p>Choose from multiple leagues and get instant access to comprehensive and reliable live matches powered by the LiveFot App.</p>
    </div>
  </div>

  <div class="subscription-section">
    <h2>Select Your Yearly Subscription Based on Leagues</h2>
    <p class="subscription-description">
      All plans include the same plugin, the same endpoints, and the following features:<br>
      <strong>2,000 calls per endpoint per hour</strong>, <strong>email support</strong>, and <strong>live matches</strong> fast and reliable powered by LiveFot App.
    </p>

    <div class="subscription-plans">
      <!-- 25 Leagues Plan -->
      <div class="plan-card">
        <div class="plan-header">
          <h3>25 Leagues</h3>
          <div class="plan-price">
            <span class="currency">$</span>
            <span class="amount">250</span>
            <span class="period">/year</span>
          </div>
        </div>
        <div class="plan-leagues">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
          <span>Up to 25 Leagues</span>
        </div>
        <ul class="plan-features">
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Matches, Live Matches, Stats, Standings, Lineup, Live Events
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            2,000 API Calls/Endpoint/Hour
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Email Support
          </li>
        </ul>
      </div>

      <!-- 40 Leagues Plan -->
      <div class="plan-card popular">
        <div class="popular-badge">Most Popular</div>
        <div class="plan-header">
          <h3>40 Leagues</h3>
          <div class="plan-price">
            <span class="currency">$</span>
            <span class="amount">380</span>
            <span class="period">/year</span>
          </div>
        </div>
        <div class="plan-leagues">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
          <span>Up to 40 Leagues</span>
        </div>
        <ul class="plan-features">
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Matches, Live Matches, Stats, Standings, Lineup, Live Events
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            2,000 API Calls/Endpoint/Hour
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Email Support
          </li>
        </ul>
      </div>

      <!-- 55 Leagues Plan -->
      <div class="plan-card">
        <div class="plan-header">
          <h3>55 Leagues</h3>
          <div class="plan-price">
            <span class="currency">$</span>
            <span class="amount">450</span>
            <span class="period">/year</span>
          </div>
        </div>
        <div class="plan-leagues">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
          <span>Up to 55 Leagues</span>
        </div>
        <ul class="plan-features">
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Matches, Live Matches, Stats, Standings, Lineup, Live Events
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            2,000 API Calls/Endpoint/Hour
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feature-icon">
              <path d="M22 4 12 14.01 9 11.01"></path>
              <path d="M9 11.01 4 16.01"></path>
            </svg>
            Email Support
          </li>
        </ul>
      </div>
    </div>

    <!-- Login / Subscription Access -->
    <div class="login-form-container" style="margin-top: 3rem;">
      <div class="login-form-header">
        <h3>Login or Create an Account to Subscribe</h3>
        <p>After logging in, you can select your subscription plan and proceed with payment.</p>
      </div>
      <div class="login-form-placeholder">
        <?php echo do_shortcode('[fotlive_login]'); ?>
      </div>
    </div>

  </div>

  <div class="livefoot-features">
    <h2>Plugin Core Features</h2>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
               viewBox="0 0 24 24" fill="none" stroke="currentColor" 
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
          </svg>
        </div>
        <h3>Relational Database</h3>
        <p>Upon installation, the plugin creates a relational database for matches to ensure optimal performance.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
               viewBox="0 0 24 24" fill="none" stroke="currentColor" 
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
          </svg>
        </div>
        <h3>Automatic/Manual Updates</h3>
        <p>The plugin updates matches automatically, with the option to run manual updates when needed.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
               viewBox="0 0 24 24" fill="none" stroke="currentColor" 
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l-3.5 3.5"></path>
          </svg>
        </div>
        <h3>Local Time Conversion</h3>
        <p>All match times are automatically converted to the user's local timezone.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
               viewBox="0 0 24 24" fill="none" stroke="currentColor" 
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
          </svg>
        </div>
        <h3>Endpoints Included</h3>
        <p>Matches, Live Matches, Match Stats, Standings, Lineup, and Live Events — all accessible via your API key.</p>
      </div>
    </div>
  </div>

  <div class="livefoot-plugin-info">
    <h2>WordPress Plugin Version 1.0</h2>
    <p>Our plugin seamlessly integrates with your WordPress site to provide comprehensive football data:</p>
    
    <div class="plugin-features">
      <div class="plugin-feature">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Fully Responsive Design</span>
      </div>
      <div class="plugin-feature">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Automatic Updates</span>
      </div>
      <div class="plugin-feature">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Relational Database Structure</span>
      </div>
      <div class="plugin-feature">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Powered by LiveFot App</span>
      </div>
    </div>

    <p style="margin-top: 2rem;">
      <strong>Coming Soon in Version 2:</strong> <br>
      Creating prediction tournaments in the LiveFot App for users with a valid subscription.
    </p>
  </div>

  <div class="livefoot-footer">
    <div class="footer-content">
      <div class="footer-logo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
             viewBox="0 0 24 24" fill="none" stroke="currentColor" 
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logo-icon">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
        </svg>
        <h3>LiveFoot<span>API</span></h3>
      </div>
      <p>© <?php echo date('Y'); ?> LiveFoot API. All rights reserved.</p>
      <p class="footer-powered">Powered by LiveFot App</p>
    </div>
  </div>
</div>

<!-- Inline JavaScript from livefoot-wordpress.js -->
<script>
    // Since we've removed the modal and "Select Plan" buttons, 
    // the below script is minimized to basic theme initialization.
    (function() {
      function initLiveFootTheme() {
        const container = document.querySelector('.livefoot-container');
        if (!container) return;
        // Additional theme scripts can go here if needed
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLiveFootTheme);
      } else {
        initLiveFootTheme();
      }
    })();
</script>

<?php
get_footer(); // Loads your theme footer
?>
