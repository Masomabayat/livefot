

<form id="giftAddGift" class="tabs-block__form gift-support__form-gift">
    <input type="hidden" name="action" value="submit_support_form">
    <input type="hidden" name="subject" value="Request to be sponsor and add your gift">
    <input class="user-name" name="user-name" type="text" placeholder="<?php echo get_field('placeholder_name', 'option'); ?>">
    <input class="user-email" name="user-email" type="email" placeholder="<?php echo get_field('placeholder_email', 'option'); ?>">
    <input class="user-tel" name="user-tel" type="tel" placeholder="<?php echo get_field('placeholder_phone', 'option'); ?>">
    <input name="gift-name" class="gift-support__form-gift-name" type="text" placeholder="<?php echo get_field('placeholder_gift-name', 'option'); ?>">
    <label class="gift-support__form-gift-file">
        <input id="gift" name="gift" type="file" accept='.jpg, .jpeg, .png, .webp, .svg'>
        <span class="gift-support__form-gift-text">
            <span class="gift-support__form-gift-icon" style="background: center / cover no-repeat url('<?php echo get_template_directory_uri() . '/assets/images/icons/icon__gift.svg'; ?>')"></span>
            <span class="gift-support__form-gift-file-name">Add Gift</span>
        </span>
    </label>
    <input name="sponsor" class="gift-support__form-sponsor-name" type="text" placeholder="<?php echo get_field('placeholder_sponsor-name-gift', 'option'); ?>">
    <input name="user-country" type="text" placeholder="<?php echo get_field('placeholder_country', 'option'); ?>" class="tabs-block__form-country">

    <textarea class="user-message" name="user-message" rows="5" placeholder="<?php echo get_field('placeholder_message', 'option'); ?>"></textarea>
    <input class="tabs-block__form-submit" type="submit" value="<?php echo get_field('form__button-name', 'option'); ?>">
    <p class="tabs-block__form-status-text"></p>
</form>