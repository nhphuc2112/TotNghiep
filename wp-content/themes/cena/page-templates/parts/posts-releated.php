<?php
    $relate_count = cena_tbay_get_config('number_blog_releated', 3);
    $relate_columns = cena_tbay_get_config('releated_blog_columns', 3);
    $terms = get_the_terms( get_the_ID(), 'category' );
    $termids =array();

    if ($terms) {
        foreach($terms as $term) {
            $termids[] = $term->term_id;
        }
    }

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $relate_count,
        'post__not_in' => array( get_the_ID() ),
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $termids,
                'operator' => 'IN'
            )
        )
    );

    $relates = new WP_Query( $args );


    if( $relates->have_posts() ):
    
?>
    <div class="widget">
        <h4 class="widget-title">
            <span><?php esc_html_e( 'Related post', 'cena' ); ?></span>
        </h4>

        <div class="related-posts-content  widget-content">
            <div class="row">
            <?php
                $class_column = 12/$relate_columns;
                while ( $relates->have_posts() ) : $relates->the_post();
                    ?>
                    <div class="col-sm-<?php echo esc_attr( $class_column ); ?>">
                          <?php get_template_part( 'vc_templates/post/_single _carousel' ); ?>
                    </div>
                    <?php
                endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        
    </div>
<?php endif; ?>