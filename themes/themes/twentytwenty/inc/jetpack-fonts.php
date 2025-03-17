<?php

add_filter( 'typekit_add_font_category_rules', function( $category_rules ) {

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'html,
		body',
		array(
			array( 'property' => 'font-family', 'value' => '-apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, sans-serif' ),
			array( 'property' => 'font-size', 'value' => '62.5%' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'input,
		textarea',
		array(
			array( 'property' => 'font-family', 'value' => '-apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, sans-serif' ),
			array( 'property' => 'font-size', 'value' => '62.5%' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'.entry-content,
		.entry-content p,
		.entry-content ol,
		.entry-content ul,
		.entry-content dl,
		.entry-content dt,
		.entry-content cite',
		array(
			array( 'property' => 'font-family', 'value' => 'NonBreakingSpaceOverride, "Hoefler Text", Garamond, "Times New Roman", serif' ),
			array( 'property' => 'font-size', 'value' => '2.1rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.faux-heading,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		.entry-content h1,
		.entry-content h2,
		.entry-content h3,
		.entry-content h4,
		.entry-content h5,
		.entry-content h6',
		array(
			array( 'property' => 'font-family', 'value' => '-apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, sans-serif' ),
			array( 'property' => 'font-weight', 'value' => '700' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-1,
		h1',
		array(
			array( 'property' => 'font-size', 'value' => '3.6rem' ),
			array( 'property' => 'font-weight', 'value' => '800' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-2,
		h2',
		array(
			array( 'property' => 'font-size', 'value' => '3.2rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-3,
		h3',
		array(
			array( 'property' => 'font-size', 'value' => '2.8rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-4,
		h4',
		array(
			array( 'property' => 'font-size', 'value' => '2.4rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-5,
		h5',
		array(
			array( 'property' => 'font-size', 'value' => '2.1rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.heading-size-6,
		h6',
		array(
			array( 'property' => 'font-size', 'value' => '1.6rem' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'dfn,
		em,
		i,
		q',
		array(
			array( 'property' => 'font-style', 'value' => 'italic' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'cite em,
		cite i,
		em em,
		em i,
		i em,
		i i',
		array(
			array( 'property' => 'font-weight', 'value' => 'bolder' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'big',
		array(
			array( 'property' => 'font-size', 'value' => '1.2em' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'small',
		array(
			array( 'property' => 'font-size', 'value' => '0.75em' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'b,
		strong',
		array(
			array( 'property' => 'font-weight', 'value' => '700' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'sub,
		sup',
		array(
			array( 'property' => 'font-size', 'value' => '75%' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'dt',
		array(
			array( 'property' => 'font-weight', 'value' => '700' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'blockquote',
		array(
			array( 'property' => 'font-size', 'value' => '1em' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'cite',
		array(
			array( 'property' => 'font-size', 'value' => '1.4rem' ),
			array( 'property' => 'font-style', 'value' => 'normal' ),
			array( 'property' => 'font-weight', 'value' => '600' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'.wp-caption-text,
		figcaption',
		array(
			array( 'property' => 'font-size', 'value' => '1.5rem' ),
			array( 'property' => 'font-weight', 'value' => '500' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'legend',
		array(
			array( 'property' => 'font-size', 'value' => '0.85em' ),
			array( 'property' => 'font-weight', 'value' => '700' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'label',
		array(
			array( 'property' => 'font-size', 'value' => '1.6rem' ),
			array( 'property' => 'font-weight', 'value' => '400' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'input[type="checkbox"] + label,
		label.inline',
		array(
			array( 'property' => 'font-weight', 'value' => '400' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'headings',
		'.button,
		.faux-button,
		.wp-block-button__link,
		.wp-block-file__button,
		button',
		array(
			array( 'property' => 'font-family', 'value' => '-apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, sans-serif' ),
		)
	);

	TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
		'.entry-content .wp-block-jetpack-contact-form,
		.entry-content .wp-block-jetpack-button button,
		.widget_text p,
		.widget_text ol,
		.widget_text ul,
		.widget_text dl,
		.widget_text dt,
		.widget-content .rssSummary',
		array(
			array( 'property' => 'font-family', 'value' => 'NonBreakingSpaceOverride, "Hoefler Text", Garamond, "Times New Roman", serif' ),
		)
	);

	return $category_rules;
} );
