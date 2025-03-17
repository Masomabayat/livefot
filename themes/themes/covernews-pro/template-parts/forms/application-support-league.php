

<form id="appLeague" class="tabs-block__form application-support__form-league">
    <input type="hidden" name="action" value="submit_support_form">
    <input type="hidden" name="subject" value="Request to add new league">
    <input class="user-name" name="user-name" type="text" placeholder="<?php echo get_field('placeholder_name', 'option'); ?>">
    <input class="user-email" name="user-email" type="email" placeholder="<?php echo get_field('placeholder_email', 'option'); ?>">
    <!-- <input class="user-tel" name="user-tel" type="tel" placeholder="<?php echo get_field('placeholder_phone', 'option'); ?>"> -->
    <label class="tabs-block__form-select form__select-user-type">
        <input readonly name="user-type" type="text" placeholder="<?php echo get_field('placeholder_user-type', 'option'); ?>" class="tabs-block__form-selected form__select-user-type">
        <div class="tabs-block__select-variants">
            <!-- <input type="search" placeholder="Search"> -->
            <ul class="tabs-block__select-variants-list tabs-block__form-user-list">
                <?php 
                    $userList = get_field('tabs-block__form-user-list', 'option');
                
                    foreach($userList as $items) {
                        $user = $items['tabs-block__form-user'];
                ?>
                    <li class="tabs-block__select-variant form__select-user-variant"><?php echo $user; ?></li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </label>
    <input class="league" name="league" type="text" placeholder="<?php echo get_field('placeholder_league', 'option'); ?>">
    <input name="user-country" type="text" placeholder="<?php echo get_field('placeholder_country', 'option'); ?>" class="tabs-block__form-country">

    <textarea class="user-message" name="user-message" rows="5" placeholder="<?php echo get_field('placeholder_message', 'option'); ?>"></textarea>
    <input class="tabs-block__form-submit" type="submit" value="<?php echo get_field('form__button-name', 'option'); ?>">
    <p class="tabs-block__form-status-text"></p>
</form>