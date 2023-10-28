(function (global, $) {
    'use strict';

    var module = global.NgSurveyApi.Form = {initiated: false};

    /**
     * Use this method to load all events that can be delegated to the future objects
     * These events need not fire everytime and loads only once during page load
     */
    module.init = function () {

        module.initiated = true;

        // Event handler to add a new question
        $('#ngs').off('click', '#question-types .btn-add-question');
        $('#ngs').on('click', '#question-types .btn-add-question', function () {
            var button = $(this);
            NgSurveyApi.Common.submitAjaxTask(button, {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'questions.create',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: $('#ngs').find('input[name="ngform[pid]"]').val(),
                    qtype: button.data('type'),
                }
            }, function (button, formData) {
                $('#questions').append($('<div>', {id: 'placeholder-' + button.data('type')}).html($('#ng-loader').html()));
                return true;
            }, function (button, formData, responseData) {
                var question = $(responseData.data);
                $('#placeholder-' + button.data('type')).replaceWith(question);
                question.find('.collapse').collapse('show');
                NgSurveyApi.Common.initializeEditor(question.find('.ngeditor').attr('id'), true);

                // Trigger plugin handler
                var questionType = question.find('input[name="ngform[qtype]"]').val();
                questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);

                if (NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
                    var context = NgSurveyApi[questionType + 'Question'];
                    NgSurveyApi.Common.executeFunctionByName('initQuestion', context, question);
                }
            });
        });

        // Event handler to save a question
        $('#ngs').off('click', '#questions .btn-save-question');
        $('#ngs').on('click', '#questions .btn-save-question', function () {
            NgSurveyApi.Common.refreshEditor();

            var button = $(this);
            var formData = new FormData(button.closest('.question').find('form')[0]);
            formData.append('_ajax_nonce', ng_ajax.nonce);
            formData.append('action', 'ngsa_ajax_handler');
            formData.append('task', 'questions.save');
            formData.append('ngform[sid]', $('#ngs').find('input[name="ngform[sid]"]').val());
            formData.append('ngform[pid]', $('#ngs').find('input[name="ngform[pid]"]').val());

            NgSurveyApi.Common.submitAjaxTask(button, formData, function (button, formData) {
                var questionId = button.closest('.question').find('input[name="ngform[qid]"]').val();
                $('#question-' + questionId + ' .question-body').html($('#ng-loader').html())
                return true;
            }, function (button, formData, responseData) {
                var question = button.closest('.question');
                var questionId = question.find('input[name="ngform[qid]"]').val();

                question.html($(responseData.data).html());
                question.find('.collapse').collapse('show');
                NgSurveyApi.Common.initializeEditor(question.find('.ngeditor').attr('id'), true);

                // Trigger plugin handler
                var questionType = question.find('input[name="ngform[qtype]"]').val();
                questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);

                if (NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
                    var context = NgSurveyApi[questionType + 'Question'];
                    NgSurveyApi.Common.executeFunctionByName('initQuestion', context, question);
                }
            });
        });

        // Event handler to remove a question
        $('#ngs').off('click', '#questions .btn-remove-question');
        $('#ngs').on('click', '#questions .btn-remove-question', function () {
            var button = $(this);
            var formData = {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'questions.remove',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: $('#ngs').find('input[name="ngform[pid]"]').val(),
                    qid: button.closest('.question').data('id'),
                    qtype: button.closest('.question').find('input[name="ngform[qtype]"]').val(),
                }
            };

            NgSurveyApi.Common.onConfirmDeleteItem(function () {
                NgSurveyApi.Common.submitAjaxTask(button, formData, function (button, formData) {
                    button.closest('.question').find('.question-body').html($('#ng-loader').html())
                    return true;
                }, function (button, formData, responseData) {
                    button.closest('.question').remove();
                    Swal.fire({icon: "success", 'text': $('#lbl-delete-success').html()});
                });
            });
        });

        // Event handler for creating new page
        $('#ngs').off('click', '.btn-create-page');
        $('#ngs').on('click', '.btn-create-page', function () {
            var button = $(this);

            Swal.fire({
                title: $('#title_add_new_page').html(),
                html: $('#text_prompt_page_title').html(),
                input: 'text',
                iconHtml: '<div class="dashicons dashicons-plus" style="transform: scale(3);"></div>',
                icon: 'info',
                showCancelButton: true,
                showConfirmButton: true,
                showLoaderOnConfirm: true,
                reverseButtons: true,
                confirmButtonText: $('#lbl-confirm').html(),
                cancelButtonText: $('#lbl-cancel').html(),
                allowOutsideClick: function () {
                    return !Swal.isLoading();
                },
                inputValidator: function (result) {
                    return !result && $('#error_missing_required_value').html()
                },
                preConfirm: function (newTitle) {
                    return new Promise(function (resolve, reject) {
                        if (!newTitle) {
                            reject(newTitle);
                        }

                        NgSurveyApi.Common.submitAjaxTask(button, {
                            _ajax_nonce: ng_ajax.nonce,
                            action: 'ngsa_ajax_handler',
                            task: 'pages.create',
                            ngform: {
                                sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                            },
                            title: newTitle,
                        }, function (button, formData) {
                            $('#ngs #questions').html($('#ng-loader').html());
                            $('#question-types button').prop('disabled', true);
                            $('#ngs .pages-form').find('select, button').prop('disabled', true);
                            return true;
                        }, function (button, formData, responseData) {
                            var new_option = $('<option>', {'value': responseData.data.id})
                                .attr('data-title', responseData.data.title)
                                .html(responseData.data.title + ' [ID: ' + responseData.data.id + ']');
                            $('#ngs .pages-form').find('#page-id').append(new_option);
                            $('#ngs .pages-form').find('select, button').prop('disabled', false);
                            $('#question-types button').prop('disabled', false);
                            $('#ngs #questions').empty();
                            $('#ngs').find('input[name="ngform[pid]"]').val(responseData.data.id),
                                new_option.prop('selected', true);

                            var rulePageBtn = $('<button type="button" class="list-group-item list-group-item-action btn-add-question">').html(responseData.data.title);
                            $('#ngs .rule-pages-list').append(rulePageBtn);
                            $('#pages-list #pages').html($(responseData.data.html).find('#pages').html());

                            resolve(responseData);
                        });
                    });
                },
            });
        });

        // Event handler for update page title
        $('#ngs').off('click', '.btn-edit-page-title');
        $('#ngs').on('click', '.btn-edit-page-title', function () {
            var button = $(this);
            var pageId = button.data('id') ? button.data('id') : $('#ngs').find('input[name="ngform[pid]"]').val();
            var pageTitle = button.data('title') ? button.data('title') : button.closest('.pages-form').find('#page-id option:selected').data('title');

            Swal.fire({
                title: $('#title_change_page_title').html(),
                html: $('#text_prompt_page_title').html(),
                input: 'text',
                inputValue: pageTitle,
                iconHtml: '<div class="dashicons dashicons-edit" style="transform: scale(3);"></div>',
                icon: 'question',
                showCancelButton: true,
                showConfirmButton: true,
                showLoaderOnConfirm: true,
                reverseButtons: true,
                confirmButtonText: $('#lbl-confirm').html(),
                cancelButtonText: $('#lbl-cancel').html(),
                allowOutsideClick: function () {
                    return !Swal.isLoading()
                },
                inputValidator: function (result) {
                    return !result && $('#error_missing_required_value').html()
                },
                preConfirm: function (newTitle) {
                    return new Promise(function (resolve, reject) {
                        if (!newTitle) {
                            reject(newTitle);
                        }

                        NgSurveyApi.Common.submitAjaxTask(button, {
                            _ajax_nonce: ng_ajax.nonce,
                            action: 'ngsa_ajax_handler',
                            task: 'pages.update',
                            ngform: {
                                sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                                pid: pageId,
                            },
                            title: newTitle,
                        }, null, function (button, formData, responseData) {

                            $('#pages-form').find('#page-id option[value="' + pageId + '"]')
                                .attr('data-title', responseData.data)
                                .html(responseData.data + ' [ID: ' + pageId + ']');
                            $('#pages #page-' + pageId).find('.btn-link').html('<span class="dashicons dashicons-text-page"></span> ' + responseData.data);
                            $('#ngs .rule-pages-list').find('[data-id="' + pageId + '"]').html(responseData.data);
                            resolve(responseData);
                        });
                    });
                },
            });
        });

        // Event handler to handle page change events for loading questions
        $('#ngs').off('change', '.pages-form #page-id');
        $('#ngs').on('change', '.pages-form #page-id', function () {
            var button = $(this);
            NgSurveyApi.Common.submitAjaxTask(button, {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'pages.display',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: button.val(),
                },
            }, function (button, formData) {
                $('#ngs #questions').html($('#ng-loader').html());
                $('#question-types button').prop('disabled', true);
                $('#ngs .pages-form').find('select, button').prop('disabled', true);
                return true;
            }, function (button, formData, responseData) {
                $('#ngs #questions').html(responseData.data);
                module.initQuestionFormPage();
                $('#question-types button').prop('disabled', false);
                $('#ngs .pages-form').find('select, button').prop('disabled', false);
                $('#ngs').find('input[name="ngform[pid]"]').val(button.val());
            });
        });

        // Event handler to handle remove page events
        $('#ngs').off('click', '.btn-remove-page');
        $('#ngs').on('click', '.btn-remove-page', function () {
            var button = $(this);
            var pageId = button.data('id') ? button.data('id') : $('#ngs').find('input[name="ngform[pid]"]').val();

            if ($('#ngs .pages-form #page-id option').length == 1) {
                Swal.fire({icon: 'error', text: $('#error_cannot_delete_only_page').html()});
                return false;
            }

            var formData = {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'pages.remove',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: pageId,
                }
            };

            NgSurveyApi.Common.onConfirmDeleteItem(function () {
                NgSurveyApi.Common.submitAjaxTask(button, formData, function (button, formData) {
                    $('#ngs #questions').html($('#ng-loader').html());
                    $('#question-types button').prop('disabled', true);
                    $('#ngs .pages-form').find('select, button').prop('disabled', true);
                    return true;
                }, function (button, formData, responseData) {
                    button.closest('.question').remove();
                    Swal.fire({icon: "success", 'text': $('#lbl-delete-success').html()});
                    $('#ngs .pages-form').find('select, button').prop('disabled', false);
                    $('#ngs .pages-form #page-id option[value="' + pageId + '"]').remove();
                    $('#ngs .pages-form #page-id option:first').prop('selected', true).trigger('change');
                    $('#ngs #pages #page-' + pageId).remove();
                });
            });
        });

        // Event handler to handle move question
        $('#ngs').off('click', '#questions .btn-move-question');
        $('#ngs').on('click', '#questions .btn-move-question', function () {
            var button = $(this);
            var options = {};

            $('#ngs .pages-form #page-id option:not(:selected)').map(function () {
                options[$(this).attr('value')] = $(this).html();
            });

            Swal.fire({
                title: $('#title_move_question').html(),
                html: $('#text_select_the_page').html(),
                input: 'select',
                iconHtml: '<div class="dashicons dashicons-share-alt2" style="transform: scale(3);"></div>',
                icon: 'question',
                inputOptions: options,
                showCancelButton: true,
                showConfirmButton: true,
                showLoaderOnConfirm: true,
                reverseButtons: true,
                confirmButtonText: $('#lbl-confirm').html(),
                cancelButtonText: $('#lbl-cancel').html(),
                allowOutsideClick: function () {
                    return !Swal.isLoading()
                },
                preConfirm: function (page_id) {
                    return new Promise(function (resolve, reject) {
                        if (!page_id) {
                            reject(page_id);
                        }

                        var formData = {
                            _ajax_nonce: ng_ajax.nonce,
                            action: 'ngsa_ajax_handler',
                            task: 'questions.move',
                            ngform: {
                                sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                                pid: $('#ngs').find('input[name="ngform[pid]"]').val(),
                                qid: button.closest('.question').find('input[name="ngform[qid]"]').val(),
                                nid: page_id,
                            }
                        };

                        return $.ajax({
                            type: 'POST',
                            url: ng_ajax.ajax_url,
                            data: formData,
                            dataType: 'json',
                        }).done(function (data) {
                            if (!data || data.success === false) {
                                Swal.showValidationMessage(data.data[0].message);
                            } else {
                                button.closest('.question').remove();
                            }
                            resolve(data);
                        }).fail(function (error) {
                            Swal.showValidationMessage(error.message);
                            resolve(error);
                        });
                    });
                },
            });
        });

        // Event handler to handle copy question
        $('#ngs').off('click', '#questions .btn-copy-question');
        $('#ngs').on('click', '#questions .btn-copy-question', function () {
            var button = $(this);
            var options = {};
            var question_id = $(this).closest('.question').find('input[name="ngform[qid]"]').val();

            $('#ngs .pages-form #page-id option').map(function () {
                options[$(this).attr('value')] = $(this).html();
            });

            Swal.fire({
                title: $('#title_copy_question').html(),
                html: $('#text_select_the_page').html(),
                input: 'select',
                iconHtml: '<div class="dashicons dashicons-format-gallery" style="transform: scale(3);"></div>',
                icon: 'question',
                inputOptions: options,
                showCancelButton: true,
                showConfirmButton: true,
                showLoaderOnConfirm: true,
                reverseButtons: true,
                confirmButtonText: $('#lbl-confirm').html(),
                cancelButtonText: $('#lbl-cancel').html(),
                allowOutsideClick: function () {
                    return !Swal.isLoading()
                },
                preConfirm: function (page_id) {
                    return new Promise(function (resolve, reject) {
                        if (!page_id) {
                            reject(page_id);
                        }

                        var formData = new FormData();
                        formData.append('_ajax_nonce', ng_ajax.nonce);
                        formData.append('action', 'ngsa_ajax_handler');
                        formData.append('task', 'questions.copy');
                        formData.append('ngform[sid]', $('#ngs').find('input[name="ngform[sid]"]').val());
                        formData.append('ngform[pid]', page_id);
                        formData.append('ngform[qid]', question_id);

                        NgSurveyApi.Common.submitAjaxTask(button, formData, null, function (button, formData, responseData) {
                            $('#ngs #page-id').trigger('change');
                            resolve(responseData);
                        });
                    });
                },
            });
        });

        // Event handler for creating new rule
        $('#ngs').off('click', '#conditional-rules .btn-create-rule');
        $('#ngs').on('click', '#conditional-rules .btn-create-rule', function () {
            var button = $(this);

            Swal.fire({
                title: $('#title_add_new_rule').html(),
                html: $('#text_prompt_rule_title').html(),
                input: 'text',
                iconHtml: '<div class="dashicons dashicons-plus" style="transform: scale(3);"></div>',
                icon: 'info',
                showCancelButton: true,
                showConfirmButton: true,
                showLoaderOnConfirm: true,
                reverseButtons: true,
                confirmButtonText: $('#lbl-confirm').html(),
                cancelButtonText: $('#lbl-cancel').html(),
                allowOutsideClick: function () {
                    return !Swal.isLoading()
                },
                inputValidator: function (result) {
                    return !result && $('#error_missing_required_value').html()
                },
                preConfirm: function (newTitle) {
                    return new Promise(function (resolve, reject) {
                        if (!newTitle) {
                            reject(newTitle);
                        }

                        NgSurveyApi.Common.submitAjaxTask(button, {
                            _ajax_nonce: ng_ajax.nonce,
                            action: 'ngsa_ajax_handler',
                            task: 'rules.create',
                            ngform: {
                                sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                                pid: $('#ngs').find('#rule_pages .btn-load-page-rules.active').data('id'),
                            },
                            title: newTitle,
                        }, function (button, formData) {
                            return true;
                        }, function (button, formData, responseData) {
                            $('#conditional-rules #rules').html($(responseData.data.html).find('#rules').html());
                            module.initConditionalRulesForm();
                            resolve(responseData);
                        });
                    });
                },
            });
        });

        // Event handler to save a rule
        $('#ngs').off('click', '#rules .btn-save-rule');
        $('#ngs').on('click', '#rules .btn-save-rule', function () {
            var button = $(this);
            var rule_content = JSON.stringify(button.closest('.rule').find('.rules-builder').queryBuilder('getRules'));
            var formData = new FormData(button.closest('.rule').find('form')[0]);
            formData.append('_ajax_nonce', ng_ajax.nonce);
            formData.append('action', 'ngsa_ajax_handler');
            formData.append('task', 'rules.save');
            formData.append('ngform[sid]', $('#ngs').find('input[name="ngform[sid]"]').val());
            formData.append('ngform[pid]', $('#ngs').find('#rule_pages .btn-load-page-rules.active').data('id'));
            formData.set('ngform[rule_content]', rule_content);

            NgSurveyApi.Common.submitAjaxTask(button, formData, function (button, formData) {
                var ruleId = button.closest('.rule').data('id');
                $('#rule-' + ruleId + ' .rule-body').html($('#ng-loader').html())
                return true;
            }, function (button, formData, responseData) {
                $('#conditional-rules #rules').html($(responseData.data.html).find('#rules').html());
                module.initConditionalRulesForm();
            });
        });

        // Event handler to load the rules of a page
        $('#ngs').off('click', '.rule-pages-list .btn-load-page-rules');
        $('#ngs').on('click', '.rule-pages-list .btn-load-page-rules', function () {
            var button = $(this);
            $('#ngs .rule-pages-list .btn-load-page-rules').removeClass('active');
            button.addClass('active');

            NgSurveyApi.Common.submitAjaxTask(button, {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'rules.display',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: button.data('id'),
                },
            }, function (button, formData) {
                $('#rules').html($('#ng-loader').html())
                return true;
            }, function (button, formData, responseData) {
                $('#conditional-rules #rules').html($(responseData.data.html).find('#rules').html());
                $('#conditional-rules #rule-templates').html($(responseData.data.html).find('#rule-templates').html());
                module.initConditionalRulesForm();
            });
        });

        // Event handler to remove a rule
        $('#ngs').off('click', '#rules .btn-remove-rule');
        $('#ngs').on('click', '#rules .btn-remove-rule', function () {
            var button = $(this);
            var formData = {
                _ajax_nonce: ng_ajax.nonce,
                action: 'ngsa_ajax_handler',
                task: 'rules.remove',
                ngform: {
                    sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                    pid: $('#ngs').find('#rule_pages .btn-load-page-rules.active').data('id'),
                    rid: button.closest('.rule').data('id'),
                }
            };

            NgSurveyApi.Common.onConfirmDeleteItem(function () {
                NgSurveyApi.Common.submitAjaxTask(button, formData, function (button, formData) {
                    return true;
                }, function (button, formData, responseData) {
                    button.closest('.rule').remove();
                    Swal.fire({icon: "success", 'text': $('#lbl-delete-success').html()});
                });
            });
        });

        // Add sorting mechanism for the questions, pages and rules
        var entities = [
            {plural: 'pages', single: 'page', pid: 0},
            {plural: 'questions', single: 'question'},
            {plural: 'rules', single: 'rule'}];
        entities.forEach(function (entity) {
            $('#' + entity.plural).sortable({
                revert: true,
                handle: '.btn-sort-' + entity.single,
                placeholder: 'alert alert-info',
                forcePlaceholderSize: true
            }).on('sortupdate', function (event, ui) {
                if (!ui.item.hasClass(entity.single)) {
                    return;
                }

                var positions = {};
                $('.' + entity.single).each(function (index) {
                    positions[index + 1] = $(this).data('id');
                });

                var page_id = 0;
                switch (entity.single) {
                    case 'question':
                        page_id = $('#ngs').find('input[name="ngform[pid]"]').val();
                        break;

                    case 'rule':
                        page_id = $('#ngs').find('#rule_pages .btn-load-page-rules.active').data('id');
                        break;
                }

                var data = {
                    _ajax_nonce: ng_ajax.nonce,
                    action: 'ngsa_ajax_handler',
                    task: entity.plural + '.sort',
                    ngform: {
                        sid: $('#ngs').find('input[name="ngform[sid]"]').val(),
                        pid: page_id,
                    },
                    ordering: positions,
                };

                NgSurveyApi.Common.submitAjaxTask(null, data, null, null, null);
            });
        });

        // Rules action selector handlers
        $('#ngs').off('change', '[name="ngform[action]"]');
        $('#ngs').on('change', '[name="ngform[action]"]', function () {
            var button = $(this);
            var pagesSelect = button.closest('[name="conditional-rules-form"]').find('[name="ngform[action_page]"]');
            var questionsSelect = button.closest('[name="conditional-rules-form"]').find('[name="ngform[action_question]"]');
            var currentPage = $('#rule_pages .btn-load-page-rules.active');

            switch (button.val()) {
                case 'show_page':
                case 'skip_page':
                    var options = new Array();
                    currentPage.nextAll().each(function () {
                        options.push($('<option>', {'value': $(this).data('id')}).html($(this).html()));
                    });

                    pagesSelect.find('option:not(":first")').remove();
                    pagesSelect.append(options).show();

                    if (questionsSelect.hasClass("select2-hidden-accessible")) {
                        questionsSelect.hide().select2('destroy');
                    }
                    questionsSelect.hide().find('option:not(":first")').remove();
                    break;

                case 'show_question':
                case 'hide_question':
                    pagesSelect.find('option:not(":first")').remove();
                    questionsSelect.find('option:not(":first")').remove()
                    pagesSelect.hide().append(new Option('Dummy', currentPage.data('id'), true, true));

                    var questionsData = [];
                    $.each($.parseJSON($('#questions-json-data').text()), function (index, page) {
                        if (page.id == currentPage.data('id')) {
                            questionsData = page.children;
                            return false;
                        }
                    });

                    questionsSelect.select2({
                        data: questionsData,
                        width: '100%',
                        container: '#ngs',
                        theme: 'bootstrap4',
                        templateResult: function (state) {
                            if (!state.id) {
                                return state.text;
                            }

                            var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                            $state.find('.icon').attr('class', state.icon);
                            $state.find('.text').text(state.text);
                            return $state;
                        },
                        templateSelection: function (state) {
                            if (!state.id) {
                                return state.text;
                            }
                            var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                            $state.find('.icon').attr('class', state.icon);
                            $state.find('.text').text(state.text);
                            return $state;
                        },
                        allowHtml: true
                    }).show();
                    break;

                case 'show_future_qn':
                case 'hide_future_qn':
                    var options = new Array();
                    currentPage.nextAll().each(function () {
                        options.push($('<option>', {'value': $(this).data('id')}).html($(this).html()));
                    });

                    pagesSelect.find('option:not(":first")').remove();
                    pagesSelect.append(options).show();

                    if (questionsSelect.hasClass("select2-hidden-accessible")) {
                        questionsSelect.hide().select2('destroy');
                    }
                    questionsSelect.hide().find('option:not(":first")').remove();
                    break;

                default:
                    pagesSelect.find('option:not(":first")').remove();
                    pagesSelect.hide();

                    if (questionsSelect.hasClass("select2-hidden-accessible")) {
                        questionsSelect.hide().select2('destroy');
                    }
                    questionsSelect.hide().find('option:not(":first")').remove();
                    break;
            }
        });

        // Event handler for rule action page change event
        $('#ngs').off('chanhge', '[name="ngform[action_page]"]');
        $('#ngs').on('change', '[name="ngform[action_page]"]', function () {
            var button = $(this);
            var questionsSelect = button.closest('[name="conditional-rules-form"]').find('[name="ngform[action_question]"]');
            var selectedAction = button.closest('[name="conditional-rules-form"]').find('[name="ngform[action]"]').val();
            var selectedPage = button.val();

            if (selectedPage && $.inArray(selectedAction, ['show_future_qn', 'hide_future_qn']) !== -1) {
                var questionsData = [];
                $.each($.parseJSON($('#questions-json-data').text()), function (index, page) {
                    if (page.id == selectedPage) {
                        questionsData = page.children;
                        return false;
                    }
                });

                questionsSelect.find('option:not(":first")').remove()
                questionsSelect.select2({
                    data: questionsData,
                    width: '100%',
                    container: '#ngs',
                    theme: 'bootstrap4',
                    templateResult: function (state) {
                        if (!state.id) {
                            return state.text;
                        }

                        var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                        $state.find('.icon').attr('class', state.icon);
                        $state.find('.text').text(state.text);
                        return $state;
                    },
                    templateSelection: function (state) {
                        if (!state.id) {
                            return state.text;
                        }
                        var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                        $state.find('.icon').attr('class', state.icon);
                        $state.find('.text').text(state.text);
                        return $state;
                    },
                    allowHtml: true
                }).show();
            }
        });

        // Initialize one time load elements
        module.initQuestionFormPage();
        module.initConditionalRulesForm();

        // Init all registered question types, whether they are loaded in current pages or not
        var types = Object.keys(NgSurveyApi);
        if (types && types.length > 0) {
            for (var i = 0; i < types.length; i++) {
                if (types[i].indexOf('Question') > 0 && NgSurveyApi[types[i]].hasOwnProperty('initForm')) {
                    var context = NgSurveyApi[types[i]];
                    NgSurveyApi.Common.executeFunctionByName('initForm', context);
                }
            }
        }
    };

    /**
     * Add all one time events here which must be executed every time the questions form page is loaded.
     * These events are executed once per load and cannot be delegated to the future objects
     */
    module.initQuestionFormPage = function () {
        $('#questions .question').each(function () {
            var question = $(this);
            question.find('.ngeditor').each(function () {
                NgSurveyApi.Common.initializeEditor($(this).attr('id'), true);
            });

            // Trigger plugin handler
            var questionType = question.find('input[name="ngform[qtype]"]').val();
            questionType = questionType.charAt(0).toUpperCase() + questionType.slice(1);

            if (NgSurveyApi.hasOwnProperty(questionType + 'Question')) {
                var context = NgSurveyApi[questionType + 'Question'];
                if (context.hasOwnProperty('initQuestion')) {
                    NgSurveyApi.Common.executeFunctionByName('initQuestion', context, question);
                }
            }
        })
    };

    /**
     * Initialize the conditional rules form. This method is called every time the rules are updated
     * or the page is reloaded or the elements are replaced in the rules form.
     */
    module.initConditionalRulesForm = function () {
        // Get all available rule templates
        var filters = [];
        $('#ngs #rule-templates .rule-template').each(function () {
            var ruleTemplate = $.trim($(this).text());
            if (ruleTemplate) {
                var rule = $.parseJSON(ruleTemplate);

                // parse the replacement function from plugin configuration
                if (rule.plugin_config && rule.plugin_config.ngselection) {
                    rule.plugin_config[rule.plugin_config.ngselection] = eval("(" + rule.plugin_config[rule.plugin_config.ngselection] + ")");
                }
                if (rule.plugin_config && rule.plugin_config.ngresult) {
                    rule.plugin_config[rule.plugin_config.ngresult] = eval("(" + rule.plugin_config[rule.plugin_config.ngresult] + ")");
                }

                filters.push(rule);
            }
        });

        if (!filters.length) {
            return;
        }

        $('#rules .rule').each(function () {
            var ruleWrapper = $(this);
            var content = ruleWrapper.find('input[name="ngform[rule_content]"]').val();
            var rules_content = $.parseJSON(content);

            // Initial rules builder with select2 plugin for question selection
            ruleWrapper.find('.rules-builder').queryBuilder({
                filters: filters, // The rule templates are defined by the plugins
                rules: (rules_content && rules_content.rules) ? rules_content : null,
                plugins: {
                    'bt-select2': {
                        width: '100%',
                        container: '#ngs',
                        theme: 'bootstrap4',
                        templateResult: function (state) {
                            if (!state.id) {
                                return state.text;
                            }
                            var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                            $state.find('.icon').attr('class', $(state.element).data('icon'));
                            $state.find('.text').text(state.text);
                            return $state;
                        },
                        templateSelection: function (state) {
                            if (!state.id) {
                                return state.text;
                            }
                            var $state = $('<span><span class="icon"></span><span class="text"></span></span>');
                            $state.find('.icon').attr('class', $(state.element).data('icon'));
                            $state.find('.text').text(state.text);
                            return $state;
                        },
                        allowHtml: true
                    },
                },
            });

            // Load saved rule actions if any
            var rule_actions = $.parseJSON(ruleWrapper.find('input[name="ngform[rule_actions]"]').val());
            if (rule_actions && rule_actions.action) {
                var ruleSelect = ruleWrapper.find('[name="ngform[action]"]');
                var pagesSelect = ruleWrapper.find('[name="ngform[action_page]"]');
                var questionsSelect = ruleWrapper.find('[name="ngform[action_question]"]');

                ruleSelect.val(rule_actions.action).trigger('change');

                switch (rule_actions.action) {
                    case 'show_page':
                    case 'skip_page':
                        pagesSelect.val(rule_actions.page);
                        break;

                    case 'show_question':
                    case 'hide_question':
                        pagesSelect.val(rule_actions.page).trigger('change');
                        questionsSelect.val(rule_actions.question).trigger('change');
                        break;

                    case 'show_future_qn':
                    case 'hide_future_qn':
                        pagesSelect.val(rule_actions.page).trigger('change');
                        questionsSelect.val(rule_actions.question).trigger('change');
                        break;

                    default:
                        break;
                }
            }
        });

        $(document).on('focus.datetimepicker', '.datetimepicker-input', function () {
            $(this).datetimepicker('show', $(this).data('datetimepicker'));
        });
        $(document).on('blur.datetimepicker', '.datetimepicker-input', function () {
            $(this).datetimepicker('hide', $(this).data('datetimepicker'));
        });
    };

    module.handleAnswerPresets = function () {
        $('#ngs')
            .off('click', '.btn-load-answer-presets')
            .on('click', '.btn-load-answer-presets', function () {
                let button = $(this);
                let presetsModal = $('#answer-presets-modal');
                let presetsList = presetsModal.find('.presets-list');
                let answersList = presetsModal.find('[name="preset-answers-list"]');
                let presetType = button.data('preset-type');

                presetsList.empty();
                presetsModal.find('.answer-presets-list').empty();
                presetsModal.find('[name="preset-question-id"]').val(button.closest('.question').data('id'));
                presetsModal.find('[name="preset-type"]').val(presetType);

                for (let i = 0; i < ng_ajax.answer_presets.length; i++) {
                    // if (ng_ajax.answer_presets[i].meta_value.preset_type[0] !== presetType) {
                    //     continue;
                    // }
                    let preset = $('<a href="#" class="list-group-item list-group-item-action">' + ng_ajax.answer_presets[i].post_title + '</a>');
                    preset.click(function () {
                        let answers = ng_ajax.answer_presets[i].meta_value.preset_values;
                        answersList.val('');

                        for (let i = 0; i < answers.length; i++) {
                            answersList.val(answers.join('\n'));
                        }
                    });

                    presetsList.append(preset);
                }
            })
            .on('click', '.btn-add-preset-answers', function () {
                let presetsModal = $('#answer-presets-modal');
                let answersList = presetsModal.find('[name="preset-answers-list"]');
                let presetType = presetsModal.find('[name="preset-type"]').val();
                let questionForm = $('#question-' + presetsModal.find('[name="preset-question-id"]').val());
                let answers = answersList.val().split('\n');

                if (!answersList.val() || !answers.length) {
                    return;
                }

                for (let i = 0; i < answers.length; i++) {
                    let answers_wrapper = questionForm.find('.answers-wrapper');
                    let template = $(answers_wrapper.find('.answer-template').html());

                    if (presetType === 'column') {
                        template.find('input[name="answer_title"]')
                            .attr('value', answers[i])
                            .attr('name', 'ngform[column_title][]')
                            .attr('tabindex', 250 + answers_wrapper.find('.columns .answer').length + 1);
                        template.find('input[name="answer_id"]').attr('name', 'ngform[column_id][]');
                        answers_wrapper.find('.columns').append(template);
                    } else {
                        template.find('input[name="answer_title"]')
                            .attr('value', answers[i])
                            .attr('name', 'ngform[answer_title][]')
                            .attr('tabindex', 200 + answers_wrapper.find('.answers .answer').length + 1);
                        template.find('input[name="answer_id"]').attr('name', 'ngform[answer_id][]');
                        answers_wrapper.find('.answers').append(template);
                    }
                }

                let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('answer-presets-modal'));
                modal.hide();
            });
    }
}(this, jQuery));