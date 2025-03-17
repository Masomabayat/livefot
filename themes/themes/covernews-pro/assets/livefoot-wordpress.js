// This file contains the code to integrate the LiveFoot theme with WordPress
// You can include this file in your WordPress theme or plugin

(function() {
  // Function to initialize the LiveFoot theme
  function initLiveFootTheme() {
    // Check if the container exists
    const container = document.querySelector('.livefoot-container');
    if (!container) return;
    
    // Find the login form shortcode placeholder
    const loginPlaceholder = document.querySelector('.login-form-placeholder');
    if (!loginPlaceholder) return;
    
    // The shortcode content will be automatically rendered by WordPress
    // This script handles any additional JavaScript functionality
    
    // Handle plan selection
    const planButtons = document.querySelectorAll('.select-plan-btn');
    planButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove selected class from all plans
        document.querySelectorAll('.plan-card').forEach(card => {
          card.classList.remove('selected');
        });
        
        // Add selected class to the parent card
        this.closest('.plan-card').classList.add('selected');
        
        // Show login section
        const loginSection = document.querySelector('.login-section');
        if (loginSection) {
          loginSection.style.display = 'flex';
        }
        
        // Get plan name for display
        const planName = this.closest('.plan-card').querySelector('h3').textContent;
        const selectedPlanInfo = document.querySelector('.selected-plan-info h3');
        if (selectedPlanInfo) {
          selectedPlanInfo.textContent = `You selected: ${planName}`;
        }
        
        // Store the selected plan in a hidden field or localStorage for later use
        // This can be used by your WordPress plugin to process the subscription
        localStorage.setItem('selectedPlan', planName);
        
        // If you have a hidden field in your form
        const planField = document.querySelector('input[name="selected_plan"]');
        if (planField) {
          planField.value = planName;
        }
      });
    });
    
    // Handle close modal button
    const closeModalButton = document.querySelector('.close-modal');
    if (closeModalButton) {
      closeModalButton.addEventListener('click', function() {
        const loginSection = document.querySelector('.login-section');
        if (loginSection) {
          loginSection.style.display = 'none';
        }
      });
    }
    
    // Handle overlay click to close modal
    const loginOverlay = document.querySelector('.login-overlay');
    if (loginOverlay) {
      loginOverlay.addEventListener('click', function() {
        const loginSection = document.querySelector('.login-section');
        if (loginSection) {
          loginSection.style.display = 'none';
        }
      });
    }
  }
  
  // Initialize when DOM is fully loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLiveFootTheme);
  } else {
    initLiveFootTheme();
  }
})();