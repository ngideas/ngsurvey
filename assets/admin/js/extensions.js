(function(global, $) {
	'use strict';
    var module = NgSurveyApi.Extensions = {};
    
    module.init = function() {
		/** Initialize my extensions list */
		module.checkLicenses();
    };

	module.checkLicenses = function() {
		$('#ngs').on('click', '.btn-activate', function(){
			$('#license-activation-modal input[name="product_id"]').val($(this).data('id'));
			$('#license-activation-modal input[name="task"]').val('extensions.activate');
			$('#license-activation-modal').modal('show');
			$('#license-activation-modal form').removeClass('was-validated');
			$('#license-activation-modal form [name="license-key"]').val('');
		});
		
		$('#ngs').on('click', '.btn-activate-license', function(){
			var button = $(this);
			var form = $('#license-activation-modal form').addClass('was-validated');
			if (form.get(0).checkValidity() === false) {
				return false;
			}

			NgSurveyApi.Common.submitAjaxTask(button, {
				_ajax_nonce: ng_ajax.nonce,
				action: 'ngsa_ajax_handler',
				task: 'extensions.activate',
				product_id: form.find('[name="product_id"]').val(),
				license_email: form.find('[name="license-email"]').val(),
				license_key: form.find('[name="license-key"]').val()
			}, function(button, _formData) {
				button.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-update-alt spin');
				return true;
			}, function(button, _formData, responseData) {
				button.prop('disabled', false).find('.dashicons').attr('class', 'dashicons dashicons-yes-alt');
				$('#license-activation-modal').modal('hide');
				$('#ngs').html($(responseData.data).html());
			}, function(button, _formData, responseData) {
				button.prop('disabled', false).find('.dashicons').attr('class', 'dashicons dashicons-yes-alt');
				if (responseData && responseData.data) {
					Swal.fire({ icon: 'error', text: responseData.data[0].message });
				}
			});
		});

		$('#ngs').on('click', '.btn-deactivate-license', function(){
			var button = $(this);
			NgSurveyApi.Common.onConfirm({
				title: $('#lbl-confirm-deactivate-license-title').html(),
				html: $('#lbl-confirm-deactivate-license-desc').html(),
				cancelButtonText: $('#lbl-cancel').html(),
				confirmButtonText: $('#lbl-deactivate').html(),
				icon: 'warning'
			}, function() {
				NgSurveyApi.Common.submitAjaxTask(button, {
					_ajax_nonce: ng_ajax.nonce,
					action: 'ngsa_ajax_handler',
					task: 'extensions.deactivate',
					product_id: button.data('id')
				}, null, function(_button, _formData, responseData) {
					$('#ngs').html($(responseData.data).html());
					Swal.close();
				});
			});
		});
	};
})(this, jQuery);
