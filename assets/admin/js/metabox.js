(function (global, $) {
    'use strict';
    var module = NgSurveyApi.Metabox = {};

    module.init = function () {
        $('#ngs').on('click', '.btn-add-option', function () {
            var option = $(this).closest('.repeat-option').clone();
            option.find('input').val('');
            $(this).closest('.repeat-options').append(option);
        }).on('click', '.btn-remove-option', function () {
            if ($(this).closest('.repeat-options').find('.repeat-option').length > 1) {
                $(this).closest('.repeat-option').remove();
            }
        }).find('.repeat-options').sortable({
                revert: true,
                handle: '.btn-sort-option',
                placeholder: 'alert alert-info',
                forcePlaceholderSize: true
        });
    };
})(this, jQuery);
