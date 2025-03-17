<?php
/**
 * Template Name: Home Page Template
 *
 * Home Page Template File
 *
 * @package True News
 */

get_header();

	$true_news_default = true_news_get_default_theme_options();
	$true_news_home_sections = get_theme_mod( 'true_news_home_sections', json_encode( $true_news_default['true_news_home_sections'] ) );
	$paged_active = false;

	if ( !is_paged() ) {
	    $paged_active = true;
	}

	$true_news_home_sections = json_decode( $true_news_home_sections );
	$repeat_times = 1;

	foreach ( $true_news_home_sections as $true_news_home_section ) {
	    $home_section_type = isset( $true_news_home_section->home_section_type ) ? $true_news_home_section->home_section_type : '';
	    switch ($home_section_type) {

	    	case 'latest-posts':

	            $ed_latest_news = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_latest_news == 'yes' ) {
	                true_news_latest_posts_section( $true_news_home_section, $repeat_times );
	            }
	        break;

	        case 'block-1':

	            $ed_block_1 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_1 == 'yes' && $paged_active ) {
	                true_news_block_1_section( $true_news_home_section, $repeat_times );
	            }
	        break;

	        case 'block-2':

	            $ed_block_2 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_2 == 'yes' && $paged_active ) {
	                true_news_block_2_section( $true_news_home_section, $repeat_times );
	            }
	        break;

	        case 'block-3':

	            $ed_block_3 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_3 == 'yes' && $paged_active ) {
	                true_news_block_3_section( $true_news_home_section, $repeat_times );
	            }
	        break;

	        case 'block-4':

	            $ed_block_4 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_4 == 'yes' && $paged_active ) {
	                true_news_block_4_section( $true_news_home_section, $repeat_times );
	            }
	        break;

	        case 'block-5':

	            $ed_block_5 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_5 == 'yes' && $paged_active ) {
	                true_news_block_5_section( $true_news_home_section, $repeat_times);
	            }
	        break;

	        case 'block-6':

	            $ed_block_6 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_6 == 'yes' && $paged_active ) {
	                true_news_block_6_section( $true_news_home_section, $repeat_times);
	            }
	        break;

	        case 'block-7':

	            $ed_block_7 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_7 == 'yes' && $paged_active ) {
	                true_news_block_7_section( $true_news_home_section, $repeat_times);
	            }
	        break;

	        case 'block-8':

	            $ed_block_8 = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_8 == 'yes' && $paged_active ) {
	                true_news_block_8_section( $true_news_home_section, $repeat_times);
	            }
	        break;

	        case 'recommended':

	            $ed_block_recommended = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '';
	            if ( $ed_block_recommended == 'yes' && $paged_active ) {
	                true_news_block_recommended_section( $true_news_home_section, $repeat_times);
	            }
	        break;

	        case 'advertise-area':
		
			$ed_advertise = isset( $true_news_home_section->section_ed ) ? $true_news_home_section->section_ed : '' ;
			
			if( $ed_advertise == 'yes' && $paged_active ){
		        true_news_advertise( $true_news_home_section );
		    }

	        break;

	        default:
	        break;

	    }
	    $repeat_times++;
	}

get_footer();
