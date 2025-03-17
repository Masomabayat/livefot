<?php
/**
 * Custom Customizer Controls.
 *
 * @package True News
 */

/**
 * Customize Control for Taxonomy Select.
 *
 * @since 1.0.0
 *
 * @see WP_Customize_Control
 */

/** Customizer Custom Control **/
if ( class_exists( 'WP_Customize_Control' ) ) {
    
    // Radio Image Custom Control Class.
    class True_News_Custom_Radio_Image_Control extends WP_Customize_Control {

        public $type = 'radio-image';
    
        public function render_content() {
           
            if ( empty( $this->choices ) ) {
                return;
            }           
            
            $name = '_customize-radio-' . $this->id; ?>
            
            <span class="customize-control-title">
                <?php echo esc_html ( $this->label ); ?>
                <?php if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
                <?php endif; ?>
            </span>
            
            <div id="input_<?php echo esc_attr($this->id); ?>" class="image radio-image-buttenset">
                <?php foreach ( $this->choices as $value => $label ) : ?>
                    <input class="image-select" type="radio" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr($this->id) . esc_attr($value); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?>>
                        <label for="<?php echo esc_attr($this->id) . esc_attr($value); ?>">
                            <span class="dashicons <?php echo esc_html( $label ); ?>"></span>
                        </label>
                    </input>
                <?php endforeach; ?>
            </div>
            
        <?php }
    }
    
}

/**
 * Customize Control for Radio Image.
 *
 * @since 1.0.0
 *
 * @see WP_Customize_Control
 */
class True_News_Radio_Image_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'radio-image';

	/**
	 * Render content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {

		if (empty($this->choices)) {
			return;
		}

		$name = '_customize-radio-'.$this->id; ?>

		<label>
    		<?php if (!empty($this->label)):?>
                <span class="customize-control-title"><?php echo esc_html($this->label);?></span>
    		<?php endif;?>

            <?php if (!empty($this->description)):?>
                <span class="description customize-control-description"><?php echo esc_html($this->description);?></span>
            <?php endif;?>

            <?php foreach ($this->choices as $value => $label):?>
                <label>
                    <input type="radio" value="<?php echo esc_attr($value);?>" <?php $this->link(); checked($this->value(), $value); ?> class="np-radio-image" name="<?php echo esc_attr($name); ?>"/>
                    <span><img src="<?php echo esc_url($label);?>" alt="<?php echo esc_attr($value);?>"/></span>
                </label>
            <?php endforeach;?>

		</label>
		<?php
	}
}


/**
 * Upsell customizer section.
 *
 * @since  1.0.0
 * @access public
 */
class True_News_Customize_Section_Upsell extends WP_Customize_Section {

	/**
	 * The type of customize section being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'upsell';

	/**
	 * Custom button text to output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_text = '';

	/**
	 * Custom pro button URL.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_url = '';

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function json() {
		$json = parent::json();

		$json['pro_text'] = $this->pro_text;
		$json['pro_url']  = esc_url( $this->pro_url );

		return $json;
	}

	/**
	 * Outputs the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_template() { ?>

		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">

			<h3 class="accordion-section-title">
				{{ data.title }}

				<# if ( data.pro_text && data.pro_url ) { #>
					<a href="{{ data.pro_url }}" class="button button-secondary alignright" target="_blank">{{ data.pro_text }}</a>
				<# } #>
			</h3>
		</li>
	<?php }
}


/**
 * Repeater Custom Control
 * 
 * @package True News
*/

class True_News_Repeater_Controler extends WP_Customize_Control {
    /**
     * The control type.
     *
     * @access public
     * @var string
    */
    public $type = 'repeater';

    public $true_news_box_label = '';

    public $true_news_box_add_control = '';

    private $cats = '';

    /**
     * The fields that each container row will contain.
     *
     * @access public
     * @var array
     */
    public $fields = array();

    /**
     * Repeater drag and drop controler
     *
     * @since  1.0.0
     */
    public function __construct( $manager, $id, $args = array(), $fields = array() ) {
        $this->fields = $fields;
        $this->true_news_box_label = $args['true_news_box_label'] ;
        $this->true_news_box_add_control = $args['true_news_box_add_control'];
        $this->cats = get_categories(array( 'hide_empty' => false ));
        parent::__construct( $manager, $id, $args );
    }

    public function render_content() {

        $values = json_decode($this->value());
        ?>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

        <?php if($this->description){ ?>
            <span class="description customize-control-description">
            <?php echo wp_kses_post($this->description); ?>
            </span>
        <?php } ?>

        <ul class="true-news-repeater-field-control-wrap">
            <?php $this->true_news_get_fields(); ?>
        </ul>

        <input type="hidden" <?php esc_attr( $this->link() ); ?> class="true-news-repeater-collector" value="<?php echo esc_attr( $values_json ); ?>" />
        <?php
    }

    private function ToObject($Array) { 
      
        // Create new stdClass object 
        $object = new stdClass(); 
          
        // Use loop to convert array into 
        // stdClass object 
        foreach ($Array as $key => $value) { 
            if (is_array($value)) { 
                $value = $this->ToObject($value); 
            } 
            $object->$key = $value; 
        } 
        return $object; 
    } 

    private function true_news_get_fields(){

        $fields = $this->fields;

        $values_json = json_decode( $this->value() );

        if( count($values_json) == 10 ){
            
            $values_json[] = (object) array(
                'section_ed' => 'yes',
                'home_section_type' => 'advertise-area',
                'section_title' => '',
                'post_category' => '',
                'section_block_1_title_left' =>  '',
                'section_block_1_cat_left' =>  '',
                'section_block_1_title_1' =>  '',
                'section_block_1_cat_1' =>  '',
                'section_title_1' =>  '',
                'post_category_1' =>  '',
                'section_block_1_title_2' =>  '',
                'section_block_1_cat_2' =>  '',
                'section_title_2' =>  '',
                'post_category_2' =>  '',
                'post_category_3' =>  '',
                'post_category_4' =>  '',
                'section_block_1_title_3' =>  '',
                'section_block_1_cat_3' =>  '',
                'section_title_3' =>  '',
                'post_category_right_side' =>  '',
                'sidebar_layout' => '',
                'hide_popular_posts' =>  '',
                'hide_trending_posts' =>  '',
                'hide_slider_overlay'=>  '',
                'hide_post_category' =>  '',
                'hide_post_author_avatar' => '',
                'hide_post_author' =>  '',
                'hide_post_date' =>  '',
                'slider_autoplay' =>  '',
                'slider_dots' =>  '',
                'slider_arrows' =>  '',
                'advertise_image' =>  '',
                'advertise_link' => '',
                'advertise_script' =>  ''
            );

        }

        if( is_array( $values_json ) ){
            foreach($values_json as $value){ ?>

            <li class="true-news-repeater-field-control">

                <div class="title-rep-wrap">
                    <span class="dashicons dashicons-move twp-filter-icon"></span>
                    <h3 class="true-news-repeater-field-title"><?php echo esc_html( $this->true_news_box_label ); ?></h3>
                    <span class="dashicons dashicons-arrow-down twp-filter-icon"></span>
                </div>

                <div class="true-news-repeater-fields">
                    <?php
                    foreach ($fields as $key => $field) {
                        $class = isset($field['class']) ? $field['class'] : ''; ?>
                        <div class="true-news-fields true-news-type-<?php echo esc_attr($field['type']).' '. esc_attr($class); ?>">
                            
                            <?php 
                            $label = isset($field['label']) ? $field['label'] : '';
                            $description = isset($field['description']) ? $field['description'] : '';
                            if($field['type'] != 'checkbox'){ ?>
                                <span class="customize-control-title"><?php echo esc_html( $label ); ?></span>
                                <span class="description customize-control-description"><?php echo esc_html( $description ); ?></span>
                            <?php 
                            }

                            $new_value = isset($value->$key) ? $value->$key : '';
                            $default = isset($field['default']) ? $field['default'] : '';

                            switch ($field['type']) {
                                case 'text':
                                    echo '<input data-default="'.esc_attr($default).'" data-name="'.esc_attr($key).'" type="text" value="'.esc_attr($new_value).'"/>';
                                break;

                                case 'number':
                                    echo '<input data-default="'.esc_attr($default).'" data-name="'.esc_attr($key).'" type="number" value="'.absint($new_value).'"/>';
                                break;

                                case 'link':
                                    echo '<input data-default="'.esc_attr($default).'" data-name="'.esc_attr($key).'" type="text" value="'.esc_url($new_value).'"/>';
                                break;

                                case 'textarea':
                                    echo '<textarea data-default="'.esc_attr($default).'"  data-name="'.esc_attr($key).'">'.esc_textarea($new_value).'</textarea>';
                                break;

                                case 'select':
                                    $options = $field['options'];
                                    echo '<select  data-default="'.esc_attr($default).'"  data-name="'.esc_attr($key).'">';
                                        foreach ( $options as $option => $val )
                                        {
                                            printf('<option value="%s" %s>%s</option>', esc_attr($option), selected($new_value, $option, false), esc_html($val));
                                        }
                                    echo '</select>';
                                break;

                                case 'checkbox':
                                    echo '<label>';
                                    echo '<input data-default="'.esc_attr($default).'" value="'. esc_html($new_value).'" data-name="'.esc_attr($key).'" type="checkbox" '.checked($new_value, 'yes', false).'/>';
                                    echo esc_html( $label );
                                    echo '<span class="description customize-control-description">'.esc_html( $description ).'</span>';
                                    echo '</label>';
                                break;

                                case 'upload':
                                        $image_escaped = $image_class = "";
                                        if($new_value){ 
                                            $image_escaped = '<img src="'.esc_url($new_value).'" style="max-width:100%;"/>';    
                                            $image_class = ' hidden';
                                        }
                                        echo '<div class="twp-img-fields-wrap">';
                                        echo '<div class="attachment-media-view">';
                                        echo '<div class="placeholder'.esc_attr($image_class).'">';
                                        esc_html_e('No image selected', 'true-news');
                                        echo '</div>';
                                        echo '<div class="thumbnail thumbnail-image">';
                                        echo $image_escaped;
                                        echo '</div>';
                                        echo '<div class="actions clearfix">';
                                        echo '<button type="button" class="button twp-img-delete-button align-left">'.esc_html__('Remove', 'true-news').'</button>';
                                        echo '<button type="button" class="button twp-img-upload-button alignright">'.esc_html__('Select Image', 'true-news').'</button>';
                                        echo '<input data-default="'.esc_attr($default).'" class="upload-id" data-name="'.esc_attr($key).'" type="hidden" value="'.esc_attr($new_value).'"/>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';                          
                                break;

                                case 'colorpicker':
                                    echo '<input data-default="'.esc_attr($default).'" class="customizer-color-picker" data-alpha="true" data-name="'.esc_attr($key).'" type="text" value="'.esc_attr($new_value).'"/>';
                                break;

                                default:
                                break;
                            } ?>

                        </div>
                    <?php } ?>

                    <div class="clearfix true-news-repeater-footer">
                        <div class="alignright">
                        <a class="true-news-repeater-field-close" href="#close"><?php esc_html_e('Close', 'true-news') ?></a>
                        </div>
                    </div>
                </div>
            </li>

            <?php   
            }
        }
    }

}