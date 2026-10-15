import { initializeFeaturedCarousel } from './carousels/featured-projects.js';

document.querySelectorAll('[data-featured-carousel]').forEach((root) => initializeFeaturedCarousel(root));
