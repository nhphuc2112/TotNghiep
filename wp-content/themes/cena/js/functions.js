(function($) {
    "use strict";

    var cenaFunc = {
        init: function() {
            var self = this;

            self.initSidebarMobile();
            self.initDataAnimation();
            self.initTopBarDevice();
            self.initOffCanvasMenu();
            self.initPlayIsotopeMasonry();
            self.initApplyFilter();
            self.initStickyHeader();
            self.initDataTooltip();
            self.initTopbarMobile();
            self.initBackToTop();
            self.initPageLoader();
            self.initTbayMenu();
            self.initMobileMenu();
            self.initBodyLoader();
            self.initSearch();
            self.initlayzyLoadImage();
            self.initSetHiddenmodal();
            self.initBrlmpButtonSettings();
            self.initToCategoryFixed();
            self.MenuDropdownsAJAX();
            
        }, 

        MenuDropdownsAJAX: function() {
            if( typeof cena_settings === "undefined" ) return;

            this._initmenuDropdownsAJAX()
        },

        _initmenuDropdownsAJAX: function() {
            var _this = this;  
            $('body').on('mousemove', function() {     
                 $('.menu').has('.dropdown-load-ajax').each(function() {
                     var $menu = $(this);   
     
                     if ($menu.hasClass('dropdowns-loading') || $menu.hasClass('dropdowns-loaded')) {
                         return;
                     }                    
             
                     if (!_this.isNear($menu, 50, event)) {
                         return;
                     }   
                   
                     _this.loadDropdowns($menu);          
                 });
            });  
        },
       
        loadDropdowns($menu) {      
            var _this = this;      
       
            $menu.addClass('dropdowns-loading');                 
    
            var storageKey          = '',      
                unparsedData        = '',   
                menu_mobile_id      = ''; 
    
            if( $menu.closest('nav').attr('id') === 'tbay-mobile-menu-navbar' ) {
    
                if( $('#main-mobile-menu-mmenu-wrapper').length > 0 ) {
                    menu_mobile_id += '_'+ $('#main-mobile-menu-mmenu-wrapper').data('id');
                }        
       
                if( $('#main-mobile-second-mmenu-wrapper').length > 0 ) {
                    menu_mobile_id += '_'+ $('#main-mobile-second-mmenu-wrapper').data('id');
                }               
        
                storageKey      = cena_settings.storage_key + '_megamenu_mobile' + menu_mobile_id;
            }  else {   
                storageKey      = cena_settings.storage_key + '_megamenu_' + $menu.closest('nav').find('ul').data('id');
            }                            
    
            unparsedData = localStorage.getItem(storageKey);
    
           
            
            var storedData = false;     
    
            var $items = $menu.find('.dropdown-load-ajax'),
                ids    = [];
    
            $items.each(function() {
                ids.push($(this).find('.dropdown-html-placeholder').data('id'));
            });
    
       
            try {
                storedData = JSON.parse(unparsedData);
            }
            catch (e) {
                console.log('cant parse Json', e);
            }
    
            if (storedData) {
                _this.renderResults(storedData, $menu);     
    
                if( $menu.attr('id') !== 'tbay-mobile-menu-navbar' ) {  
                    $menu.removeClass('dropdowns-loading').addClass('dropdowns-loaded');
                }
            } else {   
                $.ajax({
                    url     : cena_settings.ajaxurl,     
                    data    : {
                        action: 'cena_load_html_dropdowns',
                        ids   : ids, 
                        nonce: cena_settings.nonce
                    },
                    dataType: 'json',
                    method  : 'POST',
                    success : function(response) {
                        if (response.status === 'success') {
                            _this.renderResults(response.data, $menu);
                            localStorage.setItem(storageKey, JSON.stringify(response.data));
                        } else {
                            console.log('loading html dropdowns returns wrong data - ', response.message);
                        }
                    },
                    error   : function() {
                        console.log('loading html dropdowns ajax error');
                    },
                });
            }
       
        },          
    
        renderResults(data, $menu) {
            var _this = this;      
            Object.keys(data).forEach(function(id) {
                _this.removeDuplicatedStylesFromHTML(data[id], function(html) {
    
                    let html2 = html;                
    
                    const regex1    = '<li[^>]*><a[^>]*href=["]'+window.location.href+'["]>.*?<\/a><\/li>';    
                    const regex2    = '(?:class)=\\(?:["]\W+\s*(?:\w+)\()?["]([^"]+)\\["]';    
                    let content = html.match(regex1);      
                    if( content !== null ) {
                        let $url        = content[0];   
                        let $class      = $url.match(/(?:class)=(?:["']\W+\s*(?:\w+)\()?["']([^'"]+)['"]/g)[0].split('"')[1];
                        let $class_new  = $class+' active';
                        let $url_new    = $url.replace($class, $class_new);  

                        html2        = html2.replace($url, $url_new);          
                    }  
    
                    $menu.find('[data-id="' + id + '"]').replaceWith(html2);
    
                    if( $menu.attr('id') !== 'tbay-mobile-menu-navbar' ) {  
                        $menu.addClass('dropdowns-loaded');
        
                        setTimeout(function() {
                            $menu.removeClass('dropdowns-loading');
                        }, 1000);
                    }
                });
            });
           
        },
    
        isNear($element, distance, event) {
            var left   = $element.offset().left - distance,
                top    = $element.offset().top - distance,
                right  = left + $element.width() + (2 * distance),
                bottom = top + $element.height() + (2 * distance),
                x      = event.pageX,
                y      = event.pageY;
    
            return (x > left && x < right && y > top && y < bottom);
        },      
    
        removeDuplicatedStylesFromHTML(html, callback) {
    
            const regex = /<style>.*?<\/style>/mg;     
    
            let output = html.replace(regex, "");
            callback(output);      
            return;
    
        },


        initSidebarMobile: function() {
            $(document).on('click', '.cena-sidebar-mobile-btn', function() { 
                $('body').addClass('show-sidebar');
            });  

            $(document).on('click', '.close-side-widget, .cena-close-side', function() { 
                $('body').removeClass('show-sidebar');
            });            
        },
        initDataAnimation: function() {
            $("[data-progress-animation]").each(function() {
            let $this = $(this);
            $this.appear(function() {
                let delay = ($this.attr("data-appear-animation-delay") ? $this.attr("data-appear-animation-delay") : 1);
                if(delay > 1) $this.css("animation-delay", delay + "ms");
                setTimeout(function() { $this.animate({width: $this.attr("data-progress-animation")}, 800);}, delay);
            }, {accX: 0, accY: -50});
          });
        },
        initTopBarDevice: function() {
            let scroll          = $(window).scrollTop();
            let objectSelect    = $(".topbar-device-mobile").height();
            let mobileoffset    = $("#tbay-mobile-menu").height();
            let scrollmobile    = $(window).scrollTop();
            if (scroll <= objectSelect) {
                $(".topbar-device-mobile").addClass("active");
            } else {
                $(".topbar-device-mobile").removeClass("active");
            }        

            if (scrollmobile == 0) {
                $("#tbay-mobile-menu").addClass("offsetop");
                $("body").addClass("offsetop-menu-mobile");
            } else {
                $("#tbay-mobile-menu").removeClass("offsetop");
                $("body").removeClass("offsetop-menu-mobile");
            }
        },
        initOffCanvasMenu: function() {
            //Offcanvas Menu
            $('[data-toggle="offcanvas"], .btn-offcanvas').on('click', function () {
                $('.row-offcanvas').toggleClass('active')           
            });
            
            $("#main-menu-offcanvas .caret").on('click', function(){
                $("#main-menu-offcanvas .dropdown").removeClass('open');
                $(this).parent().addClass('open');
                return false;
            } );

            //counter up
            if($('.counterUp').length > 0){
                $('.counterUp').counterUp({
                    delay: 10,
                    time: 800
                });
            }
        },
        initPlayIsotopeMasonry: function() {
            $('.isotope-items,.blog-masonry').each(function(){  
                let $container = $(this);
                
                $container.imagesLoaded( function(){
                    $container.isotope({
                        itemSelector : '.isotope-item',
                        transformsEnabled: true         // Important for videos
                    }); 
                });
            });
        },
        initApplyFilter: function() {
           /*---------------------------------------------- 
             *    Apply Filter        
             *----------------------------------------------*/
            jQuery('.isotope-filter li a').on('click', function(){
               
                var parentul = jQuery(this).parents('ul.isotope-filter').data('related-grid');
                jQuery(this).parents('ul.isotope-filter').find('li a').removeClass('active');
                jQuery(this).addClass('active');
                var selector = jQuery(this).attr('data-filter'); 
                jQuery('#'+parentul).isotope({ filter: selector }, function(){ });
                
                return(false);
            });
        },
        initStickyHeader: function() {
            //Sticky Header
            var tbay_header = jQuery('#tbay-header');
            if( tbay_header.hasClass('main-sticky-header') ) {
                var CurrentScroll = 0;
                var tbay_width = jQuery(window).width();
                var header_height = tbay_header.height();
                var header_height_fixed = jQuery('#tbay-header.sticky-header').height();
                $(window).scroll(function() {
                    if(tbay_width > 992) {
                        var NextScroll = jQuery(this).scrollTop();
                        if (NextScroll > header_height) {
                            tbay_header.addClass('sticky-header');
                            tbay_header.parent().css('margin-top', header_height);
                        } else {
                            tbay_header.removeClass('sticky-header');
                            tbay_header.parent().css('margin-top', 0);
                        }
                        CurrentScroll = NextScroll;
                    }
                });
                $(window).resize(function(event) {
                    if(tbay_width < 1024){
                        tbay_header.removeClass('sticky-header');
                    }
                });
            }
        },
        initDataTooltip: function() {
            $('[data-toggle="tooltip"]').tooltip();
        },
        initTopbarMobile: function() {
            $('.topbar-mobile .dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });
        },
        initBackToTop: function() {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 400) {
                    $('.tbay-to-top').addClass('active');
                    $('.tbay-category-fixed').addClass('active');
                } else {
                    $('.tbay-to-top').removeClass('active');
                    $('.tbay-category-fixed').removeClass('active');
                }
            });
            $('#back-to-top').on('click', function () {
                $('html, body').animate({scrollTop: '0px'}, 800);
                return false;
            });
        },
        initPageLoader: function() {
            $(window).on('load', function(){
                $('.tbay-page-loader').delay(100).fadeOut(400, function () {
                    $('body').removeClass('tbay-body-loading');
                    $(this).remove();
                });

            });
        },
        initTbayMenu: function() {
            $("#tbay-mobile-menu.v8").parent().addClass('v8');

            $(".treeview-menu .menu").treeview({
                animated: 300,
                collapsed: true,
                unique: true,
                persist: "location"
            });
            
            // Treeview for Mobile Menu
            $(".navbar-offcanvas #main-mobile-menu").treeview({
                animated: 300,
                collapsed: true,
                unique: true,
                hover: false
            });
            
            $(".category-inside-content #category-menu").addClass('treeview');
            $(".category-inside-content #category-menu").treeview({
                animated: 300,
                collapsed: true,
                unique: true,
                persist: "location"
            });
            
            // Category Menu - Huy Pham
            $(".category-inside .category-inside-title").on("click", function() {
                $(this).parents('.category-inside').find(".category-inside-content").slideToggle("fast");
                $(this).parents('.category-inside').toggleClass("open");
            });
            
            
            $("ul.treeview ul.sub-menu").removeClass('dropdown-menu');
            
            var demo_content  = $(".tbay-demo");
            
            $(".tbay-switch").on('click', function(){
                $('#wrapper-container').toggleClass('show-demo');
                if(demo_content.hasClass("active")) {
                    demo_content.removeClass("active");
                } else {
                    demo_content.addClass("active");
                }
            });
        },
        initMobileMenu: function() {
            // mobile menu
            $('[data-toggle="offcanvas"], .btn-offcanvas').on('click', function () { 
                $('#wrapper-container').toggleClass('active');
                $('body').toggleClass('show-mobile-menu');
                $('#tbay-mobile-menu').toggleClass('active');           
            });
            $("#main-mobile-menu .caret").on('click', function(){
                $("#main-mobile-menu .dropdown").removeClass('open');
                $(this).parent().addClass('open');
                return false;
            } );    
        },
        initBodyLoader: function() {
            // preload page
            var $body = $('body');
            if ( $body.hasClass('tbay-body-loader') ) {

                setTimeout(function() {
                    $body.removeClass('tbay-body-loader');
                    $('.tbay-page-loader').fadeOut(250);
                }, 300);
            }
        },
        initSearch: function() {
            $('.button-show-search').on("click", function(){
                $('.tbay-search-form').addClass('active');
                return false;
            });
            $('.button-hidden-search').on("click", function(){
                $('.tbay-search-form').removeClass('active');
                return false;
            });
        },
        initlayzyLoadImage: function() {
            $(window).off('scroll.unveil resize.unveil lookup.unveil');
            imageProduct();
            imageLoad();


            function imageProduct () {
                var images = $('.product-image:not(.image-loaded) .unveil-image, .tbay-gallery-varible:not(.image-loaded) .unveil-image');
                if (images.length) {
                    images.unveil(1, function() {
                        $(this).on('load', function(){
                            $(this).parents('.product-image, .tbay-gallery-varible').first().addClass('image-loaded');
                            $(this).removeAttr('data-src');
                        });
                    });
                } 
            }

            function imageLoad () {
                var images = $('.tbay-image-loaded:not(.image-loaded) .unveil-image, .tbay-image-loaded');
                if (images.length) {
                    images.unveil(1, function() {
                        $(this).on('load', function() {
                            $(this).parents('.tbay-image-loaded:not(.image-loaded) .unveil-image').first().addClass('image-loaded');
                            $(this).removeAttr('data-src');
                        });
                    });
                } 
            }
        },
        initGetHiddenmodal: function() {
            setTimeout(function(){
                var hiddenmodal = $.fn.getCookie('hiddenmodal');
                if (hiddenmodal == "") {
                    jQuery('#popupNewsletterModal').modal('show');
                }
            }, 3000);
        },        
        initSetHiddenmodal: function() {
            $('#popupNewsletterModal').on('hidden.bs.modal', function () {
                $.fn.setCookie('hiddenmodal', 1, 0.1);
            });
        },
        initBrlmpButtonSettings: function() {
            let _this = this;
            $(document).on( 'click', '.br_lmp_button_settings .lmp_button', function (event) {

                setTimeout(
                function() {
                    _this.initlayzyLoadImage();
                },
                5000);

            });
        },
        initToCategoryFixed: function() {
            let $with = $(window).width();
            let $main_container  = $(".container").width();
            let $width_sum      = ($with - $main_container)/2;

            if( $width_sum >= 80 ) {
                let $width_sum2     =    $width_sum  - 80;

                $('.tbay-category-fixed').css('left', $width_sum2);
                $('.tbay-to-top').css('right', $width_sum2); 

                $('.tbay-category-fixed').css('display', 'block');
                $('.tbay-to-top').css('display', 'block');

            } else {

                $('.tbay-category-fixed').css('display', 'none');
                $('.tbay-to-top').css('display', 'none');

            }
        }
    };

    $.fn.setCookie = function(cname, cvalue, exdays){
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires+";path=/";
    };    


    $.fn.getCookie = function(cname){
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    };


    /** PRELOADER **/
    if ( $.fn.jpreLoader ) {
        var $preloader = $( '.js-preloader' );

        $preloader.jpreLoader({
            // autoClose: false, 
        }, function() {
            $preloader.addClass( 'preloader-done' );
            $( 'body' ).trigger( 'preloader-done' );
            $( window ).trigger( 'resize' );
        });
    };

    $.fn.wrapStart = function(numWords){
        return this.each(function(){
            var $this = $(this);
            var node = $this.contents().filter(function(){
                return this.nodeType == 3;
            }).first(),
            text = node.text().trim(),
            first = text.split(' ', 1).join(" ");
            if (!node.length) return;
            node[0].nodeValue = text.slice(first.length);
            node.before('<b>' + first + '</b>');
        });
    }; 

    $.fn.unveil = function(threshold, callback) {

        var $w = $(window),
            th = threshold || 0,
            retina = window.devicePixelRatio > 1,
            attrib = retina? "data-src-retina" : "data-src",
            images = this,
            loaded;

        this.one("unveil", function() {
          var source = this.getAttribute(attrib);
          source = source || this.getAttribute("data-src");
          if (source) {
            this.setAttribute("src", source);
            if (typeof callback === "function") callback.call(this);
          }
        });

        function unveil() {
          var inview = images.filter(function() {
            var $e = $(this),
                wt = $w.scrollTop() - 10,
                wb = wt + $w.height(),
                et = $e.offset().top,
                eb = et + $e.height();

            return eb >= wt - th && et <= wb + th;
          });

          loaded = inview.trigger("unveil");
          images = images.not(loaded);
        }

        $w.on("scroll.unveil resize.unveil lookup.unveil", unveil);

        unveil();

        return this;

    };

    /* ---------------------------------------------
         Scripts ready
         --------------------------------------------- */
    $(document).ready(function() {
        $(document.body).on('cena_lazy_load_image', () => {
            cenaFunc.initlayzyLoadImage();
        });

        cenaFunc.init();
    });
    $(window).resize(function() {
        cenaFunc.initToCategoryFixed();
    });
    
    $(window).scroll(function() {
        cenaFunc.initTopBarDevice();
    });

    jQuery(window).on('load', function(){
        cenaFunc.initGetHiddenmodal();
    });

})(jQuery)