/* globals imagesLoaded */
(function( root, $, undefined ) {
	"use strict";

	$(function () {
		// DOM ready, take it away

		$("img").css("visibility", "hidden");
		$("img").each(function () {
			imagesLoaded($(this), function (iL) {
				// console.log(iL.images[0]);
				$(iL.images[0].img).css("visibility", "visible").fadeOut(0).fadeIn(300);
			});
		});

	});

} ( this, jQuery ));
