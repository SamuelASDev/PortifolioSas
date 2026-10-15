export function initializeFeaturedCarousel(root, { interval = 5000 } = {}) {
    if (root.dataset.carouselReady === 'true') return null;
    const track = root.querySelector('[data-carousel-track]');
    const slides = [...root.querySelectorAll('[data-carousel-slide]')];
    const controls = root.querySelector('[data-carousel-controls]');
    if (slides.length < 2 || !controls) return null;
    const dots = [...root.querySelectorAll('[data-carousel-dot]')];
    const status = root.querySelector('[data-carousel-status]');
    const motion = window.matchMedia?.('(prefers-reduced-motion: reduce)');
    const abort = new window.AbortController();
    const options = { signal: abort.signal };
    let index = 0;
    let focused = false;
    let hovered = false;
    let timer = null;

    const stopTimer = () => { if (timer !== null) window.clearInterval(timer); timer = null; };
    const update = () => {
        slides.forEach((slide, position) => {
            slide.inert = position !== index;
            slide.setAttribute('aria-hidden', String(position !== index));
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-pressed', String(position === index)));
        status.setAttribute('aria-live', !motion?.matches && !hovered && !focused ? 'off' : 'polite');
        status.textContent = slides[index].getAttribute('aria-label');
    };
    const pause = () => { stopTimer(); update(); };
    const goTo = (position, manual = true, instant = false) => {
        if (manual) startTimer();
        index = (position + slides.length) % slides.length;
        track.scrollTo({ left: index * track.clientWidth, behavior: instant || motion?.matches ? 'auto' : 'smooth' });
        update();
    };
    const startTimer = () => {
        stopTimer();
        if (!motion?.matches && !hovered && !focused && !document.hidden) {
            timer = window.setInterval(() => goTo(index + 1, false), interval);
        }
    };
    root.querySelector('[data-carousel-prev]').addEventListener('click', () => goTo(index - 1), options);
    root.querySelector('[data-carousel-next]').addEventListener('click', () => goTo(index + 1), options);
    dots.forEach((dot, position) => dot.addEventListener('click', () => goTo(position), options));
    root.addEventListener('focusin', () => { focused = true; pause(); }, options);
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) { focused = false; update(); startTimer(); }
    }, options);
    root.addEventListener('mouseenter', () => { hovered = true; stopTimer(); }, options);
    root.addEventListener('mouseleave', () => { hovered = false; startTimer(); }, options);
    track.addEventListener('pointerdown', pause, options);
    window.addEventListener('pointerup', startTimer, options);
    window.addEventListener('pointercancel', startTimer, options);
    track.addEventListener('wheel', startTimer, { ...options, passive: true });
    track.addEventListener('scroll', () => {
        if (!track.clientWidth) return;
        const next = Math.max(0, Math.min(slides.length - 1, Math.round(track.scrollLeft / track.clientWidth)));
        if (next !== index) { index = next; update(); }
    }, { ...options, passive: true });
    root.addEventListener('keydown', (event) => {
        if (event.ctrlKey || event.metaKey || event.altKey) return;
        const positions = { ArrowLeft: index - 1, ArrowRight: index + 1, Home: 0, End: slides.length - 1 };
        if (!(event.key in positions)) return;
        event.preventDefault(); goTo(positions[event.key]);
    }, options);
    window.addEventListener('resize', () => goTo(index, false, true), options);
    document.addEventListener('visibilitychange', startTimer, options);
    const onMotionChange = () => { goTo(index, false, true); startTimer(); };
    motion?.addEventListener('change', onMotionChange);

    controls.hidden = false;
    root.dataset.carouselReady = 'true';
    update();
    startTimer();

    return {
        destroy() {
            stopTimer(); abort.abort(); motion?.removeEventListener('change', onMotionChange);
            slides.forEach((slide) => { slide.inert = false; slide.removeAttribute('aria-hidden'); });
            controls.hidden = true; delete root.dataset.carouselReady;
        },
    };
}
