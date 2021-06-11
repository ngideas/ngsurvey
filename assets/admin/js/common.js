(function(global, $) {
	'use strict';

    var NgSurveyApi = {form_submitted: false, countries: [], hooks: []};
    var module = NgSurveyApi.Common = {};
    global.NgSurveyApi = NgSurveyApi;
    
    module.init = function() {
    	$('body').on('mouseover', '[data-bs-toggle="tooltip"]', function(){
			this.show = null; this.hide = null;
			$(this).tooltip('show');
		});
		
		$('#ngs').on('change', '.custom-file-input', function(){
			$(this).next().html($(this).val());
		});
    };
    
    module.applyColors = function(selector) {
		var colors = Object.keys(window.chartColors).map(function(key) { return window.chartColors[key] });
		var total = colors.length;

		$(selector).find('[data-ng-color]').each(function() {
			var index = $(this).data('ng-color');
			$(this).css('background-color', colors[index % total]);
		});
	};

	// Editor handling methods
	module.initializeEditor = function(editorId, addMediaButtons) {
		wp.editor.remove(editorId);
		wp.editor.initialize(editorId, {
			tinymce: {
				wpautop: false,
				plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
				toolbar1: 'formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more | spellchecker'
			},
			quicktags: true,
			mediaButtons: addMediaButtons
		});
	};

	module.removeEditor = function(editorId) {
		wp.editor.remove(editorId);
	};

	module.getEditorContent = function(editorId) {
		return wp.editor.getContent(editorId);
	};

	module.refreshEditor = function() {
		if (typeof tinyMCE != 'undefined') {
			tinyMCE.triggerSave(true, true);
		}
	};

	// Modal dialog for deletion confirmation
	module.onConfirmDeleteItem = function(callback) {
		return Swal.fire({
			title: $('#lbl-confirm-delete-title').html(),
			html: $('#lbl-confirm-delete-desc').html(),
			icon: "warning",
			cancelButtonText: $('#lbl-cancel').html(),
			confirmButtonText: $('#lbl-delete').html(),
			showCancelButton: true,
			focusConfirm: false,
			reverseButtons: true,
			showLoaderOnConfirm: true,
			allowOutsideClick: function() {
				return !Swal.isLoading()
			},
			preConfirm: function(operation) {
				return new Promise(function(resolve) {
					callback();
				});
			}
		});
	};
	
	// Modal dialog for general confirmation
	module.onConfirm = function(oSettings, callback) {
		var defaults = {
			title: $('#lbl-confirm-delete-title').html(),
			html: $('#lbl-confirm-delete-desc').html(),
			icon: 'warning',
			cancelButtonText: $('#lbl-cancel').html(),
			confirmButtonText: $('#lbl-delete').html()
		};
		
		var settings = $.extend({}, defaults, oSettings || {});
		
		return Swal.fire({
			title: settings.title,
			html: settings.html,
			icon: settings.icon,
			cancelButtonText: settings.cancelButtonText,
			confirmButtonText: settings.confirmButtonText,
			showCancelButton: true,
			focusConfirm: false,
			reverseButtons: true,
			showLoaderOnConfirm: true,
			allowOutsideClick: function() {
				return !Swal.isLoading()
			},
			preConfirm: function(operation) {
				return new Promise(function(resolve) {
					callback();
				});
			}
		});
	};

	module.draw2dChart = function(selector, chartType) {
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
			type: chartType,
			data: {
				datasets: [{
					data: chartData,
					backgroundColor: Object.keys(window.chartColors).map(function(key) { return window.chartColors[key] }),
					label: canvas.data('title')
				}],
				labels: chartLabels
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						display: canvas.data('legend') ? true : false,
						position: 'bottom',
					},
					title: {
						display: true,
						text: canvas.data('title')
					}
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		};

		new Chart(ctx, config);
	};
	
	module.drawStackedBarChart = function(canvas) {
		var data = $.parseJSON($(canvas.data('data')).text());
		var datasets = new Array();
		
		for(var i = 0; i < data.values.length; i++) {
			datasets.push({
				label: data.titles[i],
				backgroundColor: data.colors[i],
				data: data.values[i],
				stack: data.stack[i]
			});
		}

		new Chart(canvas.get(0).getContext("2d"), {
			type: 'bar',
			data: {
				labels: data.labels,
				datasets: datasets
			}, 
			options: {
				title: {
					display: true,
					text: canvas.data('chart-title')
				},
				tooltips: {
					mode: canvas.data('tooltip-mode'),
					callbacks: {
						label: function(context) {
							var labels = new Array();
							var datasets = context.chart.data.datasets;
							var stackName = context.dataset.stack;

							if(stackName == 'stack1') {
								return context.dataset.label + ": " + context.dataset.data[context.dataIndex];
							} else {
								labels.push(stackName);
								for(var i = 0; i < datasets.length; i++) {
									if(datasets[i].stack == stackName) {
										labels.push(datasets[i].label + ': '+ datasets[i].data[0]);
									}
								}
							}
							return labels;
						}
					}
				},
				plugins: {
					legend: {
						display: canvas.data('legend') ? true : false,
						position: 'bottom',
					},
					title: {
						display: false,
					}
				},
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					x: {
						stacked: true,
					},
					y: {
						stacked: true
					}
				}
			}
		});
	};
	
	module.drawTimeSeriesChart = function(canvas) {
		var color = Chart.helpers.color;
		var data = $.parseJSON($(canvas.data('data')).text());
		var chartData = new Array();
		
		for(var i = 0; i < data.length; i++){
			chartData.push({
				x: moment(data[i].cdate, 'YYYY-MM-DD').toDate(),
				y: data[i].responses
			});
		}
		
		chartData.sort(function(a,b) {
			return a.x - b.x;
		}); 

		new Chart(canvas.get(0).getContext('2d'), {
			type: 'line',
			data: {
				datasets: [{
					label: canvas.data('title'),
					backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
					borderColor: window.chartColors.red,
					fill: false,
					data: chartData
				}]
			},
			options: {
				maintainAspectRatio: false,
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
						display: true,
						scaleLabel: {
							display: false,
						},
						ticks: {
							major: {
								enabled: true
							}
						}
					},
					y: {
						display: true,
						beginAtZero: canvas.data('begin-at-zero'),
						ticks: {
							stepSize: 1,
						},
						scaleLabel: {
							display: true,
							labelString: canvas.data('yaxis-label')
						}
					}
				},
			}
		});
	};

	// Ajax handlers
	module.submitAjaxTask = function(button, formData, triggerBefore, triggerAfter, triggerError) {
		var buttonObj = $(button);
		$.ajax({
			type: 'POST',
			url: ng_ajax.ajax_url,
			data: formData,
			dataType: 'json',
			encode: true,
			contentType: formData instanceof FormData ? false : buttonObj.closest('form').attr('enctype'),
			processData: formData instanceof FormData ? false : true,
			beforeSend: function(xhr) {
				if (triggerBefore) {
					var result = false;
					if (typeof triggerBefore === 'function') {
						result = triggerBefore(buttonObj, formData);
					}

					if (!result) {
						return false;
					}
				}

				buttonObj.prop('disabled', true);
			}
		}).done(function(data) {
			buttonObj.prop('disabled', false);

			if (data.success) {
				if (triggerAfter) {
					if (typeof triggerAfter === 'function') {
						triggerAfter(buttonObj, formData, data);
					}
				}
			} else {
				if (triggerError) {
					if (typeof triggerError === 'function') {
						triggerError(buttonObj, formData, data);
					}
				} else {
					if (data && data.data) {
						Swal.fire({ icon: 'error', text: data.data[0].message });
					}
				}
			}
		});
	};

	module.executeFunctionByName = function(functionName, context, args) {
		var args = [].slice.call(arguments).splice(2);
		var namespaces = functionName.split(".");
		var func = namespaces.pop();

		for (var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		}
		return context[func].apply(this, args);
	};
}(this, jQuery));

(function (m) {
	/*
	 * PHP => moment.js
	 * Will take a php date format and convert it into a JS format for moment
	 * http://www.php.net/manual/en/function.date.php
	 * http://momentjs.com/docs/#/displaying/format/
	 */
	var formatMap = {
			d: 'DD',
			D: 'ddd',
			j: 'D',
			l: 'dddd',
			N: 'E',
			S: function () {
				return '[' + this.format('Do').replace(/\d*/g, '') + ']';
			},
			w: 'd',
			z: function () {
				return this.format('DDD') - 1;
			},
			W: 'W',
			F: 'MMMM',
			m: 'MM',
			M: 'MMM',
			n: 'M',
			t: function () {
				return this.daysInMonth();
			},
			L: function () {
				return this.isLeapYear() ? 1 : 0;
			},
			o: 'GGGG',
			Y: 'YYYY',
			y: 'YY',
			a: 'a',
			A: 'A',
			B: function () {
				var thisUTC = this.clone().utc(),
				// Shamelessly stolen from http://javascript.about.com/library/blswatch.htm
					swatch = ((thisUTC.hours() + 1) % 24) + (thisUTC.minutes() / 60) + (thisUTC.seconds() / 3600);
				return Math.floor(swatch * 1000 / 24);
			},
			g: 'h',
			G: 'H',
			h: 'hh',
			H: 'HH',
			i: 'mm',
			s: 'ss',
			u: '[u]', // not sure if moment has this
			e: '[e]', // moment does not have this
			I: function () {
				return this.isDST() ? 1 : 0;
			},
			O: 'ZZ',
			P: 'Z',
			T: '[T]', // deprecated in moment
			Z: function () {
				return parseInt(this.format('ZZ'), 10) * 36;
			},
			c: 'YYYY-MM-DD[T]HH:mm:ssZ',
			r: 'ddd, DD MMM YYYY HH:mm:ss ZZ',
			U: 'X'
		},
		formatEx = /[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]/g;

	moment.fn.formatPHP = function (format) {
		var that = this;

		return this.format(format.replace(formatEx, function (phpStr) {
			return typeof formatMap[phpStr] === 'function' ? formatMap[phpStr].call(that) : formatMap[phpStr];
		}));
	};
	
	moment.fromPHPFormat = function(format){
        return format.replace(formatEx, function (phpStr) {
			return typeof formatMap[phpStr] === 'function' ? formatMap[phpStr].call(that) : formatMap[phpStr];
        })
    };
}(moment));
