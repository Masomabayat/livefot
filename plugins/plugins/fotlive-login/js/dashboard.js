jQuery(document).ready(function($) {

    // ─── UTILITY FUNCTIONS FOR CLIENT-SIDE CACHING ───────────────────────────────
    function getCachedData(key) {
        const cached = localStorage.getItem(key);
        if (cached) {
            try {
                const obj = JSON.parse(cached);
                if (new Date().getTime() < obj.expiration) {
                    return obj.data;
                } else {
                    localStorage.removeItem(key);
                }
            } catch (e) {
                console.error('Error parsing cached data for key ' + key, e);
            }
        }
        return null;
    }

    function setCachedData(key, data, ttl) { // ttl in milliseconds
        const expiration = new Date().getTime() + ttl;
        localStorage.setItem(key, JSON.stringify({ data, expiration }));
    }

    function escapeHtml(unsafe) {
        return unsafe
            ? unsafe.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;")
            : '';
    }

    // ─── LOAD TAB-SPECIFIC DATA ────────────────────────────────────────────────────
    function loadTabData(tabId) {
        console.log('Loading tab:', tabId);
        switch(tabId) {
            case 'leagues':
                loadLeagues();
                break;
            case 'usage':
                loadApiUsage();
                break;
            case 'subscriptions':
                loadSubscriptions();
                break;
            case 'profile':
                // Profile tab is static – no AJAX call needed.
                break;
        }
    }

    // ─── TAB SWITCHING ─────────────────────────────────────────────────────────────
    $('.fotlive-nav-item').on('click', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');
        if (!tabId) {
            console.error('No tab ID found');
            return;
        }
        $('.fotlive-nav-item').removeClass('active');
        $(this).addClass('active');
        $('.fotlive-tab-content').removeClass('active');
        const targetTab = $(`#${tabId}-tab`);
        if (targetTab.length) {
            targetTab.addClass('active');
            loadTabData(tabId);
        } else {
            console.error('Target tab not found:', tabId);
        }
    });

    // ─── LOGOUT HANDLER ─────────────────────────────────────────────────────────────
    $('#fotlive-logout-btn').on('click', function(e) {
        e.preventDefault();
        if (!fotliveDashboard || !fotliveDashboard.nonce) {
            console.error('Missing fotliveDashboard configuration');
            alert('Configuration error. Please refresh the page.');
            return;
        }
        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fotlive_logout',
                nonce: fotliveDashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    localStorage.removeItem('fotlive_token');
                    window.location.reload();
                } else {
                    console.error('Logout failed:', response);
                    alert('Failed to logout. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Logout error:', {xhr, status, error});
                alert('Failed to logout. Please try again.');
            }
        });
    });

    // ─── LEAGUES FUNCTIONS ─────────────────────────────────────────────────────────
    function loadLeagues() {
        const tableBody = $('#leagues-table-body');
        tableBody.empty();

        // Show cached leagues immediately (cache TTL: 15 minutes)
        const cachedLeagues = getCachedData('fotlive_leagues_cache');
        if (cachedLeagues) {
            renderLeagues(cachedLeagues);
        } else {
            tableBody.append('<tr><td colspan="3" class="text-center">Loading leagues...</td></tr>');
        }

        const token = localStorage.getItem('fotlive_token');
        if (!token) {
            tableBody.empty();
            tableBody.append(`
                <tr>
                    <td colspan="3" class="text-center text-red-600">
                        Please log in to view leagues
                    </td>
                </tr>
            `);
            return;
        }

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fotlive_get_leagues',
                nonce: fotliveDashboard.nonce,
                token: token
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    // Cache the data for 15 minutes (15 * 60 * 1000 ms)
                    setCachedData('fotlive_leagues_cache', response.data, 15 * 60 * 1000);
                    renderLeagues(response.data);
                } else {
                    const errorMsg = response.data || 'Failed to load leagues';
                    tableBody.html(`
                        <tr>
                            <td colspan="3" class="text-center text-red-600">
                                ${escapeHtml(errorMsg)}
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error loading leagues';
                if (xhr.status === 401) {
                    errorMessage = 'Your session has expired. Please log in again.';
                    localStorage.removeItem('fotlive_token');
                    window.location.reload();
                } else {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.data || errorMessage;
                    } catch (e) {
                        errorMessage += `: ${error}`;
                    }
                }
                tableBody.html(`
                    <tr>
                        <td colspan="3" class="text-center text-red-600">
                            ${escapeHtml(errorMessage)}
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderLeagues(leagues) {
        const tableBody = $('#leagues-table-body');
        tableBody.empty();
        if (leagues.length === 0) {
            tableBody.append(`
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="p-4">
                            <p class="text-gray-500 mb-2">You haven't subscribed to any leagues yet.</p>
                            <button class="fotlive-btn-primary" onclick="window.location.href='https://livefootballcenter.com/livefot/subscribe'">
                                Subscribe to Leagues
                            </button>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        leagues.forEach(league => {
            tableBody.append(`
                <tr>
                    <td>
                        <div class="league-name">
                            <span class="font-medium">${escapeHtml(league.LeagueName || league.leagueName || 'Unknown League')}</span>
                            <span class="country-name">${escapeHtml(league.CountryName || league.countryName || 'Unknown Country')}</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-active">Active</span>
                    </td>
                    <td>
                        ${escapeHtml(league.LeagueId || league.leagueId || '-')}
                    </td>
                </tr>
            `);
        });
    }

    // ─── API USAGE FUNCTIONS ─────────────────────────────────────────────────────────
    function loadApiUsage() {
        const usageGrid = $('#api-usage-grid');
        usageGrid.empty();

        // Show cached API usage immediately (cache TTL: 1 minute)
        const cachedUsage = getCachedData('fotlive_api_usage_cache');
        if (cachedUsage) {
            renderApiUsage(cachedUsage);
        } else {
            usageGrid.append(`
                <div class="text-center p-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2">Loading API usage data...</p>
                </div>
            `);
        }

        let token = localStorage.getItem('fotlive_token');
        if (!token && window.fotliveDashboard && window.fotliveDashboard.token) {
            token = window.fotliveDashboard.token;
            localStorage.setItem('fotlive_token', token);
        }
        if (!token) {
            usageGrid.html(`
                <div class="text-center text-red-600 p-4">
                    Please log in to view API usage
                </div>
            `);
            return;
        }

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fotlive_get_api_usage',
                nonce: fotliveDashboard.nonce,
                token: token
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Cache the data for 1 minute (1 * 60 * 1000 ms)
                    setCachedData('fotlive_api_usage_cache', response.data, 1 * 60 * 1000);
                    renderApiUsage(response.data);
                } else {
                    usageGrid.html(`
                        <div class="text-center text-red-600 p-4">
                            Failed to load API usage data
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                usageGrid.html(`
                    <div class="text-center text-red-600 p-4">
                        Error loading API usage data: ${escapeHtml(error)}
                    </div>
                `);
            }
        });
    }

    function renderApiUsage(apiInfo) {
        const usageGrid = $('#api-usage-grid');
        usageGrid.empty();
        usageGrid.append(`
            <div class="fotlive-api-info">
                <h3 class="text-xl font-semibold mb-6">API Information</h3>
                <div class="space-y-4">
                    <div class="api-key-container">
                        <span class="text-gray-600" style="min-width:80px;">API Key:</span>
                        <div class="api-key-field">
                            <div class="api-key-hidden" id="apiKeyText">${escapeHtml(apiInfo.ApiKey || 'N/A')}</div>
                            <div class="api-key-tooltip" id="apiKeyTooltip">Copied!</div>
                        </div>
                        <button class="api-key-button" id="toggleApiKey">
                            <span id="toggleIcon">
                              <span id="toggleIcon">
										<span id="toggleIcon">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
</span>
                         
                        </button>
                        <button class="api-key-button" id="copyApiKey">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            <span>Copy</span>
                        </button>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Expires At:</span>
                        <span class="font-medium">${escapeHtml(apiInfo.ExpiresAt ? new Date(apiInfo.ExpiresAt).toLocaleString() : 'N/A')}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-3 py-1 rounded ${apiInfo.IsActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${apiInfo.IsActive ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Max Calls Per Hour:</span>
                        <span class="font-medium">${apiInfo.MaxCallsPerHour || 0}</span>
                    </div>
                </div>
            </div>
        `);

        // Render endpoint usage if available
        let endpoints = [];
        try {
            if (typeof apiInfo.Endpoints === 'string') {
                endpoints = JSON.parse(apiInfo.Endpoints);
            } else if (Array.isArray(apiInfo.Endpoints)) {
                endpoints = apiInfo.Endpoints;
            }

            if (endpoints && endpoints.length > 0) {
                usageGrid.append(`
                    <div class="fotlive-endpoints-grid">
                        <h3 class="text-xl font-semibold mb-4">Endpoint Usage</h3>
                        ${endpoints.map(endpoint => {
                            const percentage = (endpoint.CallCount / apiInfo.MaxCallsPerHour) * 100;
                            const progressClass = percentage >= 90
                                ? 'danger'
                                : percentage >= 75
                                    ? 'warning'
                                    : '';
                            const hours = Math.floor(endpoint.MinutesRemaining / 60);
                            const minutes = endpoint.MinutesRemaining % 60;
                            const timeDisplay = hours > 0
                                ? `${hours}h ${minutes}m`
                                : `${minutes}m`;
                            return `
                                <div class="fotlive-usage-item">
                                    <div class="endpoint-header">
                                        <span class="endpoint-name">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                            </svg>
                                            ${escapeHtml(endpoint.EndPoint)}
                                        </span>
                                    </div>
                                    <div class="endpoint-usage-info">
                                        <div class="calls-info">
                                            <div class="calls-count">
                                                <span class="current-calls">${endpoint.CallCount}</span>
                                                <span class="calls-divider">/</span>
                                                <span class="max-calls">${apiInfo.MaxCallsPerHour}</span>
                                            </div>
                                            <div class="calls-label">API Calls</div>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill ${progressClass}" style="width: ${Math.min(percentage, 100)}%"></div>
                                        </div>
                                    </div>
                                    <div class="endpoint-stats">
                                        <div class="stat-item">
                                            <div class="stat-label">Usage Rate</div>
                                            <div class="stat-value ${progressClass ? `text-${progressClass}` : ''}">
                                                ${Math.round(percentage)}%
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Remaining Calls</div>
                                            <div class="stat-value">
                                                ${apiInfo.MaxCallsPerHour - endpoint.CallCount}
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Reset In</div>
                                            <div class="stat-value with-icon">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                ${timeDisplay}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `);
                initializeApiKeyHandlers();
            } else {
                usageGrid.append(`
                    <div class="text-center text-gray-600 p-4">
                        No endpoint usage data available
                    </div>
                `);
            }
        } catch (e) {
            console.error('Error parsing endpoints:', e);
            usageGrid.append(`
                <div class="text-center text-red-600 p-4">
                    <p>Error parsing endpoint data</p>
                    <p class="text-sm mt-2">${escapeHtml(e.message)}</p>
                </div>
            `);
        }
    }

    // ─── SUBSCRIPTION PLANS FUNCTIONS ─────────────────────────────────────────────
    function loadSubscriptions() {
        const subscriptionsContainer = $('#subscriptions-tab');
        subscriptionsContainer.empty();

        // Show cached subscription plans immediately (15 minutes TTL)
        const cachedSubscriptions = getCachedData('fotlive_subscriptions_cache');
        if (cachedSubscriptions) {
            renderSubscriptions(cachedSubscriptions);
        } else {
            subscriptionsContainer.append(`
                <div class="text-center p-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2">Loading subscription plans...</p>
                </div>
            `);
        }

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fotlive_get_subscription_plans',
                nonce: fotliveDashboard.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Cache for 15 minutes (15 * 60 * 1000 ms)
                    setCachedData('fotlive_subscriptions_cache', response.data, 15 * 60 * 1000);
                    renderSubscriptions(response.data);
                } else {
                    subscriptionsContainer.html(`
                        <div class="text-center text-red-600 p-4">
                            <p>No subscription plans available at the moment.</p>
                            <p class="mt-2">Please try again later or contact support.</p>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                subscriptionsContainer.html(`
                    <div class="text-center text-red-600 p-4">
                        <p>Error loading subscription plans</p>
                        <p class="mt-2 text-sm">${escapeHtml(error)}</p>
                    </div>
                `);
            }
        });
    }

    // Replaced the <a> link with a button for the “Subscribe Now” action.
 /*   function renderSubscriptions(plans) {
        const subscriptionsContainer = $('#subscriptions-tab');
        subscriptionsContainer.empty();
        subscriptionsContainer.html(`
            <div class="subscription-header">
                <h2 class="text-2xl font-bold mb-6">Subscription Plans</h2>
                <p class="text-gray-600 mb-8">Choose a plan that best fits your needs</p>
            </div>
            <div class="subscription-plans-grid">
                ${plans.map(plan => `
                    <div class="subscription-plan-card ${plan.is_popular ? 'popular' : ''}">
                        ${plan.is_popular ? '<div class="popular-badge">Most Popular</div>' : ''}
                        <div class="plan-header">
                            <h3 class="plan-name">${escapeHtml(plan.name)}</h3>
                            <div class="plan-price">
                                <span class="currency">$</span>
                                <span class="amount">${escapeHtml(plan.price)}</span>
                                <span class="period">/${plan.billing_period}</span>
                            </div>
                        </div>
                        <div class="plan-features">
                            ${plan.features.map(feature => `
                                <div class="feature-item">
                                    <svg class="feature-icon" viewBox="0 0 24 24" width="16" height="16">
                                        <polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <span>${escapeHtml(feature)}</span>
                                </div>
                            `).join('')}
                        </div>
                        <button
                            class="subscribe-button"
                            data-product-id="${escapeHtml(plan.product_id)}"
                        >
                            Subscribe Now
                        </button>
                    </div>
                `).join('')}
            </div>
        `);
    }*/
	
	function renderSubscriptions(plans) {
    const subscriptionsContainer = $('#subscriptions-tab');
    subscriptionsContainer.empty();

    subscriptionsContainer.html(`
        <div class="subscription-header">
            <h2 class="text-2xl font-bold mb-6">Subscription Plans</h2>
            <p class="text-gray-600 mb-8">Choose a plan that best fits your needs</p>
        </div>
        <div class="subscription-plans-grid">
            ${plans.map(plan => `
                <div class="subscription-plan-card ${plan.is_popular ? 'popular' : ''}">
                    ${plan.is_popular ? '<div class="popular-badge">Most Popular</div>' : ''}

                    <!-- NEW: Display the product image if we have one -->
                    ${plan.image_url
                        ? `
                            <div class="plan-image-wrapper">
                                <img 
                                    src="${escapeHtml(plan.image_url)}" 
                                    alt="${escapeHtml(plan.name)}" 
                                    class="subscription-plan-image" 
                                />
                            </div>
                          `
                        : ''
                    }

                    <div class="plan-header">
                        <h3 class="plan-name">${escapeHtml(plan.name)}</h3>
                        <div class="plan-price">
                            <span class="currency">$</span>
                            <span class="amount">${escapeHtml(plan.price)}</span>
                            <span class="period">/${plan.billing_period}</span>
                        </div>
                    </div>

                    <div class="plan-features">
                        ${plan.features.map(feature => `
                            <div class="feature-item">
                                <svg class="feature-icon" viewBox="0 0 24 24" width="16" height="16">
                                    <polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <span>${escapeHtml(feature)}</span>
                            </div>
                        `).join('')}
                    </div>

                    <button
                        class="subscribe-button"
                        data-product-id="${escapeHtml(plan.product_id)}"
                    >
                        Subscribe Now
                    </button>
                </div>
            `).join('')}
        </div>
    `);
}


	
	
	

    // ─── API KEY HANDLERS ──────────────────────────────────────────────────────────
    // 
    function initializeApiKeyHandlers() {
        const apiKeyText = $('#apiKeyText');
        const toggleBtn = $('#toggleApiKey');
        const copyBtn = $('#copyApiKey');
        const tooltip = $('#apiKeyTooltip');

        toggleBtn.on('click', function() {
            apiKeyText.toggleClass('api-key-visible');
            const isVisible = apiKeyText.hasClass('api-key-visible');
            
            if (isVisible) {
                $('#toggleIcon').html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                `);
                $('#toggleText').text('Hide');
            } else {
                $('#toggleIcon').html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                `);
                $('#toggleText').text('Show');
            }
        });

        copyBtn.on('click', function() {
            const textToCopy = apiKeyText.text();
            navigator.clipboard.writeText(textToCopy).then(() => {
                tooltip.addClass('visible');
                setTimeout(() => tooltip.removeClass('visible'), 2000);
            });
        });
    }
  /*  function initializeApiKeyHandlers() {
        const apiKeyText = $('#apiKeyText');
        const toggleBtn = $('#toggleApiKey');
        const copyBtn = $('#copyApiKey');
        const tooltip = $('#apiKeyTooltip');

		 toggleBtn.on('click', function() {
        apiKeyText.toggleClass('api-key-visible');
        const isVisible = apiKeyText.hasClass('api-key-visible');
        
        if (isVisible) {
            $('#toggleIcon').html(`
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            `);
            $('#toggleText').text('Hide');
        } else {
            $('#toggleIcon').html(`
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-9-10-9s1.24-3.72 4.06-6.06"/>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `);
            $('#toggleText').text('Show');
        }
    });


        copyBtn.on('click', function() {
            const textToCopy = apiKeyText.text();
            navigator.clipboard.writeText(textToCopy).then(() => {
                tooltip.addClass('visible');
                setTimeout(() => tooltip.removeClass('visible'), 2000);
            });
        });
    }*/

    // ─── INITIALIZE DASHBOARD ─────────────────────────────────────────────────────
    function initializeDashboard() {
        console.log('Initializing dashboard');
        const activeTab = $('.fotlive-nav-item.active').data('tab');
        if (activeTab) {
            loadTabData(activeTab);
        } else {
            loadTabData('profile');
        }
        const token = localStorage.getItem('fotlive_token');
        if (!token) {
            console.log('No token found, user might need to login');
        }
    }
    initializeDashboard();

    // ─── "ADD LEAGUE" MODAL ─────────────────────────────────────────────────────
    $(document).on('click', '.fotlive-btn-primary:contains("Add League")', function(e) {
        e.preventDefault();
        showAddLeagueModal();
    });

    let selectedLeagues = new Set();

    function showAddLeagueModal() {
        let modal = document.getElementById('add-league-modal');
        if (!modal) {
            const modalHtml = `
                <div id="add-league-modal" class="fotlive-modal">
                    <div class="fotlive-modal-content">
                        <div class="fotlive-modal-header">
                            <h3>Add Leagues</h3>
                            <button class="fotlive-modal-close">&times;</button>
                        </div>
                        <div class="fotlive-modal-body">
                            <div class="subscription-code-section mb-4">
                                <label for="subscription-code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subscription Code
                                </label>
                                <input type="text" 
                                       id="subscription-code" 
                                       class="fotlive-input" 
                                       placeholder="Enter subscription code"
                                       maxlength="12">
                                <p class="text-sm text-gray-500 mt-1">
                                    First 4 digits indicate the number of leagues you can subscribe to
                                </p>
                            </div>
                            <div id="league-limit-info" class="hidden mb-4 p-3 bg-blue-50 text-blue-700 rounded">
                                You can subscribe to up to <span id="league-limit">0</span> leagues
                            </div>
                            <div id="leagues-selection" class="hidden">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Available Leagues
                                    </label>
                                    <div class="leagues-grid" id="available-leagues">
                                        Loading leagues...
                                    </div>
                                </div>
                                <div class="selected-leagues-section">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Selected Leagues (<span id="selected-count">0</span>/<span id="max-leagues">0</span>)
                                    </label>
                                    <div id="selected-leagues" class="selected-leagues-list"></div>
                                </div>
                            </div>
                        </div>
                        <div class="fotlive-modal-footer">
                            <button class="fotlive-btn-secondary" id="cancel-subscription">Cancel</button>
                            <button class="fotlive-btn-primary" id="submit-subscription" disabled>
                                Subscribe to Leagues
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modal = document.getElementById('add-league-modal');
            initializeModalHandlers();
        }
        modal.style.display = 'flex';
    }

    function initializeModalHandlers() {
        const modal = document.getElementById('add-league-modal');
        const closeBtn = modal.querySelector('.fotlive-modal-close');
        const cancelBtn = document.getElementById('cancel-subscription');
        const codeInput = document.getElementById('subscription-code');
        const submitBtn = document.getElementById('submit-subscription');

        // Close modal handlers
        closeBtn.onclick = () => modal.style.display = 'none';
        cancelBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (e) => {
            if (e.target === modal) modal.style.display = 'none';
        };

        // Code input handler
        codeInput.addEventListener('input', function() {
            const code = this.value.trim();
            if (code.length >= 4) {
                const leagueLimit = parseInt(code.substring(0, 4));
                if (!isNaN(leagueLimit)) {
                    document.getElementById('league-limit').textContent = leagueLimit;
                    document.getElementById('max-leagues').textContent = leagueLimit;
                    document.getElementById('league-limit-info').classList.remove('hidden');
                    document.getElementById('leagues-selection').classList.remove('hidden');
                    loadAvailableLeagues();
                }
            }
        });

        // Submit handler uses the shared selectedLeagues set
        submitBtn.addEventListener('click', function() {
            const code = codeInput.value.trim();
            const leagueIds = Array.from(selectedLeagues);
            if (!code || leagueIds.length === 0) {
                alert('Please enter a valid code and select at least one league');
                return;
            }
            subscribeToLeagues(code, leagueIds);
        });
    }

    function loadAvailableLeagues() {
        const token = localStorage.getItem('fotlive_token');
        if (!token) return;
        const container = document.getElementById('available-leagues');
        container.innerHTML = '<div class="text-center">Loading leagues...</div>';

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_get_available_leagues',
                nonce: fotliveDashboard.nonce,
                token: token
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    container.innerHTML = response.data.map(league => `
                        <div class="league-item" data-id="${league.LeagueId}">
                            <div class="league-info">
                                <span class="league-name">${escapeHtml(league.LeagueName)}</span>
                                <span class="league-country">${escapeHtml(league.CountryName)}</span>
                            </div>
                            <button class="select-league-btn">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="10" y1="5" x2="10" y2="15" />
                                    <line x1="5" y1="10" x2="15" y2="10" />
                                </svg>
                            </button>
                        </div>
                    `).join('');
                    initializeLeagueSelection();
                } else {
                    container.innerHTML = '<div class="text-red-600">Failed to load leagues</div>';
                }
            },
            error: function() {
                container.innerHTML = '<div class="text-red-600">Error loading leagues</div>';
            }
        });
    }

    function initializeLeagueSelection() {
        const container = document.getElementById('available-leagues');
        const selectedContainer = document.getElementById('selected-leagues');
        const submitBtn = document.getElementById('submit-subscription');

        container.addEventListener('click', function(e) {
            const leagueItem = e.target.closest('.league-item');
            if (!leagueItem) return;
            const leagueId = leagueItem.dataset.id;
            const maxLeagues = parseInt(document.getElementById('max-leagues').textContent);
            if (selectedLeagues.size >= maxLeagues) {
                alert(`You can only select up to ${maxLeagues} leagues`);
                return;
            }
            selectedLeagues.add(leagueId);
            updateSelectedLeagues();
        });

        function updateSelectedLeagues() {
            const selectedCount = selectedLeagues.size;
            document.getElementById('selected-count').textContent = selectedCount;
            submitBtn.disabled = selectedCount === 0;
            selectedContainer.innerHTML = Array.from(selectedLeagues).map(id => {
                const leagueItem = container.querySelector(`[data-id="${id}"]`);
                const leagueName = leagueItem.querySelector('.league-name').textContent;
                const countryName = leagueItem.querySelector('.league-country').textContent;
                return `
                    <div class="selected-league-item" data-id="${id}">
                        <div class="league-info">
                            <span class="league-name">${escapeHtml(leagueName)}</span>
                            <span class="league-country">${escapeHtml(countryName)}</span>
                        </div>
                        <button class="remove-league-btn">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="10" x2="15" y2="10" />
                            </svg>
                        </button>
                    </div>
                `;
            }).join('');
        }

        selectedContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-league-btn');
            if (!removeBtn) return;
            const leagueItem = removeBtn.closest('.selected-league-item');
            const leagueId = leagueItem.dataset.id;
            selectedLeagues.delete(leagueId);
            updateSelectedLeagues();
        });
    }

    function subscribeToLeagues(code, leagueIds) {
        const token = localStorage.getItem('fotlive_token');
        if (!token) return;

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_subscribe_to_leagues',
                nonce: fotliveDashboard.nonce,
                token: token,
                code: code,
                league_ids: leagueIds
            },
            success: function(response) {
                if (response.success) {
                    alert('Successfully subscribed to leagues!');
                    document.getElementById('add-league-modal').style.display = 'none';
                    loadLeagues();
                } else {
                    alert(response.data || 'Failed to subscribe to leagues');
                }
            },
            error: function() {
                alert('Error subscribing to leagues');
            }
        });
    }

    // ─── NEW: CREATE SUBSCRIPTION ORDER & REDIRECT IN SAME TAB ──────────────────
    $(document).on('click', '.subscribe-button', function(e) {
        e.preventDefault();

        // Ensure user is logged in
        const token = localStorage.getItem('fotlive_token');
        if (!token) {
            alert('Please log in before subscribing to a plan.');
            return;
        }

        const productId = $(this).data('product-id');
        if (!productId) {
            alert('Invalid product ID.');
            return;
        }

        $.ajax({
            url: fotliveDashboard.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fotlive_create_subscription_order',
                nonce: fotliveDashboard.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success && response.data.checkout_url) {
                    // Redirect in the same tab
                    window.location.href = response.data.checkout_url;
                } else {
                    alert(response.data ? response.data : 'Error creating subscription order');
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error while creating subscription order: ' + error);
            }
        });
    });

});
