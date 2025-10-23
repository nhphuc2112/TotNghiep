<?php 

$_id = cena_tbay_random_key();

?>

<div id="search-form-modal-<?php echo esc_attr($_id); ?>" class="search-form-modal">
	<div class="search-form">
		<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#searchformshow-<?php echo esc_attr($_id); ?>">
		  <i class="zmdi zmdi-search"></i>
		</button>
	</div>

	<div class="modal fade searchformshow" id="searchformshow-<?php echo esc_attr($_id); ?>" tabindex="-1" role="dialog" aria-labelledby="searchformlable-<?php echo esc_attr($_id); ?>">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="searchformlable-<?php echo esc_attr($_id); ?>"><?php esc_html_e('Products search form', 'cena'); ?></h4>
	      </div>
	      <div class="modal-body">
				<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
	      </div>
	    </div>
	  </div>
	</div>
</div>