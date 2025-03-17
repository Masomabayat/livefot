jQuery(document).ready(function($) {
    // Tab switching
    $('.fotlive-tab').on('click', function() {
        const tabId = $(this).data('tab');
        
        // Update active tab
        $('.fotlive-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show corresponding content
        $('.fotlive-tab-content').removeClass('active');
        $(`#${tabId}-tab-content`).addClass('active');
    });

    // Forgot password link
    $('#forgot-password-link').on('click', function(e) {
        e.preventDefault();
        $('.fotlive-tab-content').removeClass('active');
        $('#forgot-password-content').addClass('active');
    });

    // Back to login link
    $('.fotlive-form-footer').on('click', '#back-to-login-link', function(e) {
        e.preventDefault();
        $('.fotlive-tab-content').removeClass('active');
        $('#login-tab-content').addClass('active');
    });

    // Login form submission
    $('#fotlive-login-form').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#login-email').val();
        const password = $('#login-password').val();
        
        if (!email || !password) {
            showMessage('Please enter both email and password', 'error');
            return;
        }
        
        showMessage('Signing in...', 'info');
        
        $.ajax({
            url: fotliveAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_sign_in',
                nonce: fotliveAjax.nonce,
                email: email,
                password: password
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Store token and user details in localStorage
                    localStorage.setItem('fotlive_token', response.data.token);
                    localStorage.setItem('fotlive_user', JSON.stringify(response.data));

                    // Update WP Dashboard object token, if available
                    if (window.fotliveDashboard) {
                        window.fotliveDashboard.token = response.data.token;
                    }

                    // Hide login, show dashboard
                    $('#fotlive-login-section').hide();
                    $('#fotlive-dashboard-section').show();

                    // Update user info
                    updateUserDetails(response.data);

                    // Load initial data if function is defined
                    if (typeof loadTabData === 'function') {
                        loadTabData('profile');
                    }
                } else {
                    // Show error
                    let errorMessage = 'Login failed';
                    if (response.data) {
                        errorMessage += ': ' + response.data;
                    }
                    showMessage(errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred during login.\n\n';
                if (xhr.status === 0) {
                    errorMessage += 'Could not connect to the server. Check your internet connection.';
                } else if (xhr.status === 404) {
                    errorMessage += 'The login service is not available (404).';
                } else if (xhr.status === 500) {
                    errorMessage += 'Internal server error (500).';
                } else if (status === 'parsererror') {
                    errorMessage += 'Invalid response from server.';
                } else if (status === 'timeout') {
                    errorMessage += 'Request timed out.';
                } else if (status === 'abort') {
                    errorMessage += 'Request was aborted.';
                } else {
                    errorMessage += `Error: ${error}`;
                }
                showMessage(errorMessage, 'error');
            }
        });
    });

    // Register form submission
    $('#fotlive-register-form').on('submit', function(e) {
        e.preventDefault();
        
        const name = $('#register-name').val();
        const email = $('#register-email').val();
        const password = $('#register-password').val();
        const confirmPassword = $('#register-confirm-password').val();
        
        if (!name || !email || !password || !confirmPassword) {
            showMessage('Please fill in all fields', 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            showMessage('Passwords do not match', 'error');
            return;
        }
        
        showMessage('Creating account...', 'info');
        
        $.ajax({
            url: fotliveAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_sign_up',
                nonce: fotliveAjax.nonce,
                name: name,
                email: email,
                password: password
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Store token and user details in localStorage
                    localStorage.setItem('fotlive_token', response.data.token);
                    localStorage.setItem('fotlive_user', JSON.stringify(response.data));

                    // Update WP Dashboard object token, if available
                    if (window.fotliveDashboard) {
                        window.fotliveDashboard.token = response.data.token;
                    }

                    // Hide login, show dashboard
                    $('#fotlive-login-section').hide();
                    $('#fotlive-dashboard-section').show();

                    // Update user info
                    updateUserDetails(response.data);

                    // Load initial data if function is defined
                    if (typeof loadTabData === 'function') {
                        loadTabData('profile');
                    }
                } else {
                    // Show error
                    let errorMessage = 'Registration failed';
                    if (response.data) {
                        errorMessage += ': ' + response.data;
                    }
                    showMessage(errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred during registration.\n\n';
                if (xhr.status === 0) {
                    errorMessage += 'Could not connect to the server. Check your internet connection.';
                } else if (xhr.status === 404) {
                    errorMessage += 'The registration service is not available (404).';
                } else if (xhr.status === 500) {
                    errorMessage += 'Internal server error (500).';
                } else {
                    errorMessage += `Error: ${error}`;
                }
                showMessage(errorMessage, 'error');
            }
        });
    });

    // Forgot password form submission
    $('#fotlive-forgot-password-form').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#forgot-email').val();
        
        if (!email) {
            showMessage('Please enter your email address', 'error');
            return;
        }
        
        // Disable the submit button and show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Sending...');
        
        showMessage('Sending password reset code...', 'info');
        
        $.ajax({
            url: fotliveAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_reset_password_request',
                nonce: fotliveAjax.nonce,
                email: email
            },
            success: function(response) {
                // Re-enable the button
                submitBtn.prop('disabled', false).text(originalBtnText);
                
                if (response.success) {
                    showMessage('Password reset code sent. Please check your inbox for the reset code.', 'success');
                    
                    // Show reset password form
                    setTimeout(function() {
                        $('.fotlive-tab-content').removeClass('active');
                        $('#reset-password-content').addClass('active');
                    }, 2000);
                } else {
                    let errorMessage = 'Failed to send reset code';
                    if (response.data) {
                        errorMessage += ': ' + response.data;
                    }
                    showMessage(errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Re-enable the button
                submitBtn.prop('disabled', false).text(originalBtnText);
                
                let errorMessage = 'An error occurred while sending the reset code.\n\n';
                if (xhr.status === 0) {
                    errorMessage += 'Could not connect to the server. Check your internet connection.';
                } else if (xhr.status === 404) {
                    errorMessage += 'The reset service is not available (404).';
                } else if (xhr.status === 500) {
                    errorMessage += 'Internal server error (500).';
                } else {
                    errorMessage += `Error: ${error}`;
                }
                showMessage(errorMessage, 'error');
            }
        });
    });

    // Reset password form submission
    $('#fotlive-reset-password-form').on('submit', function(e) {
        e.preventDefault();
        
        const code = $('#reset-code').val();
        const newPassword = $('#new-password').val();
        const confirmNewPassword = $('#confirm-new-password').val();
        
        if (!code || !newPassword || !confirmNewPassword) {
            showMessage('Please fill in all fields', 'error');
            return;
        }
        
        if (newPassword !== confirmNewPassword) {
            showMessage('Passwords do not match', 'error');
            return;
        }
        
        // Disable the submit button and show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Resetting...');
        
        showMessage('Resetting password...', 'info');
        
        $.ajax({
            url: fotliveAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'fotlive_reset_password',
                nonce: fotliveAjax.nonce,
                code: code,
                new_password: newPassword
            },
            success: function(response) {
                // Re-enable the button
                submitBtn.prop('disabled', false).text(originalBtnText);
                
                if (response.success) {
                    showMessage('Password reset successfully. You can now log in with your new password.', 'success');
                    
                    // Clear form fields
                    $('#reset-code').val('');
                    $('#new-password').val('');
                    $('#confirm-new-password').val('');
                    
                    // Show login form
                    setTimeout(function() {
                        $('.fotlive-tab-content').removeClass('active');
                        $('#login-tab-content').addClass('active');
                    }, 2000);
                } else {
                    let errorMessage = 'Failed to reset password';
                    if (response.data) {
                        errorMessage += ': ' + response.data;
                    }
                    showMessage(errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Re-enable the button
                submitBtn.prop('disabled', false).text(originalBtnText);
                
                let errorMessage = 'An error occurred while resetting the password.\n\n';
                if (xhr.status === 0) {
                    errorMessage += 'Could not connect to the server. Check your internet connection.';
                } else if (xhr.status === 404) {
                    errorMessage += 'The reset service is not available (404).';
                } else if (xhr.status === 500) {
                    errorMessage += 'Internal server error (500).';
                } else {
                    errorMessage += `Error: ${error}`;
                }
                showMessage(errorMessage, 'error');
            }
        });
    });

    function updateUserDetails(userData) {
        const userNameEl  = document.getElementById('fotlive-username');
        const profNameEl  = document.getElementById('profile-name');
        const profEmailEl = document.getElementById('profile-email');
        const profIdEl    = document.getElementById('profile-user-id');
        const profStatEl  = document.getElementById('profile-status');

        if (userNameEl)  userNameEl.textContent  = userData.name   || 'User';
        if (profNameEl)  profNameEl.textContent  = userData.name   || 'N/A';
        if (profEmailEl) profEmailEl.textContent = userData.email  || 'N/A';
        if (profIdEl)    profIdEl.textContent    = userData.user_id || 'N/A';
        if (profStatEl)  profStatEl.textContent  = userData.status || 'Active';
    }

    function showMessage(message, type) {
        const messageDiv = $('#fotlive-login-message');
        messageDiv.removeClass('success error info');
        
        if (type) {
            messageDiv.addClass(type);
        }
        
        messageDiv.html(message);
        messageDiv.show(); // Ensure the message is visible
        
        // Auto-hide success and info messages after 5 seconds
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                messageDiv.fadeOut(function() {
                    messageDiv.html('').removeClass('success error info').show();
                });
            }, 5000);
        }
    }

    // Check localStorage on page load and sync with PHP-localized token if missing
    function initializeLoginState() {
        let token = localStorage.getItem('fotlive_token');
        const user = localStorage.getItem('fotlive_user');
        
        if (!token && window.fotliveDashboard && window.fotliveDashboard.token) {
            token = window.fotliveDashboard.token;
            localStorage.setItem('fotlive_token', token);
        }
        
        if (token) {
            if (window.fotliveDashboard) {
                window.fotliveDashboard.token = token;
            }
            
            // Hide login, show dashboard
            $('#fotlive-login-section').hide();
            $('#fotlive-dashboard-section').show();

            if (user) {
                try {
                    const userData = JSON.parse(user);
                    updateUserDetails(userData);
                } catch (e) {
                    console.error('Error parsing stored user data', e);
                }
            }
        }
    }

    // Initialize login state
    initializeLoginState();
});