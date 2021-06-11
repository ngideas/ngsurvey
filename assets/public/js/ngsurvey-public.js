(function( $ ) {
	'use strict';
	
	var rule_operators = {
        'equal':           {'accept_values': true,  'apply_to': ['string', 'integer', 'number', 'datetime']},
        'not_equal':       {'accept_values': true,  'apply_to': ['string', 'integer', 'number', 'datetime']},
        'in':              {'accept_values': true,  'apply_to': ['string', 'integer', 'number', 'datetime']},
        'not_in':          {'accept_values': true,  'apply_to': ['string', 'integer', 'number', 'datetime']},
        'less':            {'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'less_or_equal':   {'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'greater':         {'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'greater_or_equal':{'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'between':         {'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'not_between':     {'accept_values': true,  'apply_to': ['integer', 'number', 'datetime']},
        'begins_with':     {'accept_values': true,  'apply_to': ['string']},
        'not_begins_with': {'accept_values': true,  'apply_to': ['string']},
        'contains':        {'accept_values': true,  'apply_to': ['string']},
        'not_contains':    {'accept_values': true,  'apply_to': ['string']},
        'ends_with':       {'accept_values': true,  'apply_to': ['string']},
        'not_ends_with':   {'accept_values': true,  'apply_to': ['string']},
        'is_empty':        {'accept_values': false, 'apply_to': ['string']},
        'is_not_empty':    {'accept_values': false, 'apply_to': ['string']},
        'is_null':         {'accept_values': false, 'apply_to': ['string', 'integer', 'number', 'datetime']},
        'is_not_null':     {'accept_values': false, 'apply_to': ['string', 'integer', 'number', 'datetime']}
    };

	var needs_array = ['in', 'not_in', 'between', 'not_between'];

	/**
	 * Used to load the handlers for supporting user response page.
	 * Attaches the events to the main container to trigger when elements are loaded in the future.
	 * Any event which cannot be attached in the future, will be triggered on demand with initResponseElements
	 */
	NgSurveyApi.initResponse = function() {
		// Initialize the one time load events of the initial page
		$('#ngs .survey-form').each(function(){
			NgSurveyApi.initResponsePageElements($(this));
			NgSurveyApi.attachValidators($(this));
		});
		
		// Event handler to update Net Promoter Score value to its attached input
		$('#ngs').on('shown.bs.tab', '.nps-values .nps-value', function(){
			$($(this).data('input')).val($(this).data('value')).trigger('change');
		});
		
		// Event handler to save the response
		$('#ngs').on('click', '#survey-form .btn-save-response', function() {
			var button = $(this);
			if(!button.closest('form').valid()) {
				return false;
			}
			
			var formData = new FormData(button.closest('form')[0]);
			formData.append('_ajax_nonce', ng_ajax.nonce);
			formData.append('action', 'ngsurvey_ajax_handler');
			formData.append('task', 'survey.save');
			
			NgSurveyApi.Common.submitAjaxTask(button, formData, function(button, formData) {
				if(NgSurveyApi.hooks.length > 0) {
					for(var i = 0; i < NgSurveyApi.hooks.length; i++) {
						if(!NgSurveyApi.hasOwnProperty(NgSurveyApi.hooks[i])) {
							continue;
						}
						var context = NgSurveyApi[NgSurveyApi.hooks[i]];
						if(!context.execute(formData)) {
							return false;
						}
					}
				}
				
				button.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-update-alt spin');
				return true;
			}, function(button, formData, responseData) {
				var container = button.closest('form');
				button.prop('disabled', false).find('.dashicons').attr('class', 'dashicons dashicons-yes-alt');
				
				if($(responseData.data).find('#survey-form').length > 0) {
					container.hide().html($(responseData.data).find('#survey-form').html()).fadeIn('slow');
				} else {
					container.hide().html(responseData.data).fadeIn('slow');
				}
				if(container.find('.questions').length > 0) {
					NgSurveyApi.initResponsePageElements(container);
				}

				container.find('.survey-response-preform').remove();
			}, function(buttonObj, formData, data) {
				button.prop('disabled', false).find('.dashicons').attr('class', 'dashicons dashicons-yes-alt');
				
				if(data && data.data) {
					Swal.fire({icon: 'error', text: data.data[0].message});
				}
			});
		});
	};

	/**
	 * Method triggers the one time load events on each page load
	 */
	NgSurveyApi.initResponsePageElements = function(container) {
		container.find('.sortable').each(function(){
			$(this).sortable();
		});

		if(container.find('#conditional_rules').length > 0) {
			var conditionalRules = $.parseJSON(container.find('#conditional_rules').text());

			container.find('input, select, textarea').change(function(){
				for(var i = 0; i < conditionalRules.length; i++) {
					var ruleActions = $.parseJSON(conditionalRules[i].rule_actions);
					if(ruleActions.action != 'show_question' && ruleActions.action != 'hide_question') {
						continue;
					}

					var ruleContent = $.parseJSON(conditionalRules[i].rule_content);
					var isValid = NgSurveyApi.validateConditionalRules(ruleContent);

					// Show question if the rule is valid, else hide
					if(ruleActions.action == 'show_question') {
						$('#question-' + ruleActions.question).toggle(isValid);
					}

					// Hide question if the rule is valid else show
					if(ruleActions.action == 'hide_question') {
						$('#question-' + ruleActions.question).toggle(!isValid);
					}

					// Now unset values of hidden question if any
					if( $('#question-' + ruleActions.question).is(':hidden') ) {
						$('#question-' + ruleActions.question).find(':checked, :selected').prop('checked', false).prop('selected', false);
						$('#question-' + ruleActions.question).find('input, textarea').not(':checkbox, :radio').val('');
					}
				}
			});
			
			container.find('input, select, textarea').trigger('change');
		}

		// Trigger plugin handler
		container.find('.questions .question').each(function(){
			var questionType = $(this).data('type');
			questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);

			if(NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
				var context = NgSurveyApi[questionType + 'Question'];
				if(context.hasOwnProperty('initResponse')) {
					NgSurveyApi.Common.executeFunctionByName('initResponse', context, $(this));
				}
			}
		})
	};
	
	NgSurveyApi.validateConditionalRules = function(rulesGroup) {
		var isOr = rulesGroup.hasOwnProperty('condition') == 'OR' ? true : false;
		var isValid = isOr ? false : true; // default false for OR, true for AND condition
		
		if(rulesGroup.rules.length == 0) {
			return false;
		}

		for(var i = 0; i < rulesGroup.rules.length; i++) {
			if(rulesGroup.rules[i].hasOwnProperty('condition')) {
				var groupResult = NgSurveyApi.validateConditionalRules(rulesGroup.rules[i]);
				isValid = isOr ? groupResult || isValid : groupResult && isValid;
			} else {
				var result = NgSurveyApi.validateConditionalRulesGroup(rulesGroup.rules[i]);
				isValid = isOr ? result || isValid : result && isValid;
			}
			
			// no need to proceed for further elements if 
			// it is OR operation and isValid is true or if it is AND operation and isValid is false
			if( (isOr && isValid) || (!isOr && !isValid) ) {
				break;
			}
		}

		return isValid;
	};

	NgSurveyApi.validateConditionalRulesGroup = function(rule) {
		var valid = rule_operators[rule.operator].accept_values ? false : true;
		if(!rule_operators.hasOwnProperty(rule.operator) || $.inArray(rule.type, rule_operators.apply_to) >= 0 || (rule_operators[rule.operator].accept_values && !rule.value)) {
			return false;
		}

		if($.inArray(rule.operator, needs_array) >= 0) {
			if(rule_operators[rule.operator].accept_values) {
				for(var i = 0; i < rule.value.length; i++) {
					if(typeof rule.value[i] === 'number' || rule.value[i].indexOf('_') < 0) {
						$('[name="ngform[answers]['+rule.id+'][response][]"]:checked,[name="ngform[answers]['+rule.id+'][response][]"]:selected').each(function(){
							if( $(this).val() == rule.value[i] ) {
								valid = true;
								return false; // break each
							}
						});
					} else if(rule.value[i].indexOf('_') > 0){
						var answers = rule.value[i].split('_');
	
						$('[name="ngform[answers]['+rule.id+'][response]['+answers[0]+'][]"]:checked, [name="ngform[answers]['+rule.id+'][response]['+answers[0]+'][]"]:selected').each(function(){
							if( $(this).val() == answers[1]) {
								valid = true;
								return false; // break each
							}
						});
					}
					
					if( (!rule_operators[rule.operator].accept_values && !valid) || (rule_operators[rule.operator].accept_values && valid) ) {
						break;
					}
				}
			} else if($('[name^="ngform[answers]['+rule.id+'][response]"]:selected').length > 0 || $('[name^="ngform[answers]['+rule.id+'][response]"]:checked').length > 0) {
				valid = false;
			}
		} else {

			switch(rule.type) {
				case 'integer':
				case 'number':
					if( rule_operators[rule.operator].accept_values ) {
						if(rule.value && rule.value.indexOf('_') > 0) {
							var answers = rule.value.split('_');
		
							$('[name="ngform[answers]['+rule.id+'][response]['+answers[0]+'][]"]:checked, [name="ngform[answers]['+rule.id+'][response]['+answers[0]+'][]"]:selected').each(function(){
								if( $(this).val() == answers[1]) {
									valid = true;
									return false; // break each
								}
							});
						} else {
							$('[name="ngform[answers]['+rule.id+'][response][]"]:checked,[name="ngform[answers]['+rule.id+'][response][]"]:selected').each(function(){
								if( $(this).val() == rule.value ) {
									valid = true;
									return false; // break each
								}
							});
						}
					} else if($('[name^="ngform[answers]['+rule.id+'][response]"]:selected').length > 0 || $('[name^="ngform[answers]['+rule.id+'][response]"]:checked').length > 0) {
						valid = false;
					}
					break;
				
				case 'datetime':
				case 'string':
					if( 
						(!rule_operators[rule.operator].accept_values && !$('[name="ngform[answers]['+rule.id+'][response][]"]').val()) || 
						(rule_operators[rule.operator].accept_values && $('[name="ngform[answers]['+rule.id+'][response][]"]').val() == rule.value) ) {
						valid = true;
					}
					break; 
			}
		}

		return valid;
	};
	
	NgSurveyApi.attachValidators = function(form) {
		$.validator.addMethod("data-regex", function(value, element, param) {
			if ( this.optional( element ) ) {
				return true;
			}
			if ( typeof param === "string" ) {
				param = new RegExp( "^(?:" + param + ")$" );
			}
			return param.test( value );
		}, function(params, element) {
			return $(element).data('message');
		});

		form.validate({
			ignore: '.hideme:hidden input, .hideme:hidden select, .hideme:hidden textarea',
			errorPlacement: function(error, element) {
				if(element.closest('.question').find('label.error:not(:empty)').length == 0) {
					error.appendTo( element.closest('.question').find('.validation-messages') );
				}
			}, invalidHandler: function(event, validator) {
				form.find('.validation-error-message').show();
			}, success: function(label) {
				label.remove();
				if(form.find('label.error').length == 0) {
					form.find('.validation-error-message').hide();
				}
			}, highlight: function (element, errorClass, validClass) {
		        $(element).addClass('is-invalid');
		    }, unhighlight: function (element, errorClass, validClass) {
		        $(element).removeClass('is-invalid');
		    }
		});

		form.on('change', 'input, select, textarea', function(){
			$(this).valid();
		});
	};
})( jQuery );

jQuery( document ).ready( function ( $ ) {
	NgSurveyApi.initResponse();
});
