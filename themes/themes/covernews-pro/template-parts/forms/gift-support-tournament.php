

<form id="giftTournament" class="tabs-block__form gift-support__form-tournament">
    <input type="hidden" name="action" value="submit_support_form">
    <input type="hidden" name="subject" value="Request to sponsor a tournament">
    <input class="user-name" name="user-name" type="text" placeholder="<?php echo get_field('placeholder_name', 'option'); ?>">
    <input class="user-email" name="user-email" type="email" placeholder="<?php echo get_field('placeholder_email', 'option'); ?>">
    <input class="user-tel" name="user-tel" type="tel" placeholder="<?php echo get_field('placeholder_phone', 'option'); ?>">
    <input class="tournament" name="tournament" type="text" placeholder="<?php echo get_field('placeholder_tournament', 'option'); ?>">
    <input class="sponsor" name="sponsor" type="text" placeholder="<?php echo get_field('placeholder_sponsor-name-tournament', 'option'); ?>">
    <input class="tabs-block__form-country" name="user-country" type="text" placeholder="<?php echo get_field('placeholder_country', 'option'); ?>">
    <label class="tabs-block__form-calendar-wrapper">
        <input class="tabs-block__form-choose-date" name="date" type="text" name="date" placeholder="<?php echo get_field('placeholder_date', 'option'); ?>">
        <span style="background: center / cover no-repeat url('<?php echo get_template_directory_uri() . '/assets/images/icons/icon__calendar.svg'; ?>')"></span>
    </label>
    <textarea class="user-message" name="user-message" rows="5" placeholder="<?php echo get_field('placeholder_message', 'option'); ?>"></textarea>
    <input class="tabs-block__form-submit" type="submit" value="<?php echo get_field('form__button-name', 'option'); ?>">
    <p class="tabs-block__form-status-text"></p>
</form>