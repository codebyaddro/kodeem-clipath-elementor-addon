class KPCWidget {

    constructor(element) {
        this.element = element;
        this.widgetId = element.id || 'kpc-' + Date.now();
        
        // DOM Elements
        this.gallery = element.querySelector('.kpc-gallery');
        this.preview = element.querySelector('.kpc-preview');
        this.beforeImage = element.querySelector('.kpc-before-image');
        this.afterImage = element.querySelector('.kpc-after-image');
        this.afterWrapper = element.querySelector('.kpc-after-wrapper');
        this.divider = element.querySelector('.kpc-divider');
        this.handle = element.querySelector('.kpc-handle');
        this.fullscreenBtn = element.querySelector('.kpc-fullscreen');
        this.pulseContainer = element.querySelector('.kpc-pulse-container');
        this.sliderLine = element.querySelector('.kpc-slider-line');
        
        // State
        this.items = [...element.querySelectorAll('.kpc-thumb')];
        this.index = 0;
        this.selected = null;
        this.timer = null;
        this.dragging = false;
        this.isFullscreen = false;
        this.isVisible = false;
        this.imagesLoaded = false;
        this.currentPercent = 50;
		this.customHeight = element.dataset.customHeight === 'yes';
        
        // Configuration
        this.interval = parseInt(element.dataset.interval || 3000, 10);
        this.autoplay = element.dataset.autoplay === 'yes';
        this.pauseHover = element.dataset.pauseHover === 'yes';
        this.initialPosition = parseFloat(element.dataset.initialPosition || 50);
        this.showLabels = element.dataset.showLabels === 'yes';
        
        // Bind methods
        this.handleMouseDown = this.handleMouseDown.bind(this);
        this.handleTouchStart = this.handleTouchStart.bind(this);
        this.handleMouseMove = this.handleMouseMove.bind(this);
        this.handleMouseUp = this.handleMouseUp.bind(this);
        this.handleTouchMove = this.handleTouchMove.bind(this);
        this.handleTouchEnd = this.handleTouchEnd.bind(this);
        this.handleResize = this.debounce(this.handleResize.bind(this), 250);
        this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
        this.initializeSlider = this.initializeSlider.bind(this);
        
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        
        if (this.isVisible) {
            this.preloadImages();
        }
        
        this.bindGallery();
        this.bindSlider();
        this.bindHover();
        this.bindKeyboard();
        this.bindFullscreen();
        this.bindResize();
        this.bindVisibilityChange();
        
        // Initialize slider at center
        this.initializeSlider();
        
        if (this.autoplay) {
            this.startAutoplay();
        }
        
        this.updateAriaAttributes();
    }

    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    this.isVisible = entry.isIntersecting;
                    if (this.isVisible && !this.imagesLoaded) {
                        this.preloadImages();
                        this.imagesLoaded = true;
                    }
                    if (!this.isVisible && this.autoplay) {
                        this.stopAutoplay();
                    } else if (this.isVisible && this.autoplay && this.selected === null) {
                        this.startAutoplay();
                    }
                });
            }, {
                rootMargin: '50px',
                threshold: 0.1
            });
            
            observer.observe(this.element);
            this.observer = observer;
        } else {
            this.isVisible = true;
            this.preloadImages();
            this.imagesLoaded = true;
        }
    }

    preloadImages() {
        let loadedCount = 0;
        const totalImages = this.items.length * 2;

        this.items.forEach((item, index) => {
            const before = item.dataset.before;
            const after = item.dataset.after;

            if (before) {
                const img = new Image();
                img.onload = () => this.checkImagesLoaded(++loadedCount, totalImages, index);
                img.onerror = () => this.checkImagesLoaded(++loadedCount, totalImages, index);
                img.src = before;
            }

            if (after) {
                const img = new Image();
                img.onload = () => this.checkImagesLoaded(++loadedCount, totalImages, index);
                img.onerror = () => this.checkImagesLoaded(++loadedCount, totalImages, index);
                img.src = after;
            }
        });
    }

    checkImagesLoaded(loaded, total, index) {
        if (loaded === total) {
            this.element.dispatchEvent(new CustomEvent('kpc:imagesLoaded', {
                detail: { index }
            }));
        }
    }

    startAutoplay() {
        this.stopAutoplay();

        if (!this.isVisible) {
            return;
        }

        this.timer = setInterval(() => {
            if (this.selected !== null || this.dragging) {
                return;
            }

            this.index = (this.index + 1) % this.items.length;
            this.activate(this.index, false);

        }, this.interval);
    }

    stopAutoplay() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    bindHover() {
        if (!this.pauseHover) {
            return;
        }

        this.element.addEventListener('mouseenter', () => {
            if (this.selected === null) {
                this.stopAutoplay();
            }
        });

        this.element.addEventListener('mouseleave', () => {
            if (this.selected === null && this.autoplay) {
                this.startAutoplay();
            }
        });
    }

    bindKeyboard() {
        this.element.setAttribute('tabindex', '0');
        this.element.setAttribute('role', 'region');
        this.element.setAttribute('aria-label', 'Portfolio comparison slider');

        this.element.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                this.stopAutoplay();
                this.next();
            }

            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                this.stopAutoplay();
                this.previous();
            }

            if (e.key === 'Home') {
                e.preventDefault();
                this.stopAutoplay();
                this.index = 0;
                this.activate(0, false);
            }

            if (e.key === 'End') {
                e.preventDefault();
                this.stopAutoplay();
                this.index = this.items.length - 1;
                this.activate(this.index, false);
            }

            if (e.key === ' ' || e.key === 'Space') {
                e.preventDefault();
                this.toggleAutoplay();
            }
        });
    }

    toggleAutoplay() {
        if (this.timer) {
            this.stopAutoplay();
            this.element.setAttribute('aria-label', 'Autoplay paused');
        } else {
            this.startAutoplay();
            this.element.setAttribute('aria-label', 'Autoplay playing');
        }
    }

    next() {
        this.index = (this.index + 1) % this.items.length;
        this.activate(this.index, false);
    }

    previous() {
        this.index--;
        if (this.index < 0) {
            this.index = this.items.length - 1;
        }
        this.activate(this.index, false);
    }

    bindGallery() {
        this.items.forEach((item, index) => {
            item.addEventListener('click', () => {
                this.handleThumbnailClick(index);
            });

            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.handleThumbnailClick(index);
                }
            });

            item.setAttribute('role', 'tab');
            item.setAttribute('aria-selected', 'false');
            item.setAttribute('aria-controls', 'kpc-preview-' + this.widgetId);
        });

        if (this.items.length > 0) {
            this.items[0].setAttribute('aria-selected', 'true');
        }
    }

    handleThumbnailClick(index) {
        if (this.selected === index) {
            this.selected = null;
            this.items[index].classList.remove('is-active');
            this.items[index].setAttribute('aria-selected', 'false');
            this.startAutoplay();
            return;
        }

        this.selected = index;
        this.items.forEach(i => {
            i.classList.remove('is-active');
            i.setAttribute('aria-selected', 'false');
        });
        this.items[index].classList.add('is-active');
        this.items[index].setAttribute('aria-selected', 'true');

        this.stopAutoplay();
        this.activate(index, true);
    }

    activate(index, manual = false) {
        const item = this.items[index];
        if (!item) return;

        const before = item.dataset.before;
        const after = item.dataset.after;

        this.preview.classList.add('is-loading');
        this.preview.classList.add('is-transitioning');

        const loadImage = (src) => {
            return new Promise((resolve) => {
                if (!src) {
                    resolve();
                    return;
                }
                const img = new Image();
                img.onload = () => resolve(src);
                img.onerror = () => {
                    console.warn('KPC: Failed to load image', src);
                    resolve(src);
                };
                img.src = src;
            });
        };

        Promise.all([
            loadImage(before),
            loadImage(after)
        ]).then(() => {
            setTimeout(() => {
                if (before) {
                    this.beforeImage.src = before;
                    this.beforeImage.alt = item.dataset.title || 'Before image';
                }

                if (after) {
                    this.afterImage.src = after;
                    this.afterImage.alt = item.dataset.title || 'After image';
                }

                if (!manual) {
                    this.items.forEach(i => {
                        i.classList.remove('is-active');
                        i.setAttribute('aria-selected', 'false');
                    });
                    item.classList.add('is-active');
                    item.setAttribute('aria-selected', 'true');
                }

                // Re-initialize slider position
                this.initializeSlider();
                this.updateAriaAttributes();

                requestAnimationFrame(() => {
                    this.preview.classList.remove('is-transitioning');
                    this.preview.classList.remove('is-loading');
                });

            }, 150);
        }).catch(() => {
            this.preview.classList.remove('is-loading');
            this.preview.classList.remove('is-transitioning');
        });
    }

    /**
     * Initialize slider at center position
     */
    initializeSlider() {
		const containerHeight = this.customHeight ? 
        this.preview.offsetHeight : 
        this.preview.offsetWidth;
		
		const containerWidth = this.preview.offsetWidth || this.preview.clientWidth;

		if (containerWidth === 0) {
			// If container not ready, try again
			requestAnimationFrame(() => this.initializeSlider());
			return;
		}

		const percent = this.initialPosition || 50;
		const dividerContainer = this.element.querySelector('.kpc-divider-container');

		// Apply smooth transition for initialization
		if (this.beforeImage) {
			this.beforeImage.style.transition = 'clip-path 0.3s ease';
		}
		if (this.afterImage) {
			this.afterImage.style.transition = 'clip-path 0.3s ease';
		}
		if (dividerContainer) {
			dividerContainer.style.transition = 'left 0.3s ease';
		}

		this.updateSlider(percent);

		// Remove transitions after initialization
		setTimeout(() => {
			if (this.beforeImage) {
				this.beforeImage.style.transition = 'none';
			}
			if (this.afterImage) {
				this.afterImage.style.transition = 'none';
			}
			if (dividerContainer) {
				dividerContainer.style.transition = 'none';
			}
		}, 350);
	}

    /**
     * Update slider position using clip-path (like the example)
     */
    updateSlider(percent) {
		percent = Math.max(0, Math.min(100, percent));
		this.currentPercent = percent;

		// Using clip-path method (like the example)
		if (this.beforeImage) {
			this.beforeImage.style.clipPath = `inset(0 ${100 - percent}% 0 0)`;
		}
		if (this.afterImage) {
			this.afterImage.style.clipPath = `inset(0 0 0 ${percent}%)`;
		}

		// UPDATE: Move the divider container instead of individual elements
		const dividerContainer = this.element.querySelector('.kpc-divider-container');
		if (dividerContainer) {
			dividerContainer.style.left = percent + '%';
			dividerContainer.style.setProperty('--kpc-divider-position', percent + '%');
		}

		// For backward compatibility, also update individual elements
		if (this.divider) {
			this.divider.style.left = percent + '%';
		}

		// Update slider line if it exists
		if (this.sliderLine) {
			this.sliderLine.style.left = percent + '%';
		}

		// Update handle position (if it's not inside the container)
		if (this.handle) {
			// Check if handle is inside divider container
			const isInsideContainer = this.handle.closest('.kpc-divider-container');
			if (!isInsideContainer) {
				this.handle.style.left = percent + '%';
			}
			this.handle.setAttribute('aria-valuenow', Math.round(percent));
		}

		// Update after wrapper if using the old method
		if (this.afterWrapper) {
			this.afterWrapper.style.width = percent + '%';
		}
	}

    /**
     * Bind slider events - Updated for the new style
     */
    bindSlider() {
		// Use the divider container for dragging
		const dividerContainer = this.element.querySelector('.kpc-divider-container');

		if (dividerContainer) {
			dividerContainer.addEventListener('mousedown', this.handleMouseDown);
			dividerContainer.addEventListener('touchstart', this.handleTouchStart, { passive: true });
			dividerContainer.style.cursor = 'ew-resize';
		}

		// Also bind to the handle specifically
		if (this.handle) {
			this.handle.addEventListener('mousedown', this.handleMouseDown);
			this.handle.addEventListener('touchstart', this.handleTouchStart, { passive: true });
			this.handle.addEventListener('dragstart', (e) => e.preventDefault());
		}

		// Also bind to the slider line if it exists
		if (this.sliderLine) {
			this.sliderLine.addEventListener('mousedown', this.handleMouseDown);
			this.sliderLine.addEventListener('touchstart', this.handleTouchStart, { passive: true });
			this.sliderLine.addEventListener('dragstart', (e) => e.preventDefault());
		}

		// Also make the whole preview draggable on the divider line
		if (this.divider) {
			this.divider.addEventListener('mousedown', this.handleMouseDown);
			this.divider.addEventListener('touchstart', this.handleTouchStart, { passive: true });
		}
	}

    /**
     * Handle mouse down on handle
     */
    handleMouseDown(e) {
        e.preventDefault();
        e.stopPropagation();
        
        this.dragging = true;
        this.preview.classList.add('is-dragging');
        this.stopAutoplay();
        
        // Store the current position for better tracking
        this.startX = e.clientX;
        this.startPercent = this.currentPercent;
        
        // Add global listeners
        document.addEventListener('mousemove', this.handleMouseMove);
        document.addEventListener('mouseup', this.handleMouseUp);
    }

    /**
     * Handle mouse move
     */
    handleMouseMove(e) {
        if (!this.dragging) return;
        
        e.preventDefault();
        
        const rect = this.preview.getBoundingClientRect();
        let x = e.clientX - rect.left;
        x = Math.max(0, Math.min(rect.width, x));
        
        const percent = (x / rect.width) * 100;
        this.updateSlider(percent);
    }

    /**
     * Handle mouse up
     */
    handleMouseUp(e) {
        if (!this.dragging) return;
        
        this.dragging = false;
        this.preview.classList.remove('is-dragging');
        
        // Remove global listeners
        document.removeEventListener('mousemove', this.handleMouseMove);
        document.removeEventListener('mouseup', this.handleMouseUp);
        
        // Resume autoplay
        if (this.selected === null && this.autoplay) {
            this.startAutoplay();
        }
    }

    /**
     * Handle touch start
     */
    handleTouchStart(e) {
        const touch = e.touches[0];
        if (!touch) return;
        
        this.dragging = true;
        this.preview.classList.add('is-dragging');
        this.stopAutoplay();
        
        this.startX = touch.clientX;
        this.startPercent = this.currentPercent;
        
        // Add global touch listeners
        document.addEventListener('touchmove', this.handleTouchMove, { passive: false });
        document.addEventListener('touchend', this.handleTouchEnd, { passive: true });
    }

    /**
     * Handle touch move
     */
    handleTouchMove(e) {
        if (!this.dragging) return;
        
        e.preventDefault();
        
        const touch = e.touches[0];
        if (!touch) return;
        
        const rect = this.preview.getBoundingClientRect();
        let x = touch.clientX - rect.left;
        x = Math.max(0, Math.min(rect.width, x));
        
        const percent = (x / rect.width) * 100;
        this.updateSlider(percent);
    }

    /**
     * Handle touch end
     */
    handleTouchEnd(e) {
        if (!this.dragging) return;
        
        this.dragging = false;
        this.preview.classList.remove('is-dragging');
        
        // Remove global touch listeners
        document.removeEventListener('touchmove', this.handleTouchMove);
        document.removeEventListener('touchend', this.handleTouchEnd);
        
        // Resume autoplay
        if (this.selected === null && this.autoplay) {
            this.startAutoplay();
        }
    }

    bindFullscreen() {
        if (!this.fullscreenBtn) return;

        this.fullscreenBtn.addEventListener('click', () => {
            this.toggleFullscreen();
        });
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.element.requestFullscreen?.() || 
            this.element.webkitRequestFullscreen?.() ||
            this.element.msRequestFullscreen?.();
            this.isFullscreen = true;
            this.fullscreenBtn?.classList.add('is-fullscreen');
            this.fullscreenBtn?.setAttribute('aria-label', 'Exit fullscreen');
        } else {
            document.exitFullscreen?.() ||
            document.webkitExitFullscreen?.() ||
            document.msExitFullscreen?.();
            this.isFullscreen = false;
            this.fullscreenBtn?.classList.remove('is-fullscreen');
            this.fullscreenBtn?.setAttribute('aria-label', 'Enter fullscreen');
        }
    }

    bindResize() {
        window.addEventListener('resize', this.handleResize);
    }

    handleResize() {
        this.initializeSlider();
    }

    bindVisibilityChange() {
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.stopAutoplay();
        } else if (this.autoplay && this.selected === null) {
            this.startAutoplay();
        }
    }

    updateAriaAttributes() {
        const currentItem = this.items[this.index];
        if (currentItem) {
            const title = currentItem.dataset.title || 'Project ' + (this.index + 1);
            this.preview.setAttribute('aria-label', 'Showing: ' + title);
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    destroy() {
        this.stopAutoplay();

        document.removeEventListener('mousemove', this.handleMouseMove);
        document.removeEventListener('mouseup', this.handleMouseUp);
        document.removeEventListener('touchmove', this.handleTouchMove);
        document.removeEventListener('touchend', this.handleTouchEnd);
        window.removeEventListener('resize', this.handleResize);
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);

        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }

        this.element = null;
        this.gallery = null;
        this.preview = null;
        this.beforeImage = null;
        this.afterImage = null;
        this.afterWrapper = null;
        this.divider = null;
        this.handle = null;
        this.fullscreenBtn = null;
        this.pulseContainer = null;
        this.sliderLine = null;
        this.items = [];
    }
}

// Initialize function
function initKPC(scope) {
    const widget = scope.querySelector('.kpc-widget');
    if (widget && !widget.dataset.initialized) {
        widget.dataset.initialized = 'true';
        const instance = new KPCWidget(widget);
        
        if (!window._kpcInstances) {
            window._kpcInstances = new WeakMap();
        }
        window._kpcInstances.set(widget, instance);
        
        return instance;
    }
    return null;
}

window.initKPCWidget = function(widget) {
    if (!widget) return;
    return initKPC(widget);
};

window.destroyKPCWidget = function(widget) {
    if (window._kpcInstances && window._kpcInstances.has(widget)) {
        const instance = window._kpcInstances.get(widget);
        instance.destroy();
        window._kpcInstances.delete(widget);
    }
};

// DOM Ready
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.elementor-widget-kpc_portfolio_compare')
        .forEach(initKPC);
});

// Elementor Frontend
if (window.elementorFrontend) {
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/kpc_portfolio_compare.default',
        function($scope) {
            const widget = initKPC($scope[0]);
            $scope.data('kpcInstance', widget);
        }
    );
}

if (window.elementor) {
    elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
        if (view && view.el) {
            const widget = view.el.querySelector('.kpc-widget');
            if (widget) {
                window.destroyKPCWidget(widget);
            }
        }
    });
}