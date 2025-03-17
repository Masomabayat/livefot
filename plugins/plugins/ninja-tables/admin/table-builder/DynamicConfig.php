<?php

class DynamicConfig
{
    public static function getSetting($dynamic_setting, $static_config)
    {
        $general       = $dynamic_setting['general']['options'];
        $background    = $dynamic_setting['background']['options'];
        $sticky        = $dynamic_setting['sticky']['options'];
        $accessibility = $dynamic_setting['accessibility']['options'];
        $border        = $dynamic_setting['border']['options'];
        $global_style  = $dynamic_setting['global_styling']['options'];

        $static_config['general']['options']['cell_padding']['value']                                                = $general['cell_padding']['value'];
        $static_config['general']['options']['table_alignment']['value']                                             = $general['table_alignment']['value'];
        $static_config['general']['options']['cell_min_auto_width']['value']                                         = $general['cell_min_auto_width']['value'];
        $static_config['general']['options']['container_max_height']['value']                                        = $general['container_max_height']['value'];
        $static_config['general']['options']['columns_rows_separate']['value']                                       = $general['columns_rows_separate']['value'];
        $static_config['general']['options']['columns_rows_separate']['childs']['space_between_column']['value']     = $general['columns_rows_separate']['childs']['space_between_column']['value'];
        $static_config['general']['options']['columns_rows_separate']['childs']['space_between_row']['value']        = $general['columns_rows_separate']['childs']['space_between_row']['value'];
        $static_config['general']['options']['container_max_width_switch']['value']                                  = $general['container_max_width_switch']['value'];
        $static_config['general']['options']['container_max_width_switch']['childs']['container_max_width']['value'] = $general['container_max_width_switch']['childs']['container_max_width']['value'];

        $static_config['background']['options']['header_background']['value']   = $background['header_background']['value'];
        $static_config['background']['options']['even_row_background']['value'] = $background['even_row_background']['value'];
        $static_config['background']['options']['odd_row_background']['value']  = $background['odd_row_background']['value'];

        $static_config['sticky']['options']['first_row_sticky']['value']    = $sticky['first_row_sticky']['value'];
        $static_config['sticky']['options']['first_column_sticky']['value'] = $sticky['first_column_sticky']['value'];

        $static_config['accessibility']['options']['table_role']['value'] = $accessibility['table_role']['value'];


        $static_config['border']['options']['table_border']['value']                                  = $border['table_border']['value'];
        $static_config['border']['options']['border_color']['value']                                  = $border['border_color']['value'];
        $static_config['border']['options']['inner_border']['value']                                  = $border['inner_border']['value'];
        $static_config['border']['options']['inner_border']['childs']['header_inner_border']['value'] = $border['inner_border']['childs']['header_inner_border']['value'];
        $static_config['border']['options']['inner_border']['childs']['inner_border_color']['value']  = $border['inner_border']['childs']['inner_border_color']['value'];
        $static_config['border']['options']['inner_border']['childs']['inner_border_size']['value']   = $border['inner_border']['childs']['inner_border_size']['value'];

        $static_config['global_styling']['options']['margin_top']['value']  = $global_style['margin_top']['value'];
        $static_config['global_styling']['options']['font_size']['value']   = $global_style['font_size']['value'];
        $static_config['global_styling']['options']['color']['value']       = $global_style['color']['value'];
        $static_config['global_styling']['options']['font_family']['value'] = $global_style['font_family']['value'];

        return $static_config;
    }

    public static function getResponsive($dynamic_responsive, $static_config)
    {
        $general = $dynamic_responsive['general']['options'];
        $mobile  = $dynamic_responsive['mode_options']['options']['devices']['mobile'];
        $tablet  = $dynamic_responsive['mode_options']['options']['devices']['tablet'];

        $static_config['general']['options']['enable_responsive_table']['value'] = $general['enable_responsive_table']['value'];

        $static_config['mode_options']['options']['devices']['mobile']['disable_breakpoint']['value'] = $mobile['disable_breakpoint']['value'];
        $static_config['mode_options']['options']['devices']['mobile']['top_row_as_header']['value']  = $mobile['top_row_as_header']['value'];
        $static_config['mode_options']['options']['devices']['mobile']['items_per_row']['value']      = $mobile['items_per_row']['value'];
        $static_config['mode_options']['options']['devices']['mobile']['cell_border']['value']        = isset($mobile['cell_border']['value']) ? $mobile['cell_border']['value'] : 0;

        $static_config['mode_options']['options']['devices']['tablet']['disable_breakpoint']['value'] = $tablet['disable_breakpoint']['value'];
        $static_config['mode_options']['options']['devices']['tablet']['top_row_as_header']['value']  = $tablet['top_row_as_header']['value'];
        $static_config['mode_options']['options']['devices']['tablet']['items_per_row']['value']      = $tablet['items_per_row']['value'];
        $static_config['mode_options']['options']['devices']['tablet']['cell_border']['value']        = isset($tablet['cell_border']['value']) ? $tablet['cell_border']['value'] : 0;

        return $static_config;
    }
}