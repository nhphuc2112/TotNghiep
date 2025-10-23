<?php
   $job = get_post_meta( get_the_ID(), 'tbay_testimonial_job', true );
?>
<div class="testimonials-body media">
   <div class="testimonials-profile"> 
      <div class="wrapper-avatar">
         <div class=" testimonial-avatar tbay-image-loaded">
         <?php 
            $post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
            echo cena_tbay_get_attachment_image_loaded($post_thumbnail_id, 'widget');
         ?>
         </div>
      </div>
      <div class="testimonial-meta">
         <span class="name-client"> <?php the_title(); ?></span>
         <span class="job"><?php echo trim($job); ?></span>
      </div> 
   </div> 
   <div class="description media-body"><?php the_content() ?></div>
</div>