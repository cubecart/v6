/**
 * Homepage hero controls.
 *
 * The .cc-hero block is written into the homepage document once at install
 * time (language definitions: default_doc_content_welcome) and carries its own
 * flex/scroll-snap layout in a style attribute, so it renders as a carousel in
 * any skin with no CSS support at all. Touch swipe and trackpad scrolling
 * therefore work everywhere -- but a desktop mouse has nothing to grab, so
 * this adds prev/next buttons and dots.
 *
 * Deliberately vanilla and self-contained rather than a jQuery plugin, so it
 * can be lifted into any skin. No template literals: these files are served
 * through {combine}, whose JSMin pass fatals on them.
 */
(function () {
	'use strict';

	function init(hero) {
		if (hero.getAttribute('data-cc-hero') === 'on') {
			return;
		}
		/* Every element child is a slide. Do NOT filter on IMG: each slide is a
		   positioned wrapper holding the image plus its HTML caption, and older
		   documents (pre-6.8.0) hold bare <img> children instead. Counting
		   children covers both. */
		var imgs = [];
		for (var i = 0; i < hero.children.length; i++) {
			if (hero.children[i].nodeType === 1) {
				imgs.push(hero.children[i]);
			}
		}
		/* A single banner is not a carousel; controls would be a lie. */
		if (imgs.length < 2) {
			return;
		}
		hero.setAttribute('data-cc-hero', 'on');

		var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var wrap = document.createElement('div');
		wrap.className = 'cc-hero-wrap';
		hero.parentNode.insertBefore(wrap, hero);
		wrap.appendChild(hero);

		/* offsetLeft is measured against a shared offsetParent, so the
		   difference from the first image is the scroll offset regardless of
		   what that parent turns out to be. */
		function offsetOf(i) {
			return imgs[i].offsetLeft - imgs[0].offsetLeft;
		}

		function current() {
			var best = 0, min = Infinity;
			for (var i = 0; i < imgs.length; i++) {
				var d = Math.abs(offsetOf(i) - hero.scrollLeft);
				if (d < min) {
					min = d;
					best = i;
				}
			}
			return best;
		}

		function go(i) {
			if (i < 0) {
				i = imgs.length - 1;
			}
			if (i > imgs.length - 1) {
				i = 0;
			}
			hero.scrollTo({left: offsetOf(i), behavior: still ? 'auto' : 'smooth'});
		}

		function arrow(dir, label, glyph) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'cc-hero-arrow cc-hero-' + dir;
			b.setAttribute('aria-label', label);
			b.innerHTML = glyph;
			b.addEventListener('click', function () {
				go(current() + (dir === 'next' ? 1 : -1));
			});
			wrap.appendChild(b);
			return b;
		}

		arrow('prev', 'Previous banner', '&#10094;');
		arrow('next', 'Next banner', '&#10095;');

		var nav = document.createElement('div');
		nav.className = 'cc-hero-dots';
		var dots = [];
		imgs.forEach(function (img, i) {
			var d = document.createElement('button');
			d.type = 'button';
			d.className = 'cc-hero-dot';
			d.setAttribute('aria-label', 'Banner ' + (i + 1));
			d.addEventListener('click', function () {
				go(i);
			});
			nav.appendChild(d);
			dots.push(d);
		});
		wrap.appendChild(nav);

		function sync() {
			var now = current();
			for (var i = 0; i < dots.length; i++) {
				dots[i].className = 'cc-hero-dot' + (i === now ? ' cc-hero-dot-on' : '');
				/* aria-current, not aria-selected: these are links to slides,
				   not tabs, and there is no tablist role here. */
				if (i === now) {
					dots[i].setAttribute('aria-current', 'true');
				} else {
					dots[i].removeAttribute('aria-current');
				}
			}
		}

		var queued = false;
		hero.addEventListener('scroll', function () {
			if (queued) {
				return;
			}
			queued = true;
			window.requestAnimationFrame(function () {
				queued = false;
				sync();
			});
		});
		window.addEventListener('resize', sync);
		sync();
	}

	function boot() {
		var heroes = document.querySelectorAll('.cc-hero');
		for (var i = 0; i < heroes.length; i++) {
			init(heroes[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
