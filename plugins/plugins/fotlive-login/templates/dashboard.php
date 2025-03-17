<?php
/**
 * Template Name: FotLive Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['fotlive_token'])) {
    wp_redirect(home_url('/login/'));
    exit;
}

get_header();
?>

<div class="fotlive-dashboard">
    <!-- Navigation -->
    <nav class="fotlive-nav">
        <div class="fotlive-nav-container">
            <div class="fotlive-nav-logo">
                <svg class="fotlive-logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                    <line x1="16" y1="8" x2="2" y2="22"></line>
                    <line x1="17.5" y1="15" x2="9" y2="15"></line>
                </svg>
                <span>FotLive</span>
            </div>
            <div class="fotlive-nav-user">
                <span class="fotlive-username"><?php echo esc_html($_SESSION['fotlive_name'] ?? 'User'); ?></span>
                <button id="fotlive-logout-btn" class="fotlive-btn-link">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="fotlive-dashboard-container">
        <div class="fotlive-dashboard-grid">
            <!-- Sidebar -->
            <div class="fotlive-sidebar">
                <nav class="fotlive-sidebar-nav">
                    <button class="fotlive-nav-item active" data-tab="profile">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Profile
                    </button>
                    <button class="fotlive-nav-item" data-tab="leagues">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Subscribed Leagues
                    </button>
                    <button class="fotlive-nav-item" data-tab="usage">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        API Usage
                    </button>
                    <button class="fotlive-nav-item" data-tab="plans">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        Custom Plans
                    </button>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="fotlive-content">
                <!-- Profile Tab -->
                <div class="fotlive-tab-content active" id="profile-tab">
                    <h2>Profile Information</h2>
                    <div class="fotlive-profile-grid">
                        <div class="fotlive-profile-item">
                            <label>Name</label>
                            <p><?php echo esc_html($_SESSION['fotlive_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="fotlive-profile-item">
                            <label>Email</label>
                            <p><?php echo esc_html($_SESSION['fotlive_email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="fotlive-profile-item">
                            <label>User ID</label>
                            <p><?php echo esc_html($_SESSION['fotlive_user_id'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="fotlive-profile-item">
                            <label>Status</label>
                            <span class="fotlive-status-badge">
                                <?php echo esc_html($_SESSION['fotlive_status'] ?? 'Active'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Leagues Tab -->
                <div class="fotlive-tab-content" id="leagues-tab">
                    <div class="fotlive-header-actions">
                        <h2>Subscribed Leagues</h2>
                        <button class="fotlive-btn-primary">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Add League
                        </button>
                    </div>
                    <div class="fotlive-table-container">
                        <table class="fotlive-table">
                            <thead>
                                <tr>
                                    <th>League Name</th>
                                    <th>Status</th>
                                    <th>Expires At</th>
                                </tr>
                            </thead>
                            <tbody id="leagues-table-body">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- API Usage Tab -->
                <div class="fotlive-tab-content" id="usage-tab">
                    <h2>API Usage</h2>
                    <div class="fotlive-usage-grid" id="api-usage-grid">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- Custom Plans Tab -->
                <div class="fotlive-tab-content" id="plans-tab">
                    <h2>Custom Plans</h2>
                    <div class="fotlive-plans-grid">
                        <div class="fotlive-plan-card">
                            <h3>Need a Custom Plan?</h3>
                            <p>Contact our team to discuss custom plans tailored to your specific needs.</p>
                            <button class="fotlive-btn-primary">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                                Contact Support
                            </button>
                        </div>
                        <div class="fotlive-plan-card">
                            <h3>Raise a Ticket</h3>
                            <p>Having issues or need assistance? Our support team is here to help.</p>
                            <button class="fotlive-btn-primary">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Create Ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>