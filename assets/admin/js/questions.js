(function (global, $) {
    'use strict';

    var module = NgSurveyApi.TextboxQuestion = {};

    /**
     * Function loaded on the consolidated reports page and initialize the question.
     *
     * @param question the question that is being loaded.
     */
    module.initReport = function (question) {
        question.find('.time-series-chart').each(function () {
            NgSurveyApi.Common.drawTimeSeriesChart($(this));
        });
    };
}(this, jQuery));

(function (global, $) {
    'use strict';

    var module = NgSurveyApi.ChoiceQuestion = {};

    /**
     * Function loaded on the load of main question form and attaches all event handlers to DOM.
     */
    module.initForm = function () {
        $('#ngs').off('click', '.qtype-choice .btn-add-answer');
        $('#ngs').on('click', '.qtype-choice .btn-add-answer', function () {
            var answers_wrapper = $(this).closest('.answers-wrapper');
            var template = $(answers_wrapper.find('.answer-template').html());
            template.find('input[name="answer_title"]')
                .attr('name', 'ngform[answer_title][]')
                .attr('tabindex', 100 + answers_wrapper.find('.answers .answer').length + 1);
            template.find('input[name="answer_id"]').attr('name', 'ngform[answer_id][]');
            answers_wrapper.find('.answers').append(template);
        });

        $('#ngs').off('click', '.qtype-choice .btn-remove-answer');
        $('#ngs').on('click', '.qtype-choice .btn-remove-answer', function () {
            $(this).closest('.answer').remove();
        });

        // Presets handling
        NgSurveyApi.Form.handleAnswerPresets("answer");
    };

    /**
     * Function loaded everytime the form page is loaded, for example new page created, page changed etc.
     *
     * @param question the question that is being loaded.
     */
    module.initQuestion = function (question) {
        question.find('.answers').sortable({
            revert: true,
            handle: '.btn-sort-answer',
            placeholder: 'alert alert-info',
            forcePlaceholderSize: true
        });
    };

    /**
     * Function loaded on the consolidated reports page and initialize the question.
     *
     * @param question the question that is being loaded.
     */
    module.initReport = function (question) {
        question.find('canvas').each(function () {
            NgSurveyApi.Common.draw2dChart('#' + $(this).attr('id'), $(this).data('chart-type'));
        });
    };
}(this, jQuery));
