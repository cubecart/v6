/**
 * Atrium — product image flies to the basket. No template literals (00-boot.js).
 *
 * Called at click time, not after the response: both elements must be measured
 * before the mini-basket fragment is swapped out. Every guard returns rather
 * than throws — a flourish must not be able to break a purchase.
 */
window.ccFlyToBasket = function (img) {
    if (!img) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var target = document.getElementById('mini-basket');
    if (!target) return;

    var from = img.getBoundingClientRect();
    var to = target.getBoundingClientRect();
    // Not laid out, or the off-screen below-sm basket.
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

    // Lifted 70px: a straight line reads as a file transfer, the arc as a throw.
    var flight = clone.animate([
        { transform: 'translate(0px, 0px) scale(1)', opacity: 0.95 },
        { transform: 'translate(' + (dx * 0.5) + 'px, ' + ((dy * 0.5) - 70) + 'px) scale(0.55)', opacity: 0.9, offset: 0.55 },
        { transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(0.12)', opacity: 0.15 }
    ], { duration: 620, easing: 'cubic-bezier(0.33, 0, 0.67, 1)' });

    function cleanup() { if (clone.parentNode) clone.remove(); }
    flight.onfinish = cleanup;
    flight.oncancel = cleanup;
    // A backgrounded tab can leave it neither finished nor cancelled.
    window.setTimeout(cleanup, 1500);
};
