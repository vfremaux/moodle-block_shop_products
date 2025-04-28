


define(['jquery', 'core/log'], function($, log) {

    var shopproducts = {

        init: function() {
            $('.product-toggle-handle').bind('click', this.toggleproduct);

            log.debug('AMD shop product block initialized');
        },

        toggleproduct: function() {

            var that = $(this);

            var pid = that.attr('id').replace('toggle-icon-', '');
            if ($('#product-events-' + pid).hasClass('cs-row-hidden')) {
                $('#product-events-' + pid).removeClass('cs-row-hidden');
                $('#product-controls-' + pid).removeClass('cs-row-hidden');
                var oldsrc = that.attr('src');
                that.attr('src', oldsrc.replace('collapsed', 'expanded'));
            } else {
                $('#product-events-' + pid).addClass('cs-row-hidden');
                $('#product-controls-' + pid).addClass('cs-row-hidden');
                var oldsrc = that.attr('src');
                that.attr('src', oldsrc.replace('expanded', 'collapsed'));
            }
        }

    };

    return shopproducts;

})