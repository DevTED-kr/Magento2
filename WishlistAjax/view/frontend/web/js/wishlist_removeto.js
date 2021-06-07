define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, modal, urlBuilder) {
    "use strict";

    var wpWishlistAddTo = {
        getWishlistsUrl: null,
        customerWishlists: null,
        stopEventPropagation: false,
        ajaxWishlist: false,
        wishlistElm: null
    };

    return {
        initAjaxWishlistRemove :function(params) {
            wpWishlistAddTo.ajaxWishlist = params.ajaxWishlist;
            var that = this;
            if (wpWishlistAddTo.ajaxWishlist) {
                $('body').on('click', 'a.heart_icon.action.delete', function() {
                    //if from cart move to wishlist, no ajax
                   /* if ($(this).hasClass('action-towishlist')) {
                        return true;
                    }*/
                    that._removetoAjax($(this));
                    return false;
                });
            }
        },
        showOverlay: function() {
            $('body').trigger('processStart');
        },
        removeOverlay: function() {
            $('body').trigger('processStop');
        },
        _removetoAjax: function(element) {
            var wProductId = element.attr("w-data-id");
            var that = this;
            var formKey = $('input[name="form_key"]').val();
            var params = element.data('post');
            params.data.ajax = 1;

            if (formKey) {
                params.data['form_key'] = formKey;
            }

            if (wProductId) {
                params.data['wProductId'] = wProductId;
            }

            that.showOverlay();

            $.ajax({
                url: params.action,
                method: 'POST',
                data: params.data,
                success: function (response) {
                    $('.page.messages').hide(); 

                    
                    if(response.result == true)
                    {

                    $.ajax({
                        url: response.wish_add_url,
                        method: 'POST',
                        data: params.data,
                        success: function (response_wish) {
                            if(response_wish){
                                $('#customer_wishlist_icon_'+wProductId).html(response_wish);
                            }
                        }
                     });
                    }

                    if(typeof response !='object' || response.result != true)
                    {
                        var url = urlBuilder.build("wishlist");
                        window.location.href = url;
                    }
                    that.removeOverlay();
                }
            });
        }
    };

});