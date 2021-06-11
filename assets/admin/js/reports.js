(function(global, $) {
	'use strict';
	
    var module = global.NgSurveyApi.Reports = {};

	/**
	 * Handler method to initialize all event handlers of reports and load the dashboard.
	 */
	module.init = function() {
		$.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing_spinner';
		window.chartColors = $.parseJSON($('#ng-bg-colors').text());

		$('#ngs .dashboard-stats a').off('click');
		$('#ngs .dashboard-stats a').click(function() {
			$($(this).attr('href')).tab('show');
		});

		// Load dashboard charts: countrywise responses chart
		module.drawCountrywiseResponsesChart('#countrywise-responses-chart');

		// Load dashboard charts: datewise responses chart
		module.drawDaywiseResponsesChart('#datewise-responses-chart');

		// Load dashboard charts: finished vs pending
		module.drawPendingFinishedChart('#finished-and-pending-count-chart');

		var chartTypes = ['locations', 'platforms', 'browsers', 'devices'];
		for (var i = 0; i < chartTypes.length; i++) {
			NgSurveyApi.Common.draw2dChart('#' + chartTypes[i] + '-bar-chart', 'bar');
			NgSurveyApi.Common.draw2dChart('#' + chartTypes[i] + '-pie-chart', 'doughnut');
		}

		// Consolidated survey report: expand and collapse all buttons
		$('#ngs #survey-reports').off('click', '#btn-collapse-all');
		$('#ngs #survey-reports').on('click', '#btn-collapse-all', function() {
			$('#questions .collapse').attr('data-bs-parent', '#questions').collapse('hide');
		});

		$('#ngs #survey-reports').off('click', '#btn-expand-all');
		$('#ngs #survey-reports').on('click', '#btn-expand-all', function() {
			$('#questions .collapse').removeAttr('data-bs-parent').collapse('show');
		});

		// Add event handler for tab change events
		$('#ngs #nav-reports a[data-bs-toggle="tab"]').off('shown.bs.tab');
		$('#ngs #nav-reports a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
			var button = $(this);
			if ($.trim($(button.attr('href') + ' .report-content').html())) {
				return true;
			}

			var columnsData = [];
			var buttons = [];
			var order = 0;

			switch (button.data('task')) {
				case 'responses.display':
					order = 6;
					columnsData = [
						{ 
							data: "1",
							targets: 0,
							searchable: false, 
							orderable: false,
							className: "dt-body-center",
							render: function (data, type, full, meta){
								return '<input type="checkbox" name="id[]" value="' + full.id + '">';
							} 
						},
						{ data: "display_name" },
						{ data: "created_date_gmt" },
						{ data: "finished_date_gmt" },
						{
							data: "finished",
							render: function(data, type, row) {
								if (type === 'display') {
									if (data == 1) {
										return '<span class="dashicons dashicons-yes-alt text-success" data-bs-toggle="tooltip" title="' + row.status + '"></span>';
									} else {
										return '<span class="dashicons dashicons-clock text-warning" data-bs-toggle="tooltip" title="' + row.status + '"></span>';
									}
								}
								return data;
							}
						},
						{
							data: "id",
							render: function(data, type, row) {
								if (type === 'display') {
									return row.result;
								}
								return data;
							}
						},
						{ data: "id" }
					];

					buttons = {
						dom: {
							button: {
								className: ''
							}
						},
						buttons: [
							{
								text: '<span class="dashicons dashicons-trash"></span> ' + $('#lbl-delete-selected').text(),
								className: 'btn btn-danger btn-sm ms-2 btn-delete-responses',
								enabled: false,
								action: function(e, dt, btn, config) {
									NgSurveyApi.Common.onConfirmDeleteItem(function() {
										var params = dt.ajax.params();
										var selected = new Array();
										$(button.attr('href') + ' .data-table tbody input[type="checkbox"]:checked').each(function(){
											selected.push($(this).val());
										});
										
										params.task = 'responses.delete';
										params.rid = selected;
	
										NgSurveyApi.Common.submitAjaxTask(btn, params, function(btn, formData) {
											btn.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-update-alt spin');
											return true;
										}, function(btn, formData, responseData) {
											dt.draw();
											Swal.fire({ icon: "success", 'text': $('#lbl-delete-success').html() });
											btn.prop('disabled', true).find('.dashicons').attr('class', 'dashicons dashicons-trash');
										});
									});
								}
							}
						]
					};
					
					break;

				case 'locations.display':
					order = 3;
					columnsData = [
						{ data: "country" },
						{ data: "state" },
						{ data: "city" },
						{ data: "responses" }
					];
					break;

				case 'platforms.display':
					order = 2;
					columnsData = [
						{ data: "platform_name" },
						{ data: "platform_version" },
						{ data: "responses" }
					];
					break;

				case 'browsers.display':
					order = 3;
					columnsData = [
						{ data: "browser_name" },
						{ data: "browser_version" },
						{ data: "browser_engine" },
						{ data: "responses" }
					];
					break;

				case 'devices.display':
					order = 3;
					columnsData = [
						{ data: "device_type" },
						{ data: "brand_name" },
						{ data: "model_name" },
						{ data: "responses" }
					];
					break;

				case 'consolidated.display':
					module.loadConsolidatedReport(button);
					break;
			}

			if ($(button.attr('href') + ' .data-table').length > 0) {
				var table = $( $(button.attr('href') + ' .data-table') );
				
				// Add data tables support for listings
				var dataTable = table.DataTable({
					processing: true,
					serverSide: true,
					deferRender: true,
					autoWidth: false,
					pageLength: 15,
					lengthMenu: [10, 15, 25, 50, 100, 500],
					order: [order, 'desc'],
					dom: 
						"<'d-flex flex-row form-inline mb-2'<'flex-grow-1 me-3'<'row row-cols-lg-auto g-3 align-items-center'fB>>l>" +
						"<'row'<'col-sm-12'tr>>" +
						"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
					buttons: buttons,
					ajax: {
						url: ng_ajax.ajax_url,
						type: "POST",
						data: {
							_ajax_nonce: ng_ajax.nonce,
							action: 'ngsa_ajax_handler',
							task: button.data('task'),
							ngform: {
								sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
							}
						}
					},
					columns: columnsData,
					language: {
						loadingRecords: '&nbsp;',
						processing: '<i class="spinner-border text-primary"></i>',
						sLengthMenu: "_MENU_",
						search: ""
					},
				});

				if(button.data('task') == 'responses.display') {
					// Handle click on "Select all" control
					table.find('.btn-select-all').on('click', function(){
						var rows = dataTable.rows({ 'search': 'applied' }).nodes();
						$('input[type="checkbox"]', rows).prop('checked', this.checked);
						$(button.attr('href') + ' .btn-delete-responses').removeClass('disabled').prop('disabled', !this.checked);
					});
					
					// Handle click on checkbox to set state of "Select all" control
					table.on('change', 'tbody input[type="checkbox"]', function(){
						if(!this.checked){
							var el = table.find('.btn-select-all').get(0);
							
							// If "Select all" control is checked and has 'indeterminate' property
							if(el && el.checked && ('indeterminate' in el)){
								// Set visual state of "Select all" control as 'indeterminate'
								el.indeterminate = true;
							}
						}
						
						var disabled = table.find('tbody input[type="checkbox"]:checked').length == 0;
						$(button.attr('href') + ' .btn-delete-responses').removeClass('disabled').prop('disabled', disabled);
					});
				}
				
				// Init all registered integrations to update the table
				var types = Object.keys(NgSurveyApi);
				if(types && types.length > 0) {
					for(var i = 0; i < types.length; i++) {
						if(types[i].indexOf('Report') > 0 ) {
							var context = NgSurveyApi[types[i]];
							NgSurveyApi.Common.executeFunctionByName('initReports', context, dataTable, button.data('task'));
						}
					}
				}				
			}
		});

		// View response button handler
		$('#ngs #survey-reports').off('click', '.btn-view-response');
		$('#ngs #survey-reports').on('click', '.btn-view-response', function() {
			var button = $(this);
			NgSurveyApi.Common.submitAjaxTask(button, {
				_ajax_nonce: ng_ajax.nonce,
				action: 'ngsa_ajax_handler',
				task: 'response.display',
				ngform: {
					sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
					rid: button.data('id'),
				},
			}, function(button, formData) {
				var modal = $(button.data('bs-target'));
				modal.find('.modal-body').html($('#ng-loader').html());
				modal.modal('show');
				return true;
			}, function(button, formData, responseData) {
				var container = $(button.data('bs-target')).find('.modal-body');
				container.html(responseData.data);

				// Trigger plugin handler
				$(container).find('.question').each(function() {
					var question = $(this);
					var questionType = question.find('input[name="ngform[qtype]"]').val();
					questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);
	
					if(NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
						var context = NgSurveyApi[questionType + 'Question'];
						if(context.hasOwnProperty('initResults')) {
							NgSurveyApi.Common.executeFunctionByName('initResults', context, question);
						}
					}
				})
			}, function(button, formData, responseData) {
				$(button.data('bs-target')).modal('hide');
				Swal.fire({ icon: 'error', text: responseData.data[0].message });
			});
		});
		
		$('#ngs').off('click', '.btn-close-modal');
		$('#ngs').on('click', '.btn-close-modal', function(){
			$(this).closest('.modal').modal('hide');
		});
		
		// Load comments when the view comments button is clicked
		$('#ngs #survey-reports').off('click', '.btn-view-comments');
		$('#ngs #survey-reports').on('click', '.btn-view-comments', function() {
			var button = $(this);
			NgSurveyApi.Common.submitAjaxTask(button, {
				_ajax_nonce: ng_ajax.nonce,
				action: 'ngsa_ajax_handler',
				task: 'reports.get_custom_answers',
				ngform: {
					sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
					qid: button.data('id'),
				},
			}, function(button, formData) {
				var modal = $(button.data('bs-target'));
				modal.find('.modal-body').html($('#ng-loader').html());
				modal.modal('show');
				return true;
			}, function(button, formData, responseData) {
				$(button.data('bs-target')).find('.modal-body').html(responseData.data);
			});
		});
	};
	
	module.loadConsolidatedReport = function(button) {
		NgSurveyApi.Common.submitAjaxTask(button, {
			_ajax_nonce: ng_ajax.nonce,
			action: 'ngsa_ajax_handler',
			task: 'consolidated.display',
			ngform: {
				sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
			}
		}, function(btn, formData) {
			$(btn.attr('href')).html($('#ng-loader').html());
			return true;
		}, function(btn, formData, responseData) {
			var container = btn.attr('href');
			$(container).html(responseData.data);
			NgSurveyApi.Common.applyColors(container);

			// Trigger plugin handler
			$(container).find('.question').each(function() {
				var question = $(this);
				var questionType = question.find('input[name="ngform[qtype]"]').val();
				questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);

				if(NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
					var context = NgSurveyApi[questionType + 'Question'];
					if(context.hasOwnProperty('initReport')) {
						NgSurveyApi.Common.executeFunctionByName('initReport', context, question);
					}
				}
			})
		});
	};

	module.drawCountrywiseResponsesChart = function(selector) {
		var canvas = $(selector);
		var ctx = canvas.get(0).getContext('2d');

		$.getJSON(ng_ajax.assets_url + "/world-countries-sans-antarctica.json", function(data) {
			var coutryData = $.parseJSON($(canvas.data('data')).text());
			var countries = ChartGeo.topojson.feature(data, data.objects.countries).features;

			for (var i = 0; i < countries.length; i++) {
				countries[i].properties.value = 0;
				for (var j = 0; j < coutryData.length; j++) {
					if (countries[i].properties['Alpha-2'] == coutryData[j].country) {
						countries[i].properties.value = Number(coutryData[j].responses);
						break;
					}
				}
			}

			new Chart(ctx, {
				type: 'choropleth',
				data: {
					labels: countries.map(function(d) {
						return d.properties.name
					}),
					datasets: [{
						label: canvas.data('title'),
						data: countries.map(function(d) { 
							return { feature: d, value: d.properties.value };
						}),
					}]
				},
				options: {
					showOutline: true,
					showGraticule: true,
					plugins: {
						legend: {
							display: false,
						},
						title: {
							display: false,
						}
					},
					scales: {
						xy: {
							projection: 'equirectangular',
						}
					},
					geo: {
						colorScale: {
							display: false
						},
					},
					layout: {
						padding: {
							left: 0,
							right: 0,
							top: 0,
							bottom: 0
						}
					}
				}
			});
		});
	};

	module.drawDaywiseResponsesChart = function(selector) {
		var canvas = $(selector);
		var ctx = canvas.get(0).getContext('2d');
		var color = Chart.helpers.color;
		var data = $.parseJSON($(canvas.data('data')).text());
		var chartLabels = new Array();
		var chartValues = new Array();

		for (var i = 0; i < data.length; i++) {
			chartLabels.push(new Date(data[i].cdate).valueOf())
			chartValues.push(data[i].responses);
			//chartData.push({
			//	t: new Date(data[i].cdate).valueOf(),
			//	y: data[i].responses
			//})
		}

		new Chart(ctx, {
			type: 'line',
			data: {
				labels: chartLabels,
				datasets: [{
					label: canvas.data('title'),
					backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
					borderColor: window.chartColors.red,
					data: chartValues,
					fill: false,
					lineTension: 0,
					borderWidth: 2
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: true,
				animation: {
					duration: 0
				},
				plugins: {
					legend: {
						display: false,
					},
					title: {
						display: true,
						text: canvas.data('title')
					}
				},
				scales: {
					x: {
						type: 'time',
						distribution: 'series',
						display: true,
						ticks: {
							major: {
								enabled: true,
								fontStyle: 'bold'
							},
							source: 'data',
							autoSkip: true,
							autoSkipPadding: 75,
							maxRotation: 0,
							sampleSize: 100
						}
					},
					y: {
						beginAtZero: false,
						display: true,
						ticks: {
							stepSize: 1
						}
					}
				},
				tooltips: {
					intersect: false,
					mode: 'index',
					callbacks: {
						label: function(tooltipItem, myData) {
							var label = myData.datasets[tooltipItem.datasetIndex].label || '';
							if (label) {
								label += ': ';
							}
							label += parseFloat(tooltipItem.value).toFixed(2);
							return label;
						}
					}
				}
			}
		});
	};

	module.drawPendingFinishedChart = function(selector) {
		var canvas = $(selector);
		var ctx = canvas.get(0).getContext('2d');
		var data = $.parseJSON($(canvas.data('data')).text());
		var chartData = new Array();
		var chartLabels = new Array();

		for (var i = 0; i < data.length; i++) {
			chartData.push(data[i].value);
			chartLabels.push(data[i].label);
		}

		var config = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: chartData,
					backgroundColor: [
						window.chartColors.blue,
						window.chartColors.red
					],
					label: canvas.data('title')
				}],
				labels: chartLabels
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						position: 'bottom',
					},
					title: {
						display: true,
						text: canvas.data('title')
					},
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		};

		new Chart(ctx, config);
	};
})(this, jQuery);
