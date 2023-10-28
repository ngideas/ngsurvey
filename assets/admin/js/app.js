jQuery(document).ready(function($) {
	NgSurveyApi.Common.init();
	var module = $('input[name="ngpage"]').val();
	
	switch (module) {
		case 'form': NgSurveyApi.Form.init(); break;
		case 'reports': NgSurveyApi.Reports.init(); break;
		case 'extensions': NgSurveyApi.Extensions.init(); break;
		case 'settings': NgSurveyApi.Settings.init(); break;
		case 'metabox': NgSurveyApi.Metabox.init(); break;
		default: 
			if(NgSurveyApi.hasOwnProperty(module)) {
				var context = NgSurveyApi[module];
				NgSurveyApi.Common.executeFunctionByName('init', context);
			}
	}
});