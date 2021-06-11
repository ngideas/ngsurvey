<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing edit question aspects of the plugin.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="ngs">

	<div class="container-fluid pl-0">
		<div class="row">
			<div class="col my-3">
				<div class="d-flex flex-row">
					<div class="p-none">
						<h3 class="page-heading me-3"><span class="dashicons dashicons-admin-settings"></span> <?php echo esc_html__( 'NgSurvey Settings', 'ngsurvey' ); ?></h3>
					</div>
					<div class="p-none">
						
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<form name="ngForm" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" enctype="multipart/form-data" method="post">
                	<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                    	<?php foreach ( $data as $i => $extension ):?>
                    	<li class="nav-item" role="presentation">
                    		<a class="nav-link<?php echo $i == 0 ? ' active' : '';?>" 
                    			id="<?php echo esc_attr( $extension->id );?>-tab" 
                    			data-bs-toggle="tab" 
                    			href="#ngsurvey-settings-<?php echo esc_attr( $extension->id );?>" 
                    			role="tab" 
                    			aria-controls="ngsurvey-settings-<?php echo esc_attr( $extension->id );?>" 
                    			aria-selected="true"><?php echo wp_kses_post( $extension->title );?></a>
                    	</li>
                    	<?php endforeach;?>
                	</ul>
                	
                	<div class="tab-content" id="settingsTabContent">
                		<?php foreach ( $data as $i => $extension ):?>
                		<div class="tab-pane fade<?php echo $i == 0 ? ' show active' : '';?>" 
                			id="ngsurvey-settings-<?php echo esc_attr( $extension->id );?>" 
                			role="tabpanel" 
                			aria-labelledby="ngsurvey-settings-<?php echo esc_attr( $extension->id );?>-tab">
                			
                			<?php 
                			$extension->generate_settings_html();
                			?>
                			
                		</div>
                		<?php endforeach;?>
            		</div>
            		
            		<button type="button" class="btn btn-success btn-save-settings mt-4">
                		<span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Save Settings', 'ngsurvey' );?>
                	</button>
            	</form>
            </div>
		</div>
	</div>
	
	<div style="display: none;">
		<span id="lbl-save-settings-success"><?php echo esc_html__( 'Successfully applied the settings.', 'ngsurvey' ); ?></span>
	</div>
	<input type="hidden" name="ngpage" value="settings">
</div>