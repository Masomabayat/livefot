<form id="appSocial" class="tabs-block__form application-support__form-social">
    <input type="hidden" name="action" value="submit_support_form">
    <input type="hidden" name="subject" value="Social media links">
    <input class="user-name" name="user-name" type="text" placeholder="<?php echo get_field('placeholder_name', 'option'); ?>">
    <input class="user-email" name="user-email" type="email" placeholder="<?php echo get_field('placeholder_email', 'option'); ?>">
    <input class="user-tel" name="user-tel" type="tel" placeholder="<?php echo get_field('placeholder_phone', 'option'); ?>">
    <input name="user-country" type="text" placeholder="<?php echo get_field('placeholder_country', 'option'); ?>" class="tabs-block__form-country">
    <textarea class="user-message" name="user-message" rows="5" placeholder="<?php echo get_field('placeholder_message', 'option'); ?>"></textarea>
    <input class="tabs-block__form-submit" type="submit" value="<?php echo get_field('form__button-name', 'option'); ?>">
    <p class="tabs-block__form-status-text"></p>
</form>