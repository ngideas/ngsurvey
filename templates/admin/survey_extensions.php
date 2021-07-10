<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing survey extensions aspects of the plugin.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
    exit;
}

$template   = $data->template;
$groups     = $data->extensions;
?>
<div id="ngs">
	<nav class="mt-4 mb-3">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<a class="nav-link active" id="nav-plugins-tab" data-bs-toggle="tab" href="#nav-plugins" role="tab" aria-controls="nav-plugins" aria-selected="true">
				<?php echo esc_html__( 'My Extensions', 'ngsurvey' );?>
			</a>
			<a class="nav-link" id="nav-extensions-tab" data-bs-toggle="tab" href="#nav-extensions" role="tab" aria-controls="nav-extensions" aria-selected="false">
				<?php echo esc_html__( 'Get Extensions', 'ngsurvey' );?>
			</a>
		</div>
	</nav>
	
	<div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-plugins" role="tabpanel" aria-labelledby="nav-plugins-tab">
			<?php if( empty( $data->plugins ) ):?>
			<div class="card card-default">
				<div class="card-body">
					<?php echo esc_html__( 'No extensions are installed. <a href="https://ngideas.com/extensions/" target="_blank">Get extensions here</a>.'); ?>
				</div>
			</div>
			<?php else: ?>
        	<table class="table table-striped table-hover">
        		<thead>
        			<th><?php echo esc_html__( 'Plugin', 'ngsurvey' );?></th>
        			<th><?php echo esc_html__( 'Description', 'ngsurvey' );?></th>
        			<th><?php echo esc_html__( 'License Status', 'ngsurvey' );?></th>
        		</thead>
        		<tbody>
                	<?php foreach ( $data->plugins as $plugin )
                	{
                		$key = array_search( $plugin[ 'NgSurvey ID' ], array_column( $data->licenses, 'product_id' ) );
	                	?>
	                	<tr>
	                		<td class="plugin-title column-primary"">
	                			<strong><?php echo $plugin[ 'Name' ];?></strong>
	                		</td>
	                		<td>
	                			<div class="plugin-description">
	            					<p><?php echo $plugin[ 'Description' ];?></p>
	            					<p>
	            						<?php echo esc_html__( 'Version', 'ngsurvey' )?>: <?php echo esc_html( $plugin[ 'Version' ] );?> |
	            						
	            						<?php if( isset( $plugin[ 'AuthorURI' ] ) && isset( $plugin[ 'Author' ] ) ):?>
	            						<?php echo esc_html__( 'By', 'ngsurvey' )?>: <a href="<?php echo esc_attr( $plugin[ 'AuthorURI' ] )?>" target="_blank"><?php echo esc_html( $plugin[ 'Author' ] );?></a> |
	            						<?php endif;?>
	            						
	            						<?php if( isset( $plugin[ 'PluginURI' ] ) ):?>
	            						<a href="<?php echo esc_attr( $plugin[ 'PluginURI' ] );?>" target="_blank"><?php echo esc_html__( 'Visit plugin site', 'ngsurvey' );?></a>
	            						<?php endif;?>
	            						
	            						<?php if( $key !== false ) :?>
	            						| <?php echo sprintf( esc_html__( 'License Key: %s', 'ngsurvey' ), str_repeat('*', 6 ) . substr( $data->licenses[ $key ][ 'license_key' ], -8 ) );?>
	            						<?php endif;?>
	            					</p>
	            				</div>
	                		</td>
	                		<td>
	                			<?php if( $key !== false ) :?>
	                			<div class="text-success mb-2">
	                				<span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Activated', 'ngsurvey' );?>
	                			</div>
	                			<a href="javascript:void(0);" class="text-danger btn-deactivate-license" data-id="<?php echo esc_attr( $plugin[ 'NgSurvey ID' ] );?>">
	                				<span class="dashicons dashicons-dismiss"></span> <?php echo esc_html__( 'Remove License', 'ngsurvey' ); ?>
	                			</a>
	                			<?php else:?>
	                			<a href="javascript:void(0);" class="text-primary btn-activate" data-id="<?php echo esc_attr( $plugin[ 'NgSurvey ID' ] );?>">
	                				<span class="dashicons dashicons-shield"></span> <?php echo esc_html__( 'Activate', 'ngsurvey' ); ?>
	                			</a>
	                			<?php endif;?>
	                		</td>
	                	</tr>
	                	<?php 
                	}
                	?>
        		</tbody>
        	</table>
        	<?php endif;?>
        </div>
		<div class="tab-pane fade" id="nav-extensions" role="tabpanel" aria-labelledby="nav-extensions-tab">
        	<?php foreach ($groups[ 'groups' ] as $group):?>
        	<div class="products-group mb-5">
            	<h4 class="page-heading"><span class="<?php echo esc_attr( $group['icon'] );?>"></span> <?php echo esc_html__( $group['title'], 'ngsurvey' ); ?></h4>
            	<hr>
            	<div id="product-list" class="container-fluid">
            		<div class="row row-cols-1 row-cols-xl-6 row-cols-lg-4 row-cols-md-3 g-3">
            			<?php foreach ( $group[ 'products' ] as $product ):?>
            			<div class="col mb-3">
            				<div class="card h-100">
            					<img src="<?php echo esc_attr( $product[ 'image' ] );?>" class="card-img-top" alt="<?php echo esc_attr( $product[ 'title' ] );?>">
            					<div class="card-body border-top border-1">
            						<h5 class="card-title"><?php echo esc_html( $product[ 'title' ] );?></h5>
            						<p class="card-text"><?php echo wp_kses_post( $product[ 'description' ] );?></p>
            					</div>
            					<div class="card-footer">
            						<a href="<?php echo esc_attr( $product[ 'url' ] );?>" target="_blank"><?php echo esc_html__( 'Learn more', 'ngsurvey' );?></a>
            					</div>
            				</div>
            			</div>
            			<?php endforeach;?>
            		</div>
            	</div>
            </div>
        	<?php endforeach;?>
        	
        	<input type="hidden" name="ngpage" value="extensions">
        	<?php $template->get_template_part( 'admin/common/static' );?>
        	<?php $template->get_template_part( 'public/common/loader' );?>
        </div>
        
        <div class="modal fade" id="license-activation-modal" tabindex="-1" role="dialog" aria-labelledby="license-activation-modal-label" aria-hidden="true">
    		<div class="modal-dialog">
    			<div class="modal-content">
    				<div class="modal-header">
    					<h5 class="modal-title"><?php echo esc_html__( 'Activate License', 'ngsurvey' );?></h5>
    					<button type="button" class="close" data-bs-dismiss="modal" aria-label="<?php echo esc_html__( 'Close', 'ngsurvey' );?>">
    						<span aria-hidden="true">&times;</span>
    					</button>
    				</div>
    				<div class="modal-body">
    					<form action="index.php" name="license-activation-form" method="post" class="needs-validation" novalidate>
        					<div class="mb-3">
        						<label for="license-email" class="form-label"><?php echo esc_html__( 'Email address', 'ngsurvey' );?>:</label>
        						<input type="email" class="form-control" name="license-email" id="license-email" value="" required="required" placeholder="<?php echo esc_attr__( 'Enter email address', 'ngsurvey' );?>">
        						<small id="help_license-email" class="form-text text-muted"><?php echo esc_html__( 'Enter the email id used for the purchase', 'ngsurvey' );?></small>
        					</div>
        					<div class="mb-3">
        						<label for="license-key" class="form-label"><?php echo esc_html__( 'License Key', 'ngsurvey' );?>:</label>
        						<input type="text" class="form-control" name="license-key" id="license-key" placeholder="<?php echo esc_attr__( 'Enter license key', 'ngsurvey' );?>" required="required">
        						<small id="help_license-key" class="form-text text-muted"><?php echo esc_html__( 'Enter the license key', 'ngsurvey' );?></small>
        					</div>
        					
        					<input type="hidden" name="product_id" value="">
        					<input type="hidden" name="task">
    					</form>
    				</div>
    				<div class="modal-footer">
    					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo esc_html__( 'Close', 'ngsurvey' );?></button>
    					<button type="button" class="btn btn-primary btn-activate-license"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Activate', 'ngsurvey' );?></button>
    				</div>
    			</div>
    		</div>
    	</div>
    	
    	<div style="display: none;">
    		<span id="lbl-confirm-deactivate-license-title"><?php echo esc_html__( 'Confirm deactivate?', 'ngsurvey' );?></span>
    		<span id="lbl-confirm-deactivate-license-desc"><?php echo esc_html__( 'Are you sure you would like deactivate the license from this site? You can reactivate it on this site or another site later.', 'ngsurvey' );?></span>
    		<span id="lbl-deactivate"><?php echo esc_html__( 'Deactivate', 'ngsurvey' );?></span>
    	</div>
	</div>
</div>
