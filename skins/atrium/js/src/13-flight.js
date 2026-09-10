/**
 * Atrium — product image flies to the basket.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * Called by ccAddToBasket (12-basket.js) at click time, NOT after the response:
 * the source image and the basket icon both have to be measured while the page
 * is still the one the customer clicked on, and the mini-basket fragment is
 * replaced wholesale a moment later.
 *
 * A clone is animated, never the image itself — the original stays in the grid,
 * and on the product page it is the gallery image the customer is still
 * looking at.
 *
 * Deliberately silent about failure. Every guard below returns rather than
 * throwing, because this runs inside the add-to-basket path and a flourish must
 * never be able to break a purchase.
 */
window.ccFlyToBasket = function (img) {
    if (!img) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var target = document.getElementById('mini-basket');
    if (!target) return;

    var from = img.getBoundingClientRect();
    var to = target.getBoundingClientRect();
    /* Zero width means the image has not laid out, or the basket is the
       below-sm variant that is off-screen. Nothing sensible to animate to. */
    if (!from.width || !to.width) return;

    var clone = document.createElement('img');
    if (!clone.animate) return;               // no Web Animations API
    clone.src = img.currentSrc || img.src;
    clone.alt = '';
    clone.setAttribute('aria-hidden', 'true');
    clone.className = 'cc-flight';
    clone.style.left = from.left + 'px';
    clone.style.top = from.top + 'px';
    clone.style.width = from.width + 'px';
    clone.style.height = from.height + 'px';
    document.body.appendChild(clone);

    var dx = (to.left + to.width / 2) - (from.left + from.width / 2);
    var dy = (to.top + to.height / 2) - (from.top + from.height / 2);

    /* The midpoint is lifted 70px above the straight line. A linear path reads
       as a file transfer; the arc reads as a throw, which is the whole point. */
    var flight = clone.animate([
        { transform: 'translate(0px, 0px) scale(1)', opacity: 0.95 },
        { transform: 'translate(' + (dx * 0.5) + 'px, ' + ((dy * 0.5) - 70) + 'px) scale(0.55)', opacity: 0.9, offset: 0.55 },
        { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(0.12)', opacity: 0.15 }
    ], { duration: 620, easing: 'cubic-bezier(0.33, 0, 0.67, 1)' });

    function cleanup() { if (clone.parentNode) clone.remove(); }
    flight.onfinish = cleanup;
    flight.oncancel = cleanup;
    /* Belt and braces: a backgrounded tab can leave an animation neither
       finished nor cancelled, and an abandoned clone would sit over the page. */
    window.setTimeout(cleanup, 1500);
};
