(function($) {
    "use strict";

    jQuery(document).ready(function ($){
        var acs_action = 'cena_autocomplete_search';
        var $t = jQuery("input[name=s]");

        $t.on("focus", function(){
            $(this).autocomplete({
                source: function(req, response){
                    $.ajax({
                        url: cena_settings.ajaxurl+'?callback=?&action='+acs_action,
                        dataType: "json",
                        data: {
                            term :          req.term,
                            category:       this.element.closest("form").find("select").val(),
                            post_type :     this.element.closest("form").find(".post_type").val(),
                        },
                        success: function(data) {
                            response(data); 
                        }
                    });
                },
                appendTo: $(this).closest('form').find('.tbay-search-result-wrapper'),
                autoFocus: true, 
                search: function(event, ui) {
                    $(event.currentTarget).closest('.tbay-search-form').addClass('load');
                },
                select: function(event, ui) {
                    window.location.href = ui.item.link;
                },
                create: function () {

                    jQuery(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                        var string = '';

                        ul.addClass(item.style);


                        if (item.image != '') {
                            var string = '<a href="' + item.link + '" title="' + item.label + '"><img src="' + item.image + '" ></a>';
                        }
                        string = string + '<div class="group"><div class="name"><a href="' + item.link + '" title="' + item.label + '">' + item.label + '</a></div>';

                        if (item.price != '') {
                            string = string + '<div class="price">' + item.price + '</div></div> ';
                        }
                        var strings = jQuery("<li>").append(string).appendTo(ul);

                        return strings;
                    }

                    jQuery(this).data('ui-autocomplete')._renderMenu = function (ul, items) {
                        var that = this;
                        jQuery.each(items, function (index, item) {
                            that._renderItemData(ul, item);
                        })

                        if( items[0].view_all ) {
                            ul.append(`<li class="list-header ui-menu-item"> ${items[0].result} <a class="search-view-all" href="javascript:void(0)">${cena_settings.view_all}</a></li>`);
                        } else { 
                            ul.append(`<li class="list-header ui-menu-item"> ${items[0].result} </li>`);
                        }

                        $( document.body ).trigger( 'cena_search_view_all' )
                    }

                },
                response: function(event, ui) {
                    // ui.content is the array that's about to be sent to the response callback.
                    if (ui.content.length === 0) {
                        $(".tbay-preloader").text(cena_settings.no_results);
                        $(".tbay-preloader").addClass('no-results');
                    } else {
                        $(".tbay-preloader").empty();
                        $(".tbay-preloader").removeClass('no-results');
                    }
                },
                open: function(event, ui) {
                    $(event.target).parents('.tbay-search-form').removeClass('load');
                    $(event.target).parents('.tbay-search-form').addClass('active');
                },
                close: function() {
                }
            });
        });

        $(document.body).on('cena_search_view_all', () => {
            $('.search-view-all').on("click", function() {
                jQuery(this).parents('form').submit();
             });
        });

        $('.tbay-preloader').on('click', function(){      
            $(".tbay-preloader").empty();
            $(this).parents('.tbay-search-form').removeClass('active').removeClass('load');          
            $(this).parents('.tbay-search-form').find('input[name=s]').val('');                  
        });

    });
})(jQuery)