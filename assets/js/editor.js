(function($){

    'use strict';

    // Store timeout ID for cleanup
    let changeTimeout = null;
    let isReinitializing = false;

    function reinitializeWidget($scope){
        const widget = $scope[0]?.querySelector('.kpc-widget');

        if(!widget){
            return;
        }

        // Prevent multiple simultaneous reinitializations
        if (isReinitializing) {
            return;
        }

        isReinitializing = true;

        try {
            widget.removeAttribute('data-initialized');

            // Check if the init function exists
            if (typeof window.initKPCWidget === 'function') {
                window.initKPCWidget(widget);
                widget.setAttribute('data-initialized', 'true');
            } else {
                console.warn('KPC Widget: initKPCWidget function not found');
            }
        } catch (error) {
            console.warn('KPC Widget: Failed to reinitialize', error);
        } finally {
            isReinitializing = false;
        }
    }

    // Clean up function
    function cleanup() {
        if (changeTimeout) {
            clearTimeout(changeTimeout);
            changeTimeout = null;
        }
    }

    $(window).on('elementor:init', function(){

        if (typeof elementor === 'undefined' || !elementor.channels?.editor) {
            return;
        }

        // Remove any existing listener first
        elementor.channels.editor.off('change');

        // Add new listener with cleanup
        elementor.channels.editor.on('change', function(){

            // Clear existing timeout
            cleanup();

            // Set new timeout
            changeTimeout = setTimeout(function(){

                // Only reinitialize visible widgets
                $('.elementor-widget-kpc_portfolio_compare:visible').each(function(){
                    reinitializeWidget($(this));
                });

                // Clean up after execution
                cleanup();

            }, 150); // Increased timeout for reliability

        });

        // Also handle Elementor preview changes
        if (elementor.channels.preview) {
            elementor.channels.preview.off('change');
            elementor.channels.preview.on('change', function(){
                // Use the same debounced handler
                elementor.channels.editor.trigger('change');
            });
        }

        // Handle dynamic content loading via Elementor's frontend
        $(document).on('elementor/frontend/init', function(){
            if (window.elementorFrontend) {
                window.elementorFrontend.hooks.addAction(
                    'frontend/element_ready/kpc_portfolio_compare.default',
                    function($element){
                        const widget = $element.find('.kpc-widget')[0];
                        if(widget){
                            // Small delay to ensure DOM is ready
                            setTimeout(function(){
                                if (typeof window.initKPCWidget === 'function') {
                                    window.initKPCWidget(widget);
                                    widget.setAttribute('data-initialized', 'true');
                                }
                            }, 50);
                        }
                    }
                );
            }
        });

        // Initial load
        setTimeout(function(){
            $('.elementor-widget-kpc_portfolio_compare').each(function(){
                reinitializeWidget($(this));
            });
        }, 200);

    });

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        cleanup();
        if (typeof elementor !== 'undefined' && elementor.channels?.editor) {
            elementor.channels.editor.off('change');
        }
    });

})(jQuery);