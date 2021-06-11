(function(global, $) {
	'use strict';
    var module = NgSurveyApi.Settings = {};
    
    module.init = function() {
		$('#ngs .btn-save-settings').click(function(){
			var button = $(this);
			var formData = new FormData(button.closest('form')[0]);
			formData.append('_ajax_nonce', ng_ajax.nonce);
			formData.append('action', 'ngsa_ajax_handler');
			formData.append('task', 'settings.save');

			NgSurveyApi.Common.submitAjaxTask(button, formData, function(btn, _formData) {
				btn.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-update-alt spin');
				return true;
			}, function(btn, _formData, _responseData) {
				btn.prop('disabled', false).find('.dashicons').attr('class', 'dashicons dashicons-yes-alt');
				Swal.fire({ icon: "success", 'text': $('#lbl-save-settings-success').html() });
			});
		});
    };
})(this, jQuery);
