<?php
$max_width = "";
if (isset($setting['general']['options']['container_max_width_switch']['value']) && $setting['general']['options']['container_max_width_switch']['value'] == 'true') {
    $max_width = $setting['general']['options']['container_max_width_switch']['childs']['container_max_width']['value'];
    $tableAlignment = $setting['general']['options']['table_alignment']['value'];
    $alignment = '';
    if ($tableAlignment === 'left') {
        $alignment = 'margin-right: auto';
    } else {
        if ($tableAlignment === 'right') {
            $alignment = 'margin-left: auto';
        } else {
            if ($tableAlignment === 'center') {
                $alignment = 'margin-left: auto; margin-right: auto';
            }
        }
    }
}
$max_height = "500";
if (isset($setting['general']['options']['container_max_height']['value'])) {
    $max_height = $setting['general']['options']['container_max_height']['value'];
}
?>

<div class="ntb_table_wrapper" data-responsive='<?php echo json_encode($responsive) ?>'
     id='ninja_table_builder_<?php echo $table_id; ?>'
     style="
     <?php echo "max-height:$max_height" . "px" ?>;
     <?php echo $max_width != '' ? "max-width: $max_width" . "px;" . $alignment : 'max-width: 1160px'; ?>;">
    <?php
    echo $ninja_table_builder_html;
    ?>
</div>