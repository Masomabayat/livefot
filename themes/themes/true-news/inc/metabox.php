<?php
/**
* Sidebar Metabox.
*
* @package True News
*/
 
add_action( 'add_meta_boxes', 'true_news_metabox' );

if( ! function_exists( 'true_news_metabox' ) ):


    function  true_news_metabox() {
        
        add_meta_box(
            'true_news_post_metabox',
            esc_html__( 'True News Options', 'true-news' ),
            'true_news_post_metafield_callback',
            'post', 
            'normal', 
            'high'
        );
        add_meta_box(
            'true_news_page_metabox',
            esc_html__( 'True News Options', 'true-news' ),
            'true_news_post_metafield_callback',
            'page',
            'normal', 
            'high'
        ); 
    }

endif;

/**
 * Callback function for post option.
*/
if( ! function_exists( 'true_news_post_metafield_callback' ) ):
	function true_news_post_metafield_callback() {
		global $post;
        $post_type = get_post_type( $post->ID );
		wp_nonce_field( basename( __FILE__ ), 'true_news_post_meta_nonce' ); ?>

        <div class="twp-tab-main">

            <div class="twp-metabox-tab">
                <ul>
                    <li>
                        <a id="twp-tab-og" class="twp-tab-active" href="javascript:void(0)"><?php esc_html_e('General Settings', 'true-news'); ?></a>
                    </li>
                </ul>
            </div>

            <div class="twp-tab-content">
                
                <div id="twp-tab-og-content" class="twp-content-wrap twp-tab-content-active">

                    <div class="twp-meta-panels">

                        <div class="twp-opt-wrap twp-opt-wrap-alt">

                            <label><b><?php esc_html_e( 'Sidebar Layout','true-news' ); ?></b></label>

                            <?php
                            $sidebar_layouts = true_news_sidebar_layout();
                            $true_news_post_sidebar = esc_html( get_post_meta( $post->ID, 'true_news_post_sidebar_option', true ) ); 
                            if( $true_news_post_sidebar == '' ){ $true_news_post_sidebar = 'global-sidebar'; } ?>

                            <select name="true_news_post_sidebar_option">
                                <option value="global-sidebar"><?php esc_html_e('Global Sidebar','true-news'); ?></option>
                                <?php
                                foreach ( $sidebar_layouts as $key => $true_news_post_sidebar_field ) { ?>
                                        <option <?php if( $true_news_post_sidebar == $key ){ echo 'selected'; } ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $true_news_post_sidebar_field );?></option>
                                <?php } ?>
                            </select>

                        </div>

                        <?php 
                        if( $post_type == 'post' ):

                            $twp_enable_normal_comment_box = get_post_meta($post->ID, 'twp_enable_normal_comment_box', true);
                            if( !$twp_enable_normal_comment_box ){
                                $true_news_default = true_news_get_default_theme_options();
                                $twp_enable_normal_comment_box = get_theme_mod( 'ed_toggle_comment',$true_news_default['ed_toggle_comment'] );
                            } ?>
                            <div class="twp-opt-wrap twp-checkbox-wrap">
                                <input id="twp-normal-comment" name="twp_enable_normal_comment_box" type="checkbox" <?php if ($twp_enable_normal_comment_box) { ?> checked="checked" <?php } ?> />
                                <label for="twp-normal-comment"><?php esc_html_e('Enable Collapse on Comment Box', 'true-news'); ?></label>
                            </div>
                            
                            <?php $twp_disable_ajax_load_next_post = get_post_meta($post->ID, 'twp_disable_ajax_load_next_post', true); ?>
                            <div class="twp-opt-wrap twp-opt-wrap-alt">

                                <label><b><?php esc_html_e( 'Navigation Type','true-news' ); ?></b></label>

                                <select name="twp_disable_ajax_load_next_post">
                                    <option <?php if( $twp_disable_ajax_load_next_post == '' || $twp_disable_ajax_load_next_post == 'global-layout' ){ echo 'selected'; } ?> value="global-layout"><?php esc_html_e('Global Layout','true-news'); ?></option>
                                    <option <?php if( $twp_disable_ajax_load_next_post == 'no-navigation' ){ echo 'selected'; } ?> value="no-navigation"><?php esc_html_e('Disable Navigation','true-news'); ?></option>
                                    <option <?php if( $twp_disable_ajax_load_next_post == 'norma-navigation' ){ echo 'selected'; } ?> value="norma-navigation"><?php esc_html_e('Next Previous Navigation','true-news'); ?></option>
                                    <option <?php if( $twp_disable_ajax_load_next_post == 'ajax-next-post-load' ){ echo 'selected'; } ?> value="ajax-next-post-load"><?php esc_html_e('Load Next 3 Posts Contents','true-news'); ?></option>
                                </select>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    <?php }
endif;

// Save metabox value.
add_action( 'save_post', 'true_news_save_post_meta' );

if( ! function_exists( 'true_news_save_post_meta' ) ):

function true_news_save_post_meta( $post_id ) {

    global $post;
    $post_type = '';
    if (isset($post->ID)) {
        $post_type = get_post_type($post->ID);
    }
    
    if ( !isset( $_POST[ 'true_news_post_meta_nonce' ] ) || !wp_verify_nonce( wp_unslash( $_POST['true_news_post_meta_nonce'] ), basename( __FILE__ ) ) )
        return;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
        return;
        
    if ( 'page' == wp_unslash( $_POST['post_type'] ) ) {  
        if ( !current_user_can( 'edit_page', $post_id ) )  
            return $post_id;  
    } elseif ( !current_user_can( 'edit_post', $post_id ) ) {  
            return $post_id;  
    }
    
    
    if( $post_type == 'post' ):

        $twp_enable_normal_comment_box_old = esc_html( get_post_meta( $post_id, 'twp_enable_normal_comment_box', true ) ); 
        $twp_enable_normal_comment_box_new = sanitize_text_field( wp_unslash( $_POST['twp_enable_normal_comment_box'] ) );
        if ( $twp_enable_normal_comment_box_new && $twp_enable_normal_comment_box_new != $twp_enable_normal_comment_box_old ) {  
            update_post_meta ( $post_id, 'twp_enable_normal_comment_box', $twp_enable_normal_comment_box_new );  
        } elseif ( '' == $twp_enable_normal_comment_box_new && $twp_enable_normal_comment_box_old ) {  
            delete_post_meta( $post_id,'twp_enable_normal_comment_box', $twp_enable_normal_comment_box_old );  
        }

        $twp_disable_ajax_load_next_post_old = esc_html( get_post_meta( $post_id, 'twp_disable_ajax_load_next_post', true ) ); 
        $twp_disable_ajax_load_next_post_new = true_news_sanitize_meta_pagination( wp_unslash( $_POST['twp_disable_ajax_load_next_post'] ) );
        if ( $twp_disable_ajax_load_next_post_new && $twp_disable_ajax_load_next_post_new != $twp_disable_ajax_load_next_post_old ) {  
            update_post_meta ( $post_id, 'twp_disable_ajax_load_next_post', $twp_disable_ajax_load_next_post_new );  
        } elseif ( '' == $twp_disable_ajax_load_next_post_new && $twp_disable_ajax_load_next_post_old ) {  
            delete_post_meta( $post_id,'twp_disable_ajax_load_next_post', $twp_disable_ajax_load_next_post_old );  
        }

    endif;

    $old = esc_html( get_post_meta( $post_id, 'true_news_post_sidebar_option', true ) ); 
    $new = true_news_sanitize_sidebar_option( wp_unslash( $_POST['true_news_post_sidebar_option'] ) );
    if ( $new && $new != $old ) {  
        update_post_meta ( $post_id, 'true_news_post_sidebar_option', $new );  
    } elseif ( '' == $new && $old ) {  
        delete_post_meta( $post_id,'true_news_post_sidebar_option', $old );  
    }


}
endif;   