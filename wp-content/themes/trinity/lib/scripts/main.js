/************************************

ChurchThemes for WordPress
Author: Frankie Jarrett
URI: http://churchthemes.net
Version: 1.3.1

************************************/

var ChurchThemes = {

    /**
     * Init
     */
    init: function() {
        jQuery("body").removeClass("no-js");
        ChurchThemes.externalLinks();
        ChurchThemes.dropdownMenu();
        ChurchThemes.firstLast();
        ChurchThemes.sermonFilters();
        ChurchThemes.flexSlider();
        ChurchThemes.placeholderSupport();
    },

    /**
     * External links target
     */
    externalLinks: function() {
        jQuery("a").filter(function() {
            return this.hostname && this.hostname !== location.hostname;
        }).attr("target", churchthemes_global_vars.external_target);
    },

    /**
     * Dropdown menu
     */
    dropdownMenu: function() {
        jQuery(".navbar ul li:first-child").addClass("first");
        jQuery(".navbar ul li:last-child").addClass("last");
        jQuery(".navbar ul li ul li:has(ul)").find("a:first").append(" &raquo;");
        jQuery("ul.navbar li").hover(function(){
            jQuery(this).addClass("hover");
            jQuery("ul:first",this).css("visibility", "visible");
        }, function(){
            jQuery(this).removeClass("hover");
            jQuery("ul:first",this).css("visibility", "hidden");
        });
        jQuery("ul.navbar li ul li:has(ul)").find("a:first").append(" &raquo;");
    },

    /*
     * First and last classes
     */
    firstLast: function() {
        jQuery("div.widget:first").addClass("first");
        jQuery("div.widget:last").addClass("last");
        jQuery("div.widget ul li:first").addClass("first");
        jQuery("div.widget ul li:last").addClass("last");
    },

    /**
     * Sermon search filters
     */
    sermonFilters: function() {
        if(jQuery("#sermon-filter").length > 0) {
            jQuery("#sermon_speaker").selectbox();
            jQuery("#sermon_service").selectbox();
            jQuery("#sermon_series").selectbox();
            jQuery("#sermon_topic").selectbox();
        }
    },

    /**
     * The home page slider
     */
    flexSlider: function() {
        if(jQuery("#slider .mask").length > 0) {
            jQuery("#slider .mask").flexslider({
                selector: "ul > li",
                controlsContainer: ".pag_frame",
                directionNav: false,
                animation: churchthemes_slide_vars.animation,
                direction: churchthemes_slide_vars.direction,
                slideshowSpeed: churchthemes_slide_vars.speed,
            });
        }
    },

    /**
     * Enables HTML5 placeholder support for legacy browsers
     */
    placeholderSupport: function() {
        if(churchthemes_global_vars.is_IE == "true") {
            if(jQuery("input[placeholder]").length > 0) {
                jQuery("input[placeholder]").placeholder();
            }
            if(jQuery("textarea[placeholder]").length > 0) {
                jQuery("textarea[placeholder]").placeholder();
            }
        }
    }

}

jQuery(document).ready(function() {
    ChurchThemes.init();
});
