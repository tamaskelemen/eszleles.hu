(function typeNav() {
    let nav = document.querySelector('.type-nav');
    let prev = document.querySelector('.type-nav__arrow--prev');
    let next = document.querySelector('.type-nav__arrow--next');

    if (!nav) {
        return;
    }

    // an arrow is only visible while there is something to scroll to on its side
    function update() {
        prev.hidden = nav.scrollLeft < 4;
        next.hidden = nav.scrollLeft + nav.clientWidth > nav.scrollWidth - 4;
    }

    function scroll(direction) {
        nav.scrollBy({left: direction * nav.clientWidth * 0.7, behavior: 'smooth'});
    }

    let active = nav.querySelector('.active');
    if (active) {
        nav.scrollLeft = active.offsetLeft - (nav.clientWidth - active.offsetWidth) / 2;
    }

    prev.addEventListener('click', function () { scroll(-1); });
    next.addEventListener('click', function () { scroll(1); });
    nav.addEventListener('scroll', update, {passive: true});
    window.addEventListener('resize', update);

    update();
})();
