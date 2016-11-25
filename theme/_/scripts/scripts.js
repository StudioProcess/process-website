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
	
	
	// Intro text effect
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
			htm += '</div> ';
		}
		$block.html(htm);
		// highlight special words
		var special = ['process', 'design', 'studio', 'vienna', 'generative', 'interactive', 'branding', 'web', 'installation', 'print', 'graphic', 'software', 'tools'];
		var $words = $block.find('div');
		$words.each(function() {
			var $word = $(this);
			word = $word.text().toLowerCase();
			if (special.indexOf(word) != -1) $word.addClass('special');
		});
		
		function runWord($el) {
			var $spans = $el.find('span');
			var delay = 100;
			$spans.each(function(i, el) {
				var $el = $(el);
				setTimeout(function() { 
					$el.addClass('hover'); 
					setTimeout(function() {$el.removeClass('hover')}, delay/3);
				}, i*delay);
			});
		}
		
		var blacklisted = '';
		function triggerRandomSpecialWord() {
			var $specialWords = $block.find('div.special');
			var $word = $specialWords.eq( Math.floor(Math.random()*$specialWords.length) );
			var word = $word.text().toLowerCase();
			if (word != blacklisted) {
				runWord($word);
				blacklisted = word;
			}
		}
		
		function trigger() {
			setTimeout(function() {
				triggerRandomSpecialWord();
				trigger();
			}, Math.random() * 5000 );
		}
		
		trigger();
	});

} ( this, jQuery ));
