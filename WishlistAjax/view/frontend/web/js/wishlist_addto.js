define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, modal, urlBuilder) {

    $(".catalog-category-view .action.tocart").on("click",function(){
        $('.page.messages').show();
    });
    $(".catalog-category-view .customer_wishlist_icon").on("click",function(event) {
        $('.page.messages').hide();
    });


    $(".catalog-product-view .action.tocart").on("click",function(){
        $('.page.messages').show();
    });
    $(".catalog-product-view .customer_wishlist_icon").on("click",function(event) {
        $('.page.messages').hide();
    });



    "use strict";

    var wpWishlistAddTo = {
        getWishlistsUrl: null,
        customerWishlists: null,
        stopEventPropagation: false,
        ajaxWishlist: false,
        wishlistElm: null
    };

    return {
        initAjaxWishlist :function(params) {
            wpWishlistAddTo.ajaxWishlist = params.ajaxWishlist;
            var that = this;
            if (wpWishlistAddTo.ajaxWishlist) {
                $('body').on('click', 'a.heart_icon.action.towishlist', function() {
                    //if from cart move to wishlist, no ajax
                    if ($(this).hasClass('action-towishlist')) {
                        return true;
                    }
                    that._addtoAjax($(this));
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
        _addtoAjax: function(element) {

  var inputValues = new Array();
$("#super-product-table input").each(function(){
    var product_id = $(this).attr('name').split("super_group")[1];
    product_id = product_id.split("[")[1];
    product_id = product_id.split("]")[0];
    inputValues.push({product_id:product_id,value: $(this).val()});
});

            var that = this;

            var formKey = $('input[name="form_key"]').val();
            var params = element.data('post');
            params.data.ajax = 1;
            params.data['super_group1']=JSON.stringify(inputValues);

            if (formKey) {
                params.data['form_key'] = formKey;
            }

            that.showOverlay();

            $.ajax({
                url: params.action,
                method: 'POST',
                data: params.data,
                success: function (response) {                    

                    //console.log(response);


                 if(response.result == true)
                    {   

                    $.ajax({
                        url: response.wish_remove_url,
                        method: 'POST',
                        data: params.data,
                        success: function (response_wish) {
                            if(response_wish){
                                var tProductId=params.data.product;
                                $('#customer_wishlist_icon_'+tProductId).html(response_wish);
                                
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

