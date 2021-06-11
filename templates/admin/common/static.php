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

<div style="display: none;">
	<span id="lbl-confirm-delete-title"><?php echo esc_html__( 'Confirm delete?', 'ngsurvey' ); ?></span>
	<span id="lbl-confirm-delete-desc"><?php echo esc_html__( 'This action is irreversible and the data will be deleted permanently. Do you want to continue?', 'ngsurvey' ); ?></span>
	<span id="lbl-confirm-unpublish-title"><?php echo esc_html__( 'Confirm unpublishing?', 'ngsurvey' ); ?></span>
	<span id="lbl-confirm-unpublish-desc"><?php echo esc_html__( 'The survey will no longer available for the users. Are you sure you would like to unpublish this survey?', 'ngsurvey' ); ?></span>
	<span id="lbl-confirm"><?php echo esc_html__( 'Confirm', 'ngsurvey' ); ?></span>
	<span id="lbl-add-new"><?php echo esc_html__( 'Add New', 'ngsurvey' ); ?></span>
	<span id="lbl-delete"><?php echo esc_html__( 'Delete', 'ngsurvey' ); ?></span>
	<span id="lbl-unpublish"><?php echo esc_html__( 'Unpublish', 'ngsurvey' ); ?></span>
	<span id="lbl-cancel"><?php echo esc_html__( 'Cancel', 'ngsurvey' ); ?></span>
	<span id="lbl-import"><?php echo esc_html__( 'Import', 'ngsurvey' ); ?></span>
	<span id="lbl-uninstall"><?php echo esc_html__( 'Uninstall', 'ngsurvey' ); ?></span>
	<span id="lbl-delete-success"><?php echo esc_html__( 'The selected item successfully deleted.', 'ngsurvey' ); ?></span>
	<span id="lbl-export-csv"><?php echo esc_html__( 'Export to CSV', 'ngsurvey' );?></span>
	<span id="lbl-delete-selected"><?php echo esc_html__( 'Delete', 'ngsurvey' ); ?></span>
	<span id="title_copy_question"><?php echo esc_html__( 'Copy Question', 'ngsurvey' ); ?></span>
	<span id="title_move_question"><?php echo esc_html__( 'Move Question', 'ngsurvey' ); ?></span>
	<span id="title_add_new_page"><?php echo esc_html__( 'Add new page', 'ngsurvey' ); ?></span>
	<span id="title_change_page_title"><?php echo esc_html__( 'Edit page title', 'ngsurvey' ); ?></span>
	<span id="title_add_new_rule"><?php echo esc_html__( 'Add a rule', 'ngsurvey' ); ?></span>
	<span id="title_add_new_contact_group"><?php echo esc_html__( 'Add a Contact Group', 'ngsurvey' ); ?></span>
	<span id="text_prompt_page_title"><?php echo esc_html__( 'Please enter the title of the page.', 'ngsurvey' );?></span>
	<span id="text_prompt_rule_title"><?php echo esc_html__( 'Please enter the title of the rule.', 'ngsurvey' );?></span>
	<span id="text_select_the_page"><?php echo esc_html__( 'Please select the page.', 'ngsurvey' ); ?></span>
	<span id="text_prompt_contact_group_title"><?php echo esc_html__( 'Please enter the title of the contact group.', 'ngsurvey' );?></span>
	<span id="error_cannot_delete_only_page"><?php echo esc_html__( 'You cannot delete the only page available in this survey.', 'ngsurvey' )?></span>
	<span id="error_missing_required_value"><?php echo esc_html__( 'Please enter the value to proceed.', 'ngsurvey' ); ?></span>
	<span id="text_click_to_publish"><?php echo esc_html__( 'Click to publish', 'ngsurvey' ); ?></span>
	<span id="text_click_to_unpublish"><?php echo esc_html__( 'Click to unpublish', 'ngsurvey' ); ?></span>
	<span id="text_click_to_edit"><?php echo esc_html__( 'Click to edit', 'ngsurvey' ); ?></span>
</div>

<div style="position:absolute; top:-1000px;">
	<input type="file" name="input-file-upload" id="input-file-upload">
</div>
