(function($) {
    "use strict";

    var cenaWoo = {
        init: function() {
            var self = this;

            // thumb image
            $('.thumbnails-image .thumb-link').on("click", function(e) {
                e.preventDefault();
                var image_url = $(this).attr('href');
                var image_full_url = $(this).data('image');
                $('.woocommerce-main-image .featured-image').attr('href', image_full_url);
                $('.woocommerce-main-image .featured-image img').attr('src', image_url);
                $('.cloud-zoom').CloudZoom();
            });

            self.initAjaxWishlist();
            self.initAjaxRemoveMiniCart();
            self.initAjaxAddToCart();
            self.initBuyNow();
            self.initChangeQtyPageSingle();
            self.initAjaxShopDisplayModeGrid();
            self.initAjaxShopDisplayModeList();
            self.initsidebarMobile();
            self.ProductCategoriesTabs();
            self.ProductTabs();

            if (cena_settings.ajax_update_quantity) {
                self.initChangeQtyPageCartUpdate()
            }
        },

        ProductCategoriesTabs: function() {
            if( typeof cena_settings === "undefined" ) return;

            this._initProductCategoriesTabs()
        },

        _initProductCategoriesTabs() { 
            var process = false;   
            $('.tbay-product-categories-tabs-ajax.ajax-active').each(function() {
                var $this  = $(this)     
       
    
                $this.find('.product-categories-tabs-title li a').off('click').on('click', function(e) {
                    e.preventDefault();
     
                    var $this   = $(this),          
                        atts    = $this.parent().parent().data('atts'),
                        value   = $this.data('value'),
                        id      = $this.attr('href')
    
                    if ( process || $(id).hasClass('active-content') ) {
                        return;
                    }                   
       
                    process = true;

                    if (atts && atts.layout_type) {
                        var allowedLayouts = ['grid', 'list', 'carousel', 'carousel-special', 'special'];
                        if (!allowedLayouts.includes(atts.layout_type)) {
                            console.warn('Invalid layout type:', atts.layout_type);
                            return;
                        }
                    }
             
                    $.ajax({
                        url     : cena_settings.ajaxurl,
                        data    : {
                            atts  : atts,
                            value  : value,
                            action: 'cena_get_products_categories_tab_shortcode',
                            nonce: cena_settings.nonce
                        }, 
                        dataType: 'json',    
                        method  : 'POST',
                        beforeSend : function ( xhr ) {
                            $(id).parent().addClass('load-ajax')
                        },     
                        success : function(data) {   
    
    
                           if( $(id).find('.tab-banner').length > 0 ) {
                            $(id).append(data.html);      
                           } else {
                             $(id).prepend(data.html);
                           }
    
                           $(id).parent().find('.current').removeClass('current');  
                           $(id).parent().removeClass('load-ajax')
                           $(id).addClass('active-content'); 
    
    
                           $(id).addClass('current');   
                           $( document.body ).trigger( 'tbay_carousel_slick' );    
                           $( document.body ).trigger( 'tbay_ajax_tabs_products' ); 
                           $( document.body ).trigger( 'cena_lazy_load_image' ); 
                        },
                        error   : function() {
                            console.log('ajax error');
                        },
                        complete: function() { 
                            process = false;
                        }
                    });
                });
    
            });
        },

        ProductTabs: function() {
            if( typeof cena_settings === "undefined" ) return;

            this._initProductTabs()
        },

        _initProductTabs() {      
            var process = false;             
            $('.tbay-product-tabs-ajax.ajax-active').each(function() {
                var $this  = $(this)     
     
    
                $this.find('.product-tabs-title li a').off('click').on('click', function(e) {
                    e.preventDefault();    
    
                    var $this   = $(this),          
                        atts    = $this.parent().parent().data('atts'),
                        value   = $this.data('value'),
                        id      = $this.attr('href')
    
                    if ( process || $(id).hasClass('active-content') ) {
                        return;
                    }          
    
                    process = true;  
                    
                    $.ajax({        
                        url     : cena_settings.ajaxurl,
                        data    : {
                            atts  : atts,
                            value  : value,
                            action: 'cena_get_products_tab_shortcode',
                            nonce: cena_settings.nonce
                        }, 
                        dataType: 'json',    
                        method  : 'POST',
                        beforeSend : function ( xhr ) {
                            $(id).parent().addClass('load-ajax')
                        },   
                        success : function(data) {
                           $(id).html(data.html); 
    
                           $(id).parent().find('.current').removeClass('current');
                           $(id).parent().removeClass('load-ajax')
                           $(id).addClass('active-content');
    
    
                           $(id).addClass('current');       
                           $( document.body ).trigger( 'tbay_carousel_slick' );    
                           $( document.body ).trigger( 'tbay_ajax_tabs_products' ); 
                           $( document.body ).trigger( 'cena_lazy_load_image' ); 
                        },     
                        error: function() {
                            console.log('ajax error');
                        },
                        complete: function() {
                            process = false;
                        }
                    });
                });
    
            });
        },

        initAjaxWishlist: function() {
            $(document).on('yith_wcwl_reload_fragments', function() {
                var counter = $('.count_wishlist');
                $.ajax({
                    url: yith_wcwl_l10n.ajax_url,
                    data: {
                        action: 'yith_wcwl_update_wishlist_count'
                    },
                    dataType: 'json',
                    success: function(data) {
                        counter.html(data.count);
                    },
                    beforeSend: function() {
                        counter.block();
                    },
                    complete: function() {
                        counter.unblock();
                    }
                })
            })
        },
        initAjaxRemoveMiniCart: function() {
            if( !cena_settings.enable_ajax_add_to_cart ) return;

            // Ajax delete product in the cart
            $(document).on('click', '.mini_cart_content a.remove', function(e) {
                e.preventDefault();

                var product_id = $(this).attr("data-product_id"),
                    cart_item_key = $(this).attr("data-cart_item_key"),
                    product_container = $(this).parents('.mini_cart_item');

                var thisItem = $(this).closest('.widget_shopping_cart_content');

                // Add loader
                product_container.block({
                    message: null,
                    overlayCSS: {
                        cursor: 'none'
                    }
                });

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        action: "product_remove",
                        product_id: product_id,
                        cart_item_key: cart_item_key
                    },
                    beforeSend: function() {
                        thisItem.find('.mini_cart_content').append('<div class="ajax-loader-wapper"><div class="ajax-loader"></div></div>');
                        thisItem.find('.mini_cart_content').fadeTo("slow", 0.3);
                        e.stopPropagation();
                    },
                    success: function(response) {
                        if (!response || response.error)
                            return;

                        var fragments = response.fragments;

                        // Replace fragments
                        if (fragments) {
                            $.each(fragments, function(key, value) {
                                $(key).replaceWith(value);
                            });
                        }

                        $('.add_to_cart_button.added').each(function(index) {
                            if ($(this).data('product_id') == product_id) {
                                $(this).removeClass('added');
                            }
                        });
                        e.stopPropagation();
                    }
                });
            });
        },
        initAjaxAddToCart: function() {
            // add to cart modal
            var product_info = null;
            jQuery('body').on('adding_to_cart', function(button, data, data2) {
                product_info = data2;
            });

            jQuery('body').on('added_to_cart', function(fragments, cart_hash) {
                if (product_info) {
                    jQuery('#tbay-cart-modal').modal();
                    var url = cena_settings.ajaxurl + '?action=cena_add_to_cart_product&product_id=' + product_info.product_id + '&nonce=' + cena_settings.nonce;
                    jQuery.get(url, function(data, status) {
                        jQuery('#tbay-cart-modal .modal-body .modal-body-content').html(data);
                    });
                    jQuery('#tbay-cart-modal').on('hidden.bs.modal', function() {
                        jQuery(this).find('.modal-body .modal-body-content').empty();
                    });
                }
            });

        },
        initBuyNow: function() {
            var self = this;

            /*Cena Buy Now*/
            $('body').on('click', '.tbay-buy-now', function (e) {
              e.preventDefault();
              let productform = $(this).closest('form.cart'),
                  submit_btn = productform.find('[type="submit"]'),
                  buy_now = productform.find('input[name="cena_buy_now"]')
         
                buy_now.val('1');
                productform.find('.single_add_to_cart_button').click();
            }); 

        },
        initAjaxShopDisplayModeList: function() {
            $('.display-mode button.list').each(function (index) {
                $(this).on("click", function () {

                    if( $(this).hasClass('active') ) return

                    var event = $(this),
                        data = { 
                            'action': 'cena_list_post_ajax',
                            'query': cena_settings.posts,
                            'nonce': cena_settings.nonce
                        };
            
                    $.ajax({
                        url : cena_settings.ajaxurl, // AJAX handler
                        data : data,
                        type : 'POST',
                        beforeSend : function ( xhr ) {
                            event.closest('#content').find('.products').addClass('load-ajax')
                        },
                        success : function( data ){
                            if( data ) { 
                                event.parent()
                                .children()
                                .removeClass('active')
                            
                                event.addClass('active')

                                event.closest('#content').find('.products > div').html(data); // insert new posts

                                let products = event.closest('#content').find('div.products')
                                products 
                                    .addClass('products-list')
                                    .removeClass('products-grid')
                                    .fadeIn(300)

                                $('.woocommerce-product-gallery').each(function () {
                                    jQuery(this).wc_product_gallery()
                                })
                    
                    
                                $( document.body ).trigger( 'cena_lazy_load_image' )
                                 
                                if ( typeof tawcvs_variation_swatches_form !== 'undefined' ) {
                                    $( '.variations_form' ).tawcvs_variation_swatches_form()
                                    $( document.body ).trigger( 'tawcvs_initialized' )
                                }
                    
                    
                                // variation
                                if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
                                    $( '.variations_form' ).each( function() {
                                        $( this ).wc_variation_form().find('.variations select:eq(0)').trigger('change');
                                    });
                                }
                                

                                event.closest('#content').find('.products').removeClass('load-ajax')
     
                                $.fn.setCookie('display_mode', 'list', 0.1)

                            }
                        }
                    });

                    return false
                })
            })
        },        
        initAjaxShopDisplayModeGrid: function() {
            $('.display-mode button.grid').each(function (index) {
                $(this).on("click", function () {

                    if( $(this).hasClass('active') ) return

                    var event = $(this),
                        data = { 
                            'action': 'cena_grid_post_ajax',
                            'query': cena_settings.posts,
                            'nonce': cena_settings.nonce
                        };
            
                    $.ajax({
                        url : cena_settings.ajaxurl, // AJAX handler
                        data : data,
                        type : 'POST',
                        beforeSend : function ( xhr ) {
                            event.closest('#content').find('.products').addClass('load-ajax')
                        },
                        success : function( data ){
                            if( data ) { 
                                event.parent()
                                .children()
                                .removeClass('active')
                            
                                event.addClass('active')

                                event.closest('#content').find('.products > div').html(data); // insert new posts

                                let products = event.closest('#content').find('div.products')
                                products 
                                    .addClass('products-grid')
                                    .removeClass('products-list')
                                    .fadeIn(300)

                                $('.woocommerce-product-gallery').each(function () {
                                    jQuery(this).wc_product_gallery()
                                })
                    
                    
                                $( document.body ).trigger( 'cena_lazy_load_image' )
                                 
                                if ( typeof tawcvs_variation_swatches_form !== 'undefined' ) {
                                    $( '.variations_form' ).tawcvs_variation_swatches_form()
                                    $( document.body ).trigger( 'tawcvs_initialized' )
                                }
                    
                    
                                // variation
                                if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
                                    $( '.variations_form' ).each( function() {
                                        $( this ).wc_variation_form().find('.variations select:eq(0)').trigger('change');
                                    });
                                }
                                

                                event.closest('#content').find('.products').removeClass('load-ajax')
     
                                $.fn.setCookie('display_mode', 'grid', 0.1)

                            }
                        }
                    });

                    return false
                })
            })
        },
        initChangeQtyPageCartUpdate: function() {
            $(document).on('change', '.woocommerce-cart-form input.qty', function() {
                $("[name='update_cart']").trigger('click');
            });
        },        
        initsidebarMobile: function() {
            $(document).on('click', '.cena-sidebar-mobile-btn,.cena-close-side', function() { 
                $('body').toggleClass('show-sidebar');
            });                 
            $(document).on('click', '.close-side-widget', function() { 
                $('body').removeClass('show-sidebar');
            });            
        },
        initChangeQtyPageSingle: function() {

            $(document).on('click', '.plus, .minus', function() {
                // Get values
                var qty = $(this).closest('.quantity').find('.qty'),
                    currentVal = parseFloat(qty.val()),
                    max = qty.attr('max'),
                    min = qty.attr('min'),
                    step = qty.attr('step')

                    // Format values
                    currentVal = (!currentVal || currentVal === '' || currentVal === 'NaN') ? 0 : currentVal
                    max = (max === '' || max === 'NaN') ? '' : max
                    min = (min === '' || min === 'NaN') ? 0 : min
                    step = (step === 'any' || step === '' || step === undefined || parseFloat(step) === NaN) ? 1 : step
        
                    if ($(this).is('.plus')) {
                        if (max && (max == currentVal || currentVal > max)) {
                            qty.val(max)
                        } else {
                            qty.val(currentVal + parseFloat(step))
                        }
                    } else {
                        if (min && (min == currentVal || currentVal < min)) {
                            qty.val(min)   
                        } else if (currentVal > 0) {
                            qty.val(currentVal - parseFloat(step))
                        }
                    } 

                    // Trigger change event
                    qty.trigger('change');
            });

        }
    };

    /*Single product video iframe*/
    $.fn.tbayIframe = function(options) {
        var self = this;
        var settings = $.extend({
            classBtn: '.tbay-modalButton',
            defaultW: 640,
            defaultH: 360
        }, options);

        $(settings.classBtn).on('click', function(e) {
            var allowFullscreen = $(this).attr('data-tbayVideoFullscreen') || false;

            var dataVideo = {
                'src': $(this).attr('data-tbaySrc'),
                'height': $(this).attr('data-tbayHeight') || settings.defaultH,
                'width': $(this).attr('data-tbayWidth') || settings.defaultW
            };

            if (allowFullscreen) dataVideo.allowfullscreen = "";

            // stampiamo i nostri dati nell'iframe
            $(self).find("iframe").attr(dataVideo);
        });

        // se si chiude la modale resettiamo i dati dell'iframe per impedire ad un video di continuare a riprodursi anche quando la modale è chiusa
        this.on('hidden.bs.modal', function() {
            $(this).find('iframe').html("").attr("src", "");
        });

        return this;
    };

    /* ---------------------------------------------
         Scripts ready
         --------------------------------------------- */
    $(document).ready(function() {

        /*Product video iframe*/
        $("#productvideo").tbayIframe();

        cenaWoo.init();
    });

})(jQuery)