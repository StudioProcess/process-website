/* globals imagesLoaded */
(function( root, $, undefined ) {
	"use strict";

	// fade in images
	$(function () {
		$("img").css("visibility", "hidden");
		$("img").each(function () {
			imagesLoaded($(this), function (iL) {
				// console.log(iL.images[0]);
				$(iL.images[0].img).css("visibility", "visible").fadeOut(0).fadeIn(300);
			});
		});

	});

	// fix Works menu highlight in single works
	$(function () {
		$('body.single-works #menu-item-8').addClass('current-menu-item');
	});

} ( this, jQuery ));
