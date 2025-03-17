

<form id="appFeature" class="tabs-block__form application-support__form-features">
    <input type="hidden" name="action" value="submit_support_form">
    <input type="hidden" name="subject" value="Request to add new features">
    <input class="user-name" name="user-name" type="text" placeholder="<?php echo get_field('placeholder_name', 'option'); ?>">
    <input class="user-email" name="user-email" type="email" placeholder="<?php echo get_field('placeholder_email', 'option'); ?>">
    <!-- <input class="user-tel" name="user-tel"  type="tel" placeholder="<?php echo get_field('placeholder_phone', 'option'); ?>"> -->
    <input name="user-country" type="text" placeholder="<?php echo get_field('placeholder_country', 'option'); ?>" class="tabs-block__form-country">
    <label class="tabs-block__form-select form__select-features" style="grid-column: unset;">
        <input readonly name="feature" type="text" placeholder="<?php echo get_field('placeholder_new-feature', 'option'); ?>" class="tabs-block__form-selected form__select-feature">
        <div class="tabs-block__select-variants">
            <!-- <input type="search" placeholder="Search"> -->
            <ul class="tabs-block__select-variants-list tabs-block__form-features-list">
                <?php 
                    $featuresList = get_field('tabs-block__form-features-list', 'option');
                
                    foreach($featuresList as $items) {
                        $feature = $items['tabs-block__form-feature'];
                ?>
                    <li class="tabs-block__select-variant form__select-features-variant"><?php echo $feature; ?></li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </label>
    <textarea class="user-message" name="user-message" rows="5" placeholder="<?php echo get_field('placeholder_message', 'option'); ?>"></textarea>
    <input class="tabs-block__form-submit" type="submit" value="<?php echo get_field('form__button-name', 'option'); ?>">
    <p class="tabs-block__form-status-text"></p>
</form>