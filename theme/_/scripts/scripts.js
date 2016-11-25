/* globals imagesLoaded */
(function( root, $ ) {
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
	
	// intro text effect
	$(function () {
		var $block = $('.big-text-start');
		var str = $block.text().trim();
		var words = str.split(/\s/); // split text into words (by whitespace)
		var htm = '';
		for (var j = 0; j < words.length; j++) {
			var word = words[j];
			htm += '<div>';
			for (var i = 0; i < word.length; i++) {
				htm += '<span>' + word[i] + '</span>';
			}
			htm += '</div>&nbsp;';
		}

		$block.html(htm);
	});

} ( this, jQuery ));
