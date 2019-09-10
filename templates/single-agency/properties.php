<?php
global $post;

$agency_id = get_the_ID(); 
$limit 	   = apply_filters( 'opalesate_agency_properties_limit', 5 ); 
$user_id   = get_post_meta( $agency_id, OPALESTATE_AGENCY_PREFIX . 'user_id', true ); 

$query 	   = Opalestate_Query::get_agency_property( $agency_id, $user_id , $limit );

 
if( $query->have_posts() ) :
	$id = rand(); 
?>
<div class="clearfix clear"></div>
<div class="opalestate-box-inner property-agency-section">
	<h4 class="box-heading hide"><?php echo sprintf( esc_html__('My Properties', 'opalestate-pro'),  $query->found_posts  );?></h4>
	<div class="opalestate-rows">
		<div class="<?php echo apply_filters('opalestate_row_container_class', 'opal-row');?>" id="<?php echo esc_attr( $id ); ?>">
			<?php while( $query->have_posts() ) : $query->the_post(); ?>
			  	<div class="col-lg-12 col-md-12 col-sm-12">
			  	 <?php echo opalestate_load_template_path( 'content-property-list-v2' ); ?>
			  	</div> 
			<?php endwhile; ?>	
		</div>
		<?php if( $query->max_num_pages > 1 ):   ?>
		<div class="w-pagination"><?php // echo  $query->max_num_pages; // opalestate_pagination(  $query->max_num_pages ); ?></div>
		<div class="opalestate-load-more text-center" data-post_id="<?php echo  $agency_id; ?>" data-action="get_agency_property" data-related="<?php echo esc_attr( $id ); ?>" data-numpage="<?php echo
        $query->max_num_pages; ?>" data-paged="2"> <button class="btn btn-primary btn-3d"> <?php esc_html_e('Load More', 'opalestate-pro'); ?></button></div>
		<?php endif; ?>

	</div>	
</div>	
<?php else : ?>
<div class="opalestate-message">
<?php esc_html_e( 'My Agency has not any property yet.', 'opalestate-pro' ) ;?>
</div>
<?php endif;  ?>

<?php 	wp_reset_postdata(); ?>
