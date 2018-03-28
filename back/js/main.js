/*
 *  Document   : main.js
 *  Author     : pixelcave
 *  Description: Custom scripts and plugin initializations (available to all pages)
 *
 *  Feel free to remove the plugin initilizations from uiInit() if you would like to
 *  use them only in specific pages. Also, if you remove a js plugin you won't use, make
 *  sure to remove its initialization from uiInit().
 */

var webApp = function() {

    // Cache in variables some often used jquery objects
    var body    = $('body');
    var header  = $('header');

    /* Initialization UI Code */
    var uiInit = function () {

        // Add the correct copyright year at the footer
        var yearCopy = $('#year-copy'), d = new Date();
        if (d.getFullYear() === 2013) { yearCopy.html('2013'); } else { yearCopy.html('2013-' + d.getFullYear().toString().substr(2,2)); }

        // Set min-height to #page-content, so that footer is visible at the bottom if there is not enough content
        var pageContent = $('#page-content');

        pageContent.css('min-height', $(window).height() -
            (header.outerHeight() + $('#pre-page-content').outerHeight() + $('footer').outerHeight()) + 'px');

        $(window).resize(function() {
            pageContent.css('min-height', $(window).height() -
                (header.outerHeight() + $('#pre-page-content').outerHeight() + $('footer').outerHeight()) + 'px');
        });

        // Initialize Sticky Sidebar and position it correctly
        if ($('#page-sidebar').hasClass('sticky')) { stickySidebar('create'); }

        // Toggle Side content
        $('#toggle-side-content').click(function(){ body.toggleClass('hide-side-content'); });

        // Select/Deselect all checkboxes in tables
        $('thead input:checkbox').click(function() {
            var checkedStatus = $(this).prop('checked'), table = $(this).closest('table');
            $('tbody input:checkbox', table).each(function() { $(this).prop('checked', checkedStatus); });
        });

        // Initialize tabs
        $('[data-toggle="tabs"] a').click(function (e) { e.preventDefault(); $(this).tab('show'); });

        // Initialize Image Gallery/Popups
        $('[data-toggle="lightbox-gallery"]').magnificPopup({
            delegate: 'a.gallery-link',
            type: 'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
                tPrev: 'Previous',
                tNext: 'Next',
                tCounter: '<span class="mfp-counter">%curr% of %total%</span>'
            }
        });

        // Collapsible block
        $('[data-toggle="block-collapse"]').click(function(){
            if ( $(this).hasClass('active') ) {
                $(this).parents('.block').find('.block-content').slideDown(250);
                $(this).removeClass('active').html('<i class="fa fa-arrow-up"></i>');
            }
            else
            {
                $(this).parents('.block').find('.block-content').slideUp(250);
                $(this).addClass('active').html('<i class="fa fa-arrow-down"></i>');
            }
        });

        // Initialize Image Popup
        $('[data-toggle="lightbox-image"]').magnificPopup({ type: 'image' });

        // Initialize Tooltips
        $('[data-toggle="tooltip"], .enable-tooltip').tooltip({ container: 'body', animation: false });

        // Initialize Popovers
        $('[data-toggle="popover"]').popover({ container: 'body', animation: false });

        // Initialize Chosen
        $(".select-chosen").chosen();

        // Initialize elastic
        $('textarea.textarea-elastic').elastic();

        // Initialize wysihtml5
        $('textarea.textarea-editor').wysihtml5();

        // Initialize Colorpicker
        $('.input-colorpicker').colorpicker();

        // Initialize TimePicker
        $('.input-timepicker').timepicker();

        // Initialize DatePicker
        $('.input-datepicker').datepicker();
        $('.input-datepicker-close').datepicker().on('changeDate', function(e){ $(this).datepicker('hide'); });

        // Initialize DateRangePicker
        $('.input-daterangepicker').daterangepicker();

        // iCheck (Checkbox & Radio themed)
        $('.input-themed').iCheck({ checkboxClass: 'icheckbox_square-grey', radioClass: 'iradio_square-grey' });

        // Form Sliders
        $('.slider').slider();

        // Initialize Placeholder
        $('input, textarea').placeholder();
    };

    /* Sticky Sidebar functionality */
    var stickySidebar = function (mode) {
        // Cache some often used jquery objects
        var sideScrollableCon = $('#page-sidebar .slimScrollDiv');
        var sideScrollable    = $('.side-scrollable');

        // Default height for tablets and phones
        var innerHeight       = 380;

        // Modes
        if ((mode == 'create')) {
            // If there is a div with the class .side-scrollable initialize slimscroll
            if (sideScrollable.length) {
                // First, set the height of the sidebar
                innerHeight = stickySidebar('resize');

                // Initialize Slimscroll for the first time
                sideScrollable.slimScroll({ height: innerHeight, color: '#fff', size: '3px', touchScrollStep: 100 });

                // Resize sidebar height on windows scroll and resize
                $(window).scroll(stickyResize);
                $(window).resize(stickyResize);
            }

            // On window scroll set sidebar position
            $(window).scroll(stickyPosition);
        } else if (mode == 'resize') {
            // Calculate height
            if ($(window).width() > 979) {
                if (body.hasClass('header-fixed-top') || body.hasClass('header-fixed-bottom') || $(this).scrollTop() < 41) {
                    innerHeight = $(window).height() - 41;
                } else {
                    innerHeight = $(window).height();
                }
            }

            // Set height to the sidebar scroll containers
            if (sideScrollableCon)
                sideScrollableCon.css('height', innerHeight);

            sideScrollable.css('height', innerHeight);

            return innerHeight;
        } else if (mode == 'destroy') {
            // Remove Slimscroll by replacing .slimScrollDiv with .side-scrollable
            sideScrollable.parent().replaceWith(sideScrollable);

            // Remove inline styles from the new .side-scrollable div
            $('.side-scrollable').removeAttr('style');

            // Disable functions running on window scroll and resize
            $(window).off('scroll', stickyPosition);
            $(window).off('scroll', stickyResize);
            $(window).off('resize', stickyResize);
        }
    };

    // Helper functions for sticky sidebar functionality
    var stickyResize    = function() { stickySidebar('resize'); };
    var stickyPosition  = function() {
        if (!body.hasClass('header-fixed-bottom') && !body.hasClass('header-fixed-top')) {
            if ($(this).scrollTop() < 41) {
                $('#page-sidebar').css('top', '41px');
            } else if ($(this).scrollTop() > 41) {
                $('#page-sidebar').css('top', '0');
            }
        } else {
            if ($(window).width() > 979) {
                $('#page-sidebar').removeAttr('style');
            }
        }
    };

    /* Primary navigation functionality */
    var primaryNav = function () {

        // Animation Speed, change the values for different results
        var upSpeed         = 250;
        var downSpeed       = 300;

        // Get all primary and sub navigation links
        var menuLinks       = $('.menu-link');
        var submenuLinks    = $('.submenu-link');

        // Initialize number indicators on menu links
        menuLinks.each(function(n, e){
            $(e).append('<span>' + $(e).next('ul').find('a').not('.submenu-link').length + '</span>');
        });

        // Initialize number indicators on submenu links
        submenuLinks.each(function(n, e){
            $(e).append('<span>' + $(e).next('ul').children().length + '</span>');
        });

        // Primary Accordion functionality
        menuLinks.click(function(){
            var link = $(this);

            if (link.parent().hasClass('active') !== true) {
                if (link.hasClass('open')) {
                    link.removeClass('open').next().slideUp(upSpeed);
                }
                else {
                    $('.menu-link.open').removeClass('open').next().slideUp(upSpeed);
                    link.addClass('open').next().slideDown(downSpeed);
                }
            }

            return false;
        });

        // Submenu Accordion functionality
        submenuLinks.click(function(){
            var link = $(this);

            if (link.parent().hasClass('active') !== true) {
                if (link.hasClass('open')) {
                    link.removeClass('open').next().slideUp(upSpeed);
                }
                else {
                    link.closest('ul').find('.submenu-link.open').removeClass('open').next().slideUp(upSpeed);
                    link.addClass('open').next().slideDown(downSpeed);
                }
            }

            return false;
        });
    };

    /* Scroll to top link */
    var scrollToTop = function() {
        // Get link
        var link = $('#to-top');

        $(window).scroll(function(){
            // If the user scrolled a bit (150 pixels) show the link
            if ($(this).scrollTop() > 150) {
                link.fadeIn(100);
            } else {
                link.fadeOut(100);
            }
        });

        // On click get to top
        link.click(function(){
            $('html, body').animate({ scrollTop: 0 }, 150);
            return false;
        });
    };

    /* Template Options, change features and colors */
    var templateOptions = function () {

        /*
         * Color Themes
         */
        var colorList = $('.theme-colors');
        var themeLink = $('#theme-link');
        var theme;

        if (themeLink.length) {
            theme = themeLink.attr('href');

            $('li', colorList).removeClass('active');
            $('a[data-theme="' + theme + '"]', colorList).parent('li').addClass('active');
        }

        $('a', colorList).mouseenter(function(e){
            // Get theme name
            theme = $(this).data('theme');

            $('li', colorList).removeClass('active');
            $(this).parent('li').addClass('active');

            if (theme === 'default') {
                if (themeLink.length) {
                    themeLink.remove();
                    themeLink = $('#theme-link');
                }
            } else {
                if (themeLink.length) {
                    themeLink.attr('href', theme);
                } else {
                    $('link[href="css/themes.css"]').before('<link id="theme-link" rel="stylesheet" href="' + theme + '">');
                    themeLink = $('#theme-link');
                }
            }
        });

        /*
         * Sidebar Options
         */
        var pageSidebar = $('#page-sidebar');
        var checkSticky = $('#theme-sidebar-sticky');

        // Check sidebar state and set options
        if (pageSidebar.hasClass('sticky')) {
            checkSticky.iCheck('check');
        }

        // Sticky Sidebar Checkbox Checked
        checkSticky.on('ifChecked', function(e){
            pageSidebar.addClass('sticky');
            stickySidebar('create');
        });

        // Sticky Sidebar Checkbox Unchecked
        checkSticky.on('ifUnchecked', function(e){
            pageSidebar.removeClass('sticky');
            stickySidebar('destroy');
        });

        /*
         * Header Options
         */
        var checkTop    = $('#theme-header-top');
        var checkBottom = $('#theme-header-bottom');

        // Check header state and set options
        if (header.hasClass('navbar-fixed-top')) {
            checkTop.iCheck('check');
            headerOptions('top');
        } else if (header.hasClass('navbar-fixed-bottom')) {
            checkBottom.iCheck('check');
            headerOptions('bottom');
        }

        // Fixed Top Checkbox Checked
        checkTop.on('ifChecked', function(e){
            checkBottom.iCheck('uncheck');
            headerOptions('top');
        });

        // Fixed Top Checkbox Unchecked
        checkTop.on('ifUnchecked', function(e){
            headerOptions('static');
        });

        // Fixed Bottom Checkbox Checked
        checkBottom.on('ifChecked', function(e){
            checkTop.iCheck('uncheck');
            headerOptions('bottom');
        });

        // Fixed Bottom Checkbox Unchecked
        checkBottom.on('ifUnchecked', function(e){
            headerOptions('static');
        });

        /*
         * Full Width
         */
        var pageCon     = $('#page-container');
        var checkfull   = $('#theme-page-full');

        // Check page state and set options
        if (pageCon.hasClass('full-width')) {
            checkfull.iCheck('check');
        }

        // Fixed Bottom Checkbox Checked
        checkfull.on('ifChecked', function(e){
            pageCon.addClass('full-width');
        });

        // Fixed Bottom Checkbox Unchecked
        checkfull.on('ifUnchecked', function(e){
            pageCon.removeClass('full-width');
        });
    };

    /* Header helper function for setting position (top, bottom, static) */
    var headerOptions = function(mode) {

        if (mode === 'top') { // Header Fixed Top
            body.removeClass('header-fixed-bottom').addClass('header-fixed-top');
            header.removeClass('navbar-fixed-bottom').addClass('navbar-fixed-top');
        } else if (mode === 'bottom') { // Header Fixed Bottom
            body.removeClass('header-fixed-top').addClass('header-fixed-bottom');
            header.removeClass('navbar-fixed-top').addClass('navbar-fixed-bottom');
        } else if (mode === 'static') { // Header Static
            body.removeClass('header-fixed-top').removeClass('header-fixed-bottom');
            header.removeClass('navbar-fixed-top').removeClass('navbar-fixed-bottom');
        }
    };

    /* Datatables Bootstrap integration */
    var dtIntegration = function() {

        // Set the defaults for DataTables initialization
        $.extend(true, $.fn.dataTable.defaults, {
            "sDom": "<'row'<'col-sm-6 col-xs-5'l><'col-sm-6 col-xs-7'f>r>t<'row'<'col-sm-5 hidden-xs'i><'col-sm-7 col-xs-12 clearfix'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_",
                "sSearch": "<div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-search\"></i></span>_INPUT_</div>",
                "sInfo": "<strong>_START_</strong>-<strong>_END_</strong> of <strong>_TOTAL_</strong>",
                "oPaginate": {
                    "sPrevious": "",
                    "sNext": ""
                }
            }
        });

        // Default class modification
        $.extend($.fn.dataTableExt.oStdClasses, {
            "sWrapper": "dataTables_wrapper form-inline"
        });

        // API method to get paging information
        $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
        {
            return {
                "iStart": oSettings._iDisplayStart,
                "iEnd": oSettings.fnDisplayEnd(),
                "iLength": oSettings._iDisplayLength,
                "iTotal": oSettings.fnRecordsTotal(),
                "iFilteredTotal": oSettings.fnRecordsDisplay(),
                "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
                "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
            };
        };

        // Bootstrap style pagination control
        $.extend($.fn.dataTableExt.oPagination, {
            "bootstrap": {
                "fnInit": function(oSettings, nPaging, fnDraw) {
                    var oLang = oSettings.oLanguage.oPaginate;
                    var fnClickHandler = function(e) {
                        e.preventDefault();
                        if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
                            fnDraw(oSettings);
                        }
                    };

                    $(nPaging).append(
                        '<ul class="pagination pagination-sm remove-margin">' +
                        '<li class="prev disabled"><a href="javascript:void(0)"><i class="fa fa-chevron-left"></i> ' + oLang.sPrevious + '</a></li>' +
                        '<li class="next disabled"><a href="javascript:void(0)">' + oLang.sNext + ' <i class="fa fa-chevron-right"></i></a></li>' +
                        '</ul>'
                        );
                    var els = $('a', nPaging);
                    $(els[0]).bind('click.DT', {action: "previous"}, fnClickHandler);
                    $(els[1]).bind('click.DT', {action: "next"}, fnClickHandler);
                },
                "fnUpdate": function(oSettings, fnDraw) {
                    var iListLength = 5;
                    var oPaging = oSettings.oInstance.fnPagingInfo();
                    var an = oSettings.aanFeatures.p;
                    var i, j, sClass, iStart, iEnd, iHalf = Math.floor(iListLength / 2);

                    if (oPaging.iTotalPages < iListLength) {
                        iStart = 1;
                        iEnd = oPaging.iTotalPages;
                    }
                    else if (oPaging.iPage <= iHalf) {
                        iStart = 1;
                        iEnd = iListLength;
                    } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
                        iStart = oPaging.iTotalPages - iListLength + 1;
                        iEnd = oPaging.iTotalPages;
                    } else {
                        iStart = oPaging.iPage - iHalf + 1;
                        iEnd = iStart + iListLength - 1;
                    }

                    for (i = 0, iLen = an.length; i < iLen; i++) {
                        // Remove the middle elements
                        $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                        // Add the new list items and their event handlers
                        for (j = iStart; j <= iEnd; j++) {
                            sClass = (j === oPaging.iPage + 1) ? 'class="active"' : '';
                            $('<li ' + sClass + '><a href="javascript:void(0)">' + j + '</a></li>')
                                .insertBefore($('li:last', an[i])[0])
                                .bind('click', function(e) {
                                e.preventDefault();
                                oSettings._iDisplayStart = (parseInt($('a', this).text(), 10) - 1) * oPaging.iLength;
                                fnDraw(oSettings);
                            });
                        }

                        // Add / remove disabled classes from the static elements
                        if (oPaging.iPage === 0) {
                            $('li:first', an[i]).addClass('disabled');
                        } else {
                            $('li:first', an[i]).removeClass('disabled');
                        }

                        if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
                            $('li:last', an[i]).addClass('disabled');
                        } else {
                            $('li:last', an[i]).removeClass('disabled');
                        }
                    }
                }
            }
        });
    };

    return {
        init: function () {
            uiInit(); // Initialize UI Code
            primaryNav(); // Primary Navigation functionality
            scrollToTop(); // Scroll to top functionality
            templateOptions(); // Template Options, change features and colors
            dtIntegration(); // Datatables Bootstrap integration
        }
    };
}();

var saveAccountModal = function (token, id) {
	var post_data = {
		"id": id,
		"token": token,
		"email": $('#modal-account-email').val(),
		"current-password": $('#modal-account-pass').val(),
		"new-password": $('#modal-account-newpass').val(),
		"new-password-2": $('#modal-account-newrepass').val()
	};

	$.ajax({
		method: "POST",
		url: "/api/1/account",
		data: JSON.stringify(post_data),
		dataType: "json",
		contentType: 'application/json; charset=utf-8',
		success: function (data, textStatus, xhr) {
			$('#modal-user-account').modal('toggle');
		},
		error: function (xhr, textStatus, errorThrown) {
			alert("[" + xhr.status + "] " + JSON.parse(xhr.responseText));
		}
	});

	return;
};

/* Initialize WebApp when page loads */
$(function(){ webApp.init(); });