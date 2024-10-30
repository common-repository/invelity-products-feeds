(function($) {
    'use strict';

    $(function() {

        // XML Feed Wrapper
        $(document).on('change', '#provider', function() {
            var type = $(this).val();
            var provider = $("#provider").val();
            var format = $('#feedType');

            if (type == 'Heureka') {
                $(".heurekaCat").show();
                $(".wf_csvtxt").hide();
                $(".custom_label").hide();
                $(".wf_shipping").hide();
            } else if (type == 'Facebook') {
                $(".wf_csvtxt").show();
                $(".heurekaCat").hide();
                $(".custom_label").hide();
                $(".wf_shipping").hide();
            } else if (type == '') {
                $(".wf_csvtxt").hide();
                $(".heurekaCat").hide();
                $(".custom_label").hide();
                $(".wf_shipping").hide();
            } else if (type == 'Google Merchant Center') {
                $(".wf_csvtxt").show();
                $(".heurekaCat").hide();
                $(".custom_label").hide();
                $(".wf_shipping").show();
            } else if (type == 'Custom Feed') {
                $(".wf_csvtxt").hide();
                $(".heurekaCat").hide();
                $(".custom_label").hide();
                $(".wf_shipping").hide();
            } else if (type == 'Dynamic Search Ads') {
                $(".wf_csvtxt").hide();
                $(".heurekaCat").hide();
                $(".custom_label").show();
                $(".wf_shipping").hide();
            }

            // if (provider == 'Google Merchant Center' || provider == 'Facebook' || provider == 'Custom Feed' || provider =='Dynamic Search Ads' && type != "") {
            // 	format.val('csv');
            // } else if (provider == 'Heureka'){
            // 	format.val('xml');
            // }
        });
        if ($('#language').length > 0) {

            $(document).on('change', '#language', function() {
                var type = $(this).val();
                console.log(type);
                console.log('u');

                var url = MyAutocomplete.url + "?action=change_language";

                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'GET',
                    data: {
                        "lang": type,
                    },
                    success: function(result) {
                        //console.log(result);
                        $('#multiselect_cat').empty();
                        $('#multiexcerptselect_cat').empty();
                        $.each(result, function(i, item) {
                            console.log('loading items');
                            console.log(item);

                            $('#multiselect_cat').append('<option value="' + item.id + '">' + item.name + '</option>');
                            $('#multiexcerptselect_cat').append('<option value="' + item.id + '">' + item.name + '</option>');

                        })
                        $('#multiselect_cat').multiselect('rebuild');
                        $('#multiexcerptselect_cat').multiselect('rebuild');
                        $('.multiselect.dropdown-toggle.btn.btn-default').css('border', '1px solid green');

                       // console.log('update catgeories');

                        // console.log(result);
                    }
                })

            });

        } else {
            //var type = 'sk';
            var url = MyAutocomplete.url + "?action=change_language";

            $.ajax({
                url: url,
                dataType: 'json',
                type: 'GET',
                data: {
                    //    "lang": type,
                },
                success: function(result) {
                  //  console.log(result);
                    $('#multiselect_cat').empty();
                    $('#multiexcerptselect_cat').empty();
                    $.each(result, function(i, item) {
                        // console.log('loading items');
                        //  console.log(item);

                        $('#multiselect_cat').append('<option value="' + item.id + '">' + item.name + '</option>');
                        $('#multiexcerptselect_cat').append('<option value="' + item.id + '">' + item.name + '</option>');

                    })
                    $('#multiselect_cat').multiselect('rebuild');
                    $('#multiexcerptselect_cat').multiselect('rebuild');
                    //  console.log('update catgeories');

                    // console.log(result);
                }
            })


        }
    })


    $(function() {
            $(document).on('click', '.delete_feed', function() {
                alert('Are you sure?');
            });
        });
        //$('#keyword').editableSelect();

    /*	$('.product_select').select2({
        ajax: {
          url:  MyAutocomplete.url + "?action=my_search",
        }
      });*/

    $(document).on('click', '.product_select', function() {
        var url = MyAutocomplete.url + "?action=my_search";

        $('.product_select').select2({
            ajax: {
                url: url,
                dataType: 'json',
                type: 'GET',
                data: function(params) {
                    return {
                        q: params.term, // search term
                    };
                },
                processResults: function(data) {

                    return {
                        results: $.map(data, function(obj) {
                            return {
                                id: obj.id,
                                text: obj.name
                            };
                        })
                    };
                }
            },
            minimumInputLength: 3,


        });




    });

    $(document).ready(function() {
        $('#multiselect_cat').multiselect({
            includeSelectAllOption: true
        });
    });

    $(document).ready(function() {
        $('#multiexcerptselect_cat').multiselect({
            includeSelectAllOption: true
        });
    });

    $('[data-toggle="feed_tooltip"]').tooltip();



    /*	$(document).on('click', '.product_cat_select', function () {
        var url= MyAutocomplete.url + "?action=my_search_categories";

        $('.product_cat_select').select2({
          multiple: true,
          minimumInputLength: 1,
          ajax              : {
            url           : url,
            dataType      : 'json',
            type          : 'GET',
            data: function (term) {
              return {
                term: term
              };
            },
            results: function (data) {
              return {
                results: $.map(data, function (item) {
                  return {
                    name: item.name,
                    id  : item.id
                  }
                })
              };
            }
          }
        });

      });*/


    /*$(document).on('click', '.product_cat_select',function() {
      var url= MyAutocomplete.url + "?action=my_search_categories";

      $('.product_cat_select').select2({
        //multiple:true,
        ajax: {
          url: url  ,
          dataType: 'json',
          type: 'GET',
          data: function(params) {
            return {
              q: params.term, // search term
            };
          },
          processResults: function (data) {
            return {
              results: $.map(data, function(obj) {
                return { id: obj.id, text: obj.name };
              })
            };
          }
        },
        minimumInputLength: 1,


      });


    });*/

    /*
        var t = $( this ).val();
        ajax: {
          url: function (params) {
            return '/some/url/' + params.term;
          }
        }*/

    /*	var product_select = $( ".product_select" );
 //	console.log('dd');
   var url = MyAutocomplete.url + "?action=my_search";
   console.log(url);

  product_select.autocomplete({
     minLength: 1,
     source: url
   });
  product_select.attr('autocomplete','on');
   //console.log(my_search);*/
    //});


    /*$(function () {
      $.widget("custom.combobox", {
        _create: function () {
          this.wrapper = $("<span>")
            .addClass("custom-combobox")
            .insertAfter(this.element);

          this.element.hide();
          this._createAutocomplete();
          this._createShowAllButton();

        },


        _createAutocomplete: function () {
          var selected = this.element.children(":selected"),
              value    = selected.val() ? selected.text() : "";


          this.input = $("<input>")
            .appendTo(this.wrapper)
            .val(value)
            .attr("title", "")
            .attr("name", "product_cat")
            .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
            .autocomplete({
              delay    : 0,
              minLength: 0,
              source   : $.proxy(this, "_source")
            })
            .tooltip({
              classes: {
                "ui-tooltip": "ui-state-highlight"
              }
            });

          this._on(this.input, {
            autocompleteselect: function (event, ui) {
              ui.item.option.selected = true;
              this._trigger("select", event, {
                item: ui.item.option
              });
            },

            autocompletechange: "_removeIfInvalid"
          });
        },

        _createShowAllButton: function () {
          var input   = this.input,
              wasOpen = false;

          $("<a>")
            .attr("tabIndex", -1)
            .attr("title", "Show All Items")
            //.tooltip()
            .appendTo(this.wrapper)
            .button({
              icons: {
                primary: "ui-icon-triangle-1-s"
              },
              text : false
            })
            .removeClass("ui-corner-all")
            .addClass("custom-combobox-toggle ui-corner-right")
            .on("mousedown", function () {
              wasOpen = input.autocomplete("widget").is(":visible");
            })
            .on("click", function () {
              input.trigger("focus");

              // Close if already visible
              if (wasOpen) {
                return;
              }

              // Pass empty string as value to search for, displaying all results
              input.autocomplete("search", "");
            });
        },

        _source: function (request, response) {
          var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
          response(this.element.children("option").map(function () {
            var text = $(this).text();
            if (this.value && (!request.term || matcher.test(text)))
              return {
                label : text,
                value : text,
                option: this
              };
          }));
        },

        _removeIfInvalid: function (event, ui) {

          // Selected an item, nothing to do
          if (ui.item) {
            return;
          }

          // Search for a match (case-insensitive)
          var value          = this.input.val(),
              valueLowerCase = value.toLowerCase(),
              valid          = false;
          this.element.children("option").each(function () {
            if ($(this).text().toLowerCase() === valueLowerCase) {
              this.selected = valid = true;
              return false;
            }
          });

          // Found a match, nothing to do
          if (valid) {
            return;
          }

          // Remove invalid value
          this.input
            .val("")
            .attr("title", value + " didn't match any item")
            .tooltip("open");
          this.element.val("");
          this._delay(function () {
            this.input.tooltip("close").attr("title", "");
          }, 2500);
          this.input.autocomplete("instance").term = "";
        },

        _destroy: function () {
          this.wrapper.remove();
          this.element.show();
        }
      });
      console.log(this.wrapper);
      $("#combobox").combobox();
    });*/


})(jQuery);