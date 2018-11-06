jQuery(document).ready(function ($) {


    $('.add-amount').on('click', function () {

        var field = '<p><input type="number" name="voucher_settings[voucher_amounts][]" required><button class="button remove-amount" type="button">Remove</button></p>';

        $("#more-amounts").append(field);


        //re initialize date picker for dynamic element
        $(".datepicker").datepicker({
            dateFormat: 'dd-mm-yy'
        });


    });


    $(document).on('click', '.remove-amount', function () {

        $(this).parent('p').remove();

    });


    $("#more-amounts").sortable();

    $(".phorest-colour").wpColorPicker();


    /*
     Admin JS tabs
     */


    $(".gr-tabs-nav > .nav-tab").on("click", function (e) {

        e.preventDefault();

        $(this).addClass("nav-tab-active");
        $(this).siblings().removeClass("nav-tab-active");

        var section_id = $(this).data('id');

        var active_section = $('#' + section_id);

        active_section.show();
        active_section.siblings().hide();
    });



    /*
    Uploader
     */
    $('body').on('click', '.phorest-uploader', function (e) {
        e.preventDefault();
        return_field = $(this).prev('input');


        var image = wp.media({
            title: 'Upload',
            multiple: false
        }).open()
            .on('select', function (e) {
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                $(return_field).val(image_url);


            });


    });

});