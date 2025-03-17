<?php 
/**
 * Template Name: Terms and Conditions
 * Description: A blank page template with LTR layout, mobile responsiveness, for displaying Terms and Conditions.
 */
$last_updated = '07-Mar-2025';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(''); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            background-color: #F8F8F8;
            color: #181829;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .terms-container {
            max-width: 1200px;
            margin: auto;
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        h1, h2 {
            color: #181829;
        }

        ul {
            padding-left: 20px;
        }

        .alert-info {
            background-color: #EDF5F2;
            color: #181829;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .terms-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>

<div class="terms-container">
    <h1 class="mb-4">Terms and Conditions</h1>
    <p><strong>Last Updated:</strong> <?= htmlspecialchars($last_updated); ?></p>

    <section class="mt-5">
        <h2>Introduction</h2>
        <p>Thank you for choosing LiveFootballCenter. By subscribing or using our services, you acknowledge acceptance of these Terms & Conditions.</p>
    </section>

    <section>
        <h2>1. Subscription and Payments</h2>
        <ul>
            <li>API services billed annually in advance via credit/debit card.</li>
            <li>No automatic renewals. Manual renewal required.</li>
            <li>No refunds available after payment processing.</li>
        </ul>
    </section>

    <section>
        <h2>2. Subscription Changes</h2>
        <ul>
            <li>For downgrades or changes, contact us at <a href="mailto:info@livefootballcenter.com">info@livefootballcenter.com</a>.</li>
            <li>Changes take effect after your current subscription term ends. No refunds, discounts, or credits apply.</li>
        </ul>
    </section>

    <section>
        <h2>3. Data Accuracy and Availability</h2>
        <p>We strive for accuracy but make no guarantees regarding data completeness or availability.</p>
    </section>

    <section>
        <h2>4. Permitted Use of Data</h2>
        <p>Our API data is solely for internal use. Direct reselling, redistribution, or sharing without written consent is prohibited.</p>
    </section>

    <section>
        <h2>5. Intellectual Property Rights</h2>
        <p>All provided data is subject to intellectual property rights owned by LiveFootballCenter or third parties. You must secure necessary permissions to display content.</p>
    </section>

    <section>
        <h2>6. Embedded Video Content</h2>
        <p>We are not responsible for embedded videos from third-party services (e.g., YouTube).</p>
    </section>

    <section>
        <h2>7. Limitation of Liability</h2>
        <p>We are not liable for any damages arising from the use or inability to use our services. Our data is provided “as is” without any warranty.</p>
    </section>

    <section>
        <h2>8. Currency & VAT</h2>
        <ul>
            <li>Payments must be made in your selected currency.</li>
            <li>VAT applies unless a valid exemption is provided.</li>
        </ul>
    </section>

    <section>
        <h2>9. Dispute Resolution</h2>
        <p>Contact our support team at <a href="mailto:info@livefootballcenter.com">info@livefootballcenter.com</a> first to resolve disputes amicably.</p>
    </section>

    <section>
        <h2>10. Changes to Terms</h2>
        <p>Regularly review these Terms. Continued use after changes implies acceptance.</p>
    </section>

    <section>
        <h2>11. Governing Law</h2>
        <p>These Terms are governed by the laws of the Kingdom of Bahrain. Disputes shall be settled under Bahraini jurisdiction.</p>
    </section>

    <section>
        <h2>Contact Information</h2>
        <p>If you have any questions or concerns, please contact us at <a href="mailto:info@livefootballcenter.com">info@livefootballcenter.com</a>.</p>
    </section>

    <div class="alert alert-info mt-4">
        By subscribing or using our services, you acknowledge that you have read, understood, and accepted these Terms & Conditions.
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>