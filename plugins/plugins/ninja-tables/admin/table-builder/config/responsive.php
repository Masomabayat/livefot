<?php

return [
    "general"      => [
        "name"    => __("General", "ninja-tables"),
        "key"     => 'general', //unique
        "has_pro" => false,
        "options" => [
            "enable_responsive_table" => [
                "label" => __("Enable Responsive Table", "ninja-tables"),
                "type"  => "switch",
                "value" => true,
            ],
        ],
    ],
    "mode_options" => [
        "name"    => __("Mode Options", "ninja-tables"),
        "key"     => 'mode_options', //unique
        "has_pro" => false,
        "options" => [
            "devices" => [
                "mobile"  => [
                    "name"               => "Mobile",
                    "key"                => "mobile",
                    "disable_breakpoint" => [
                        "label" => __("Disable Breakpoint", "ninja-tables"),
                        "key"   => "disable_breakpoint",
                        "type"  => "switch",
                        "value" => false,
                    ],
                    "top_row_as_header"  => [
                        "label" => __("Top Row As Header", "ninja-tables"),
                        "key"   => "top_row_as_header",
                        "type"  => "switch",
                        "value" => true,
                    ],
                    "items_per_row"      => [
                        "label" => __("Items Per Header", "ninja-tables"),
                        "key"   => "items_per_header",
                        "type"  => "slider",
                        "value" => 1,
                        "min"   => 1,
                        "max"   => 5,
                    ],
                    "cell_border"        => [
                        "label" => __("Group Separator", "ninja-tables"),
                        "key"   => "mobile_cell_border",
                        "type"  => "slider",
                        "value" => 4,
                        "min"   => 1,
                        "max"   => 10,
                    ],
                ],
                "tablet"  => [
                    "name"               => "Tablet",
                    "key"                => "tablet",
                    "disable_breakpoint" => [
                        "label" => __("Disable Breakpoint", "ninja-tables"),
                        "key"   => "disable_breakpoint",
                        "type"  => "switch",
                        "value" => false,
                    ],
                    "top_row_as_header"  => [
                        "label" => __("Top Row As Header", "ninja-tables"),
                        "key"   => "top_row_as_header",
                        "type"  => "switch",
                        "value" => true,
                    ],
                    "items_per_row"      => [
                        "label" => __("Items Per Header", "ninja-tables"),
                        "key"   => "items_per_header",
                        "type"  => "slider",
                        "value" => 4,
                        "min"   => 1,
                        "max"   => 5,
                    ],
                    "cell_border"        => [
                        "label" => __("Group Separator", "ninja-tables"),
                        "key"   => "tablet_cell_border",
                        "type"  => "slider",
                        "value" => 4,
                        "min"   => 1,
                        "max"   => 10,
                    ],
                ],
                "desktop" => [
                    "name"              => "Desktop",
                    "key"               => "desktop",
                    "top_row_as_header" => [
                        "label"   => __("Top Row As Header", "ninja-tables"),
                        "key"     => "top_row_as_header",
                        "type"    => "switch",
                        "value"   => false,
                        "disable" => true
                    ],
                    "static_top_row"    => [
                        "label"   => __("Static Top Row", "ninja-tables"),
                        "key"     => "static_top_row",
                        "type"    => "switch",
                        "value"   => false,
                        "disable" => true
                    ],
                ],
            ],
        ]
    ]
];