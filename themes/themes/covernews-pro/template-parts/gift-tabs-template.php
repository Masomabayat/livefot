


<?php 
    $tabsList = get_field('tabs-block__tabs-list');

    foreach($tabsList as $key => $items) {
        $tabTitle = $items['tabs-block__tab-title'];
        $textTitle = $items['tabs-block__text-title'];
        $text = $items['tabs-block__text'];
        $listTitle = $items['tabs-block__list-title'];
        $list = $items['tabs-block__list'];
?>
    <div data-tab="<?php echo $key; ?>" class="tabs-block__changeable-content-box">
        <h2><?php echo $tabTitle; ?></h2>
        <div class="tabs-block__tab-text-wrapper">
            <h3><?php echo $textTitle; ?></h3>
            <p><?php echo $text; ?></p>
        </div>
        <div class="tabs-block__tab-list-wrapper">
            <h3><?php echo $listTitle; ?></h3>
            <ol class="tabs-block__tab-list">
            <?php 
                foreach($list as $listItems) {
                    $listItem = $listItems['tabs-block__tab-list-item'];
            ?>
                <li class="tabs-block__tab-list-item"><?php echo $listItem; ?></li>
            <?php
                }
            ?>
            </ol>
        </div>
    </div>
<?php
    }
?>