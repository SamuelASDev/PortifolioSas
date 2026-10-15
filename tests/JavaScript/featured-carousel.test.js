import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { initializeFeaturedCarousel } from '../../resources/js/carousels/featured-projects.js';

let dom;
let carousel;
let tick;
let motion;

beforeEach(() => {
    dom = new JSDOM('<!doctype html><html><body></body></html>', { pretendToBeVisual: true });
    globalThis.window = dom.window;
    globalThis.document = dom.window.document;
    tick = null;
    window.setInterval = (callback) => { tick = callback; return 1; };
    window.clearInterval = () => { tick = null; };
    motion = new window.EventTarget();
    motion.matches = false;
    window.matchMedia = () => motion;
});
afterEach(() => { carousel?.destroy(); carousel = null; dom.window.close(); });

function mount(count = 3) {
    document.body.innerHTML = `<section data-featured-carousel tabindex="0"><div data-carousel-track>${Array.from({ length: count }, (_, index) => `<article data-carousel-slide aria-label="${index + 1} de ${count}"><a href="#">Projeto</a></article>`).join('')}</div>
        <div data-carousel-controls hidden><button data-carousel-prev>Anterior</button>${Array.from({ length: count }, (_, index) => `<button data-carousel-dot="${index}">${index + 1}</button>`).join('')}
        <button data-carousel-next>Próximo</button></div><p data-carousel-status></p></section>`;
    const root = document.querySelector('section');
    const track = root.querySelector('[data-carousel-track]');
    Object.defineProperty(track, 'clientWidth', { value: 800, configurable: true });
    track.scrollTo = ({ left }) => { track.scrollLeft = left; track.dispatchEvent(new window.Event('scroll')); };
    carousel = initializeFeaturedCarousel(root);
    return { root, track, slides: [...root.querySelectorAll('[data-carousel-slide]')] };
}

test('next, previous and dots navigate with wrapping and keep autoplay', () => {
    const { root, track, slides } = mount();
    assert.equal(root.querySelector('[data-carousel-controls]').hidden, false);
    assert.equal(slides[1].inert, true);
    root.querySelector('[data-carousel-next]').click();
    assert.equal(track.scrollLeft, 800);
    assert.equal(slides[1].inert, false);
    assert.equal(typeof tick, 'function');
    root.querySelector('[data-carousel-prev]').click();
    root.querySelector('[data-carousel-prev]').click();
    assert.equal(track.scrollLeft, 1600);
    root.querySelector('[data-carousel-dot="0"]').click();
    assert.equal(track.scrollLeft, 0);
    assert.equal(root.querySelector('[data-carousel-dot="0"]').getAttribute('aria-pressed'), 'true');
});

test('keyboard navigation, native scrolling and resize keep selection in sync', () => {
    const { root, track, slides } = mount();
    root.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'End', cancelable: true }));
    assert.equal(track.scrollLeft, 1600);
    root.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Home', cancelable: true }));
    assert.equal(track.scrollLeft, 0);
    track.scrollLeft = 800;
    track.dispatchEvent(new window.Event('scroll'));
    assert.equal(slides[1].inert, false);
    Object.defineProperty(track, 'clientWidth', { value: 390 });
    window.dispatchEvent(new window.Event('resize'));
    assert.equal(track.scrollLeft, 390);
});

test('autoplay advances and resumes after hover', () => {
    const { root, track } = mount();
    assert.equal(typeof tick, 'function');
    tick();
    assert.equal(track.scrollLeft, 800);
    root.dispatchEvent(new window.Event('mouseenter'));
    assert.equal(tick, null);
    root.dispatchEvent(new window.Event('mouseleave'));
    assert.equal(typeof tick, 'function');
});

test('focus and interaction stop rotation and reduced motion starts paused', () => {
    const { root, track } = mount();
    root.querySelector('a').focus();
    assert.equal(tick, null);
    root.querySelector('a').blur();
    assert.equal(typeof tick, 'function');
    track.dispatchEvent(new window.Event('pointerdown'));
    assert.equal(tick, null);
    carousel.destroy();
    motion.matches = true;
    const second = mount();
    assert.equal(tick, null);
});

test('single slide stays static and destruction removes timers and handlers', () => {
    const single = mount(1);
    assert.equal(carousel, null);
    assert.equal(single.root.querySelector('[data-carousel-controls]').hidden, true);
    const { root, track, slides } = mount();
    assert.equal(initializeFeaturedCarousel(root), null);
    carousel.destroy();
    root.querySelector('[data-carousel-next]').click();
    assert.equal(track.scrollLeft, 0);
    assert.equal(slides[1].inert, false);
    assert.equal(tick, null);
});
