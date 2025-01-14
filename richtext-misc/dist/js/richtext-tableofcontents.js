/**
 * Purpose: detect <div class="content-view-full attribute-body"> and loop over found header <>h(n)> tags to create a simple Table of contents using Javascript
 * Why JS instead of serverside? It's simple client processing, can detect changes in HTML and variations, portable, will HTML print but not PDF print using php PDF generator
 *
 * Author: David Sayre @ Allegiance / Beaconfire
 * Part 1: edit your HTML and put <div class='richtext-toc'></div> as the target to generate the TOC list
 * Part 2: load this script at the end of the HTML (footer_scripts.html.twig) after jQuery is loaded
 * Optional 'smoothscroll' detection for sticky headers
 */
jQuery(document).ready(function () {
    var headingSelectors = "h3,h4";
    // requires: https://ndabas.github.io/toc/
    var searchParentSelector = ".content-view-full .attribute-body";
    var tocTargetSelector = ".richtext-toc";
    if (jQuery(tocTargetSelector).length === 0) {
        // target not found
        return false;
    }
    if (jQuery(searchParentSelector).find(headingSelectors).length === 0) {
        // no headers to work with
        return false;
    }
    if (jQuery(searchParentSelector + " .in_toc").length > 0) {
        //if(console) { console.log("TOC detected in_toc"); }
        jQuery(searchParentSelector).before(jQuery("<div class='richtext-toc'></div>"));
        headingSelectors = "h3.in_toc, h4.in_toc";
    }
    //if(console) { console.log("Building table of contents.."); }
    var tocLabel = "";
    var tocLabelSize = "5";
    if (jQuery(tocTargetSelector).attr('data-label')) {
        tocLabel = jQuery(tocTargetSelector).attr('data-label');
    }
    if (jQuery(tocTargetSelector).attr('data-label-size')) {
        tocLabelSize = jQuery(tocTargetSelector).attr('data-label-size');
    }
    if ((tocLabel) && (tocLabelSize)) {
        // append <h[n]>label</h[n]> before <ul> items
        jQuery(tocTargetSelector).append("<h" + tocLabelSize + ">" + tocLabel + "</h" + tocLabelSize + ">");
    }
    // append ul for items to be added
    jQuery(tocTargetSelector).append("<ul></ul>");
    jQuery(tocTargetSelector + " > ul").toc({content: searchParentSelector, headings: headingSelectors});
    // Oncolink: re-run link + nav smoothscroll with toc scope
    if (typeof smoothScroll !== "undefined") {
        //if(console) { console.log("re-run smoothscroll for table of contents.."); }
        smoothScroll('.site-header', tocTargetSelector);
    }

});

/*
edit jQuery.toc.js

var generateUniqueId = function (text) {
+    text = slugify(text);

... and add just before end ...

function slugify(str) {
    str = str.replace(/^\s+|\s+$/g, ''); // trim leading/trailing white space
    str = str.toLowerCase(); // convert string to lowercase
    str = str.replace(/[^a-z0-9 -]/g, '') // remove any non-alphanumeric characters
        .replace(/\s+/g, '-') // replace spaces with hyphens
        .replace(/-+/g, '-'); // remove consecutive hyphens
    return str;
}
*/
