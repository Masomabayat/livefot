


<?php 
    $tabsList = get_field('tabs-block__tabs-list');

    foreach($tabsList as $key => $items) {
        $listVariant = $items['tabs-block__tab-list-variant'];
        $tabTitle = $items['tabs-block__tab-title'];
        $list = $items['tabs-block__list'];
?>
    <div data-tab="<?php echo $key; ?>" class="tabs-block__changeable-content-box <?php echo $listVariant; ?>">
        <h2><?php echo $tabTitle; ?></h2>
            <?php 
                foreach($list as $listItems) {
                    $listItem = $listItems['tabs-block__tab-list-item'];
                    $textTitle = $listItems['tabs-block__text-title'];
                    $text = $listItems['tabs-block__text'];
            ?>
                <div class="tabs-block__tab-text-wrapper">
                    <h3><?php echo $textTitle; ?></h3>
                    <p><?php echo $text; ?></p>
                </div>
            <?php
                }
            ?>
    </div>
<?php
    }
?>