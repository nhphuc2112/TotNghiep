<?php

class Tbay_Widget_Posts extends Tbay_Widget {
    public function __construct() {
        parent::__construct(
            'tbay_posts',
            esc_html__('Tbay Posts Widget', 'cena')
        );
        $this->widgetName = 'posts';
    }

    public function getTemplate() {
        $this->template = 'posts.php';
    }

    public function widget( $args, $instance ) {
        $this->display($args, $instance);
    }

    public function form( $instance ) {
        $defaults = array(
            'title' => 'List Posts',
            'layout' => 'default' ,
            'ids' => '',
            'class'=>''
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $posts = get_posts( array('orderby'=>'title','posts_per_page'=>-1) );
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php echo esc_html__( 'Title:', 'cena' ); ?></label>
            <br>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'ids' )); ?>"><?php echo esc_html__( 'Posts:', 'cena' ); ?></label>
            <br>
            <select multiple name="<?php echo esc_attr($this->get_field_name( 'ids' )); ?>[]" id="<?php echo esc_attr($this->get_field_id( 'ids' )); ?>">
               <?php foreach( $posts as $value ){ 
                    ?>
                    <option value="<?php echo esc_attr( $value->ID ); ?>" <?php selected($instance['ids'][0], $value->ID); ?>>
                        <?php echo esc_html( $value->post_title ); ?>
                    </option>
               <?php } ?>
            </select>
        </p>
<?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? $new_instance['layout'] : 'default';
        $instance['ids']    = ( ! empty( $new_instance['ids'] ) ) ? $new_instance['ids'] : '';

        return $instance;
    }
}