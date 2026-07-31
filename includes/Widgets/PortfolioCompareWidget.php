<?php
namespace KodeemPortfolioCompare\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if (!defined('ABSPATH')) {
    exit;
}

class PortfolioCompareWidget extends Widget_Base {

    public function get_name() {
        return 'kpc_portfolio_compare';
    }

    public function get_title() {
        return __('Portfolio Compare', 'kodeem-portfolio-compare');
    }

    public function get_icon() {
        return 'eicon-image-before-after';
    }

    public function get_categories() {
        return ['kodeem-portfolio-compare'];
    }

    public function get_keywords() {
        return ['before after', 'portfolio', 'comparison', 'design showcase', 'kodeem'];
    }

    public function get_style_depends() {
        wp_enqueue_style('kpc-widget');
        return ['kpc-widget'];
    }

    public function get_script_depends() {
        wp_enqueue_script('kpc-widget');
        return ['kpc-widget'];
    }

    protected function register_controls() {
        $this->register_gallery_controls();
        $this->register_preview_controls();
        $this->register_autoplay_controls();
        $this->register_style_controls();
    }

    private function register_gallery_controls() {
        $this->start_controls_section(
            'gallery_section',
            [
                'label' => __('Gallery Items', 'kodeem-portfolio-compare'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'project_title',
            [
                'label' => __('Project Title', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Project', 'kodeem-portfolio-compare'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'thumbnail',
            [
                'label' => __('Thumbnail', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'before_image',
            [
                'label' => __('Before Image', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'after_image',
            [
                'label' => __('After Image', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'gallery_items',
            [
                'label' => __('Projects', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'project_title' => __('Project 1', 'kodeem-portfolio-compare'),
                    ],
                    [
                        'project_title' => __('Project 2', 'kodeem-portfolio-compare'),
                    ],
                    [
                        'project_title' => __('Project 3', 'kodeem-portfolio-compare'),
                    ],
                ],
                'title_field' => '{{{ project_title }}}',
            ]
        );

        $this->add_responsive_control(
            'gallery_width',
            [
                'label' => __('Gallery Width', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 300,
                    ],
                    '%' => [
                        'min' => 15,
                        'max' => 40,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 140,
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => 84,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-widget' => '--kpc-gallery-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .kpc-label',
            ]
        );

        $this->add_control(
            'label_background',
            [
                'label' => __('Label Background', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(15,23,42,.72)',
                'selectors' => [
                    '{{WRAPPER}} .kpc-label' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Label Text Color', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .kpc-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_preview_controls() {
		$this->start_controls_section(
			'preview_section',
			[
				'label' => __('Preview', 'kodeem-portfolio-compare'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		// NEW: Preview Height Control
		$this->add_responsive_control(
			'preview_height',
			[
				'label' => __('Preview Height', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
						'step' => 10,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
						'step' => 5,
					],
					'em' => [
						'min' => 5,
						'max' => 60,
						'step' => 0.5,
					],
					'rem' => [
						'min' => 5,
						'max' => 60,
						'step' => 0.5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .kpc-preview' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;',
				],
				'render_type' => 'ui',
			]
		);

		// NEW: Note about height control
		$this->add_control(
			'height_control_note',
			[
				'type' => Controls_Manager::ALERT,
				'alert_type' => 'info',
				'heading' => __('Height Control', 'kodeem-portfolio-compare'),
				'content' => __('Set a custom height for the preview. If not set, the aspect ratio will be used.', 'kodeem-portfolio-compare'),
				'condition' => [
					'preview_height[size]!' => '',
				],
			]
		);

		// NEW: Max Height Control
		$this->add_responsive_control(
			'preview_max_height',
			[
				'label' => __('Maximum Height', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1200,
						'step' => 10,
					],
					'vh' => [
						'min' => 20,
						'max' => 100,
						'step' => 5,
					],
					'em' => [
						'min' => 10,
						'max' => 70,
						'step' => 0.5,
					],
					'rem' => [
						'min' => 10,
						'max' => 70,
						'step' => 0.5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .kpc-preview' => 'max-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'preview_height[size]!' => '',
				],
			]
		);

		// NEW: Image Object Fit Control
		$this->add_control(
			'image_object_fit',
			[
				'label' => __('Image Fit', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => __('Cover', 'kodeem-portfolio-compare'),
					'contain' => __('Contain', 'kodeem-portfolio-compare'),
					'fill' => __('Fill', 'kodeem-portfolio-compare'),
					'none' => __('None', 'kodeem-portfolio-compare'),
					'scale-down' => __('Scale Down', 'kodeem-portfolio-compare'),
				],
				'selectors' => [
					'{{WRAPPER}} .kpc-before-image, {{WRAPPER}} .kpc-after-image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		// Existing: Preview Minimum Height (updated with condition)
		$this->add_responsive_control(
			'preview_min_height',
			[
				'label' => __('Minimum Height', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh'],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 900,
					],
					'vh' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 400,
				],
				'tablet_default' => [
					'unit' => 'px',
					'size' => 340,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 260,
				],
				'selectors' => [
					'{{WRAPPER}} .kpc-preview' => 'min-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'preview_height[size]' => '',
				],
			]
		);

		// Existing: Before Label
		$this->add_control(
			'before_label',
			[
				'label' => __('Before Label', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::TEXT,
				'default' => __('Before', 'kodeem-portfolio-compare'),
			]
		);

		// Existing: After Label
		$this->add_control(
			'after_label',
			[
				'label' => __('After Label', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::TEXT,
				'default' => __('After', 'kodeem-portfolio-compare'),
			]
		);

		// Existing: Show Labels
		$this->add_control(
			'show_labels',
			[
				'label' => __('Show Labels', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		// Existing: Aspect Ratio (updated with condition)
		$this->add_control(
			'aspect_ratio',
			[
				'label' => __('Aspect Ratio', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SELECT,
				'default' => '4:3',
				'options' => [
					'1:1' => '1:1',
					'4:5' => '4:5',
					'2:3' => '2:3',
					'3:4' => '3:4',
					'4:3' => '4:3',
					'16:9' => '16:9',
					'21:9' => '21:9',
					'custom' => __('Custom', 'kodeem-portfolio-compare'),
				],
				'condition' => [
					'preview_height[size]' => '',
				],
			]
		);

		// Existing: Custom Ratio Width (updated with condition)
		$this->add_control(
			'custom_ratio_width',
			[
				'label' => __('Custom Width', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'min' => 1,
				'max' => 100,
				'condition' => [
					'aspect_ratio' => 'custom',
					'preview_height[size]' => '',
				],
			]
		);

		// Existing: Custom Ratio Height (updated with condition)
		$this->add_control(
			'custom_ratio_height',
			[
				'label' => __('Custom Height', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
				'max' => 100,
				'condition' => [
					'aspect_ratio' => 'custom',
					'preview_height[size]' => '',
				],
			]
		);

		// Existing: Initial Slider Position
		$this->add_control(
			'initial_position',
			[
				'label' => __('Initial Slider Position', 'kodeem-portfolio-compare'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['%'],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
			]
		);

		$this->end_controls_section();
	}

    private function register_autoplay_controls() {
        $this->start_controls_section(
            'autoplay_section',
            [
                'label' => __('Autoplay', 'kodeem-portfolio-compare'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_autoplay',
            [
                'label' => __('Enable Autoplay', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_interval',
            [
                'label' => __('Autoplay Interval (Seconds)', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 30,
                'default' => 3,
                'condition' => [
                    'enable_autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'pause_on_hover',
            [
                'label' => __('Pause on Hover', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'enable_autoplay' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls() {
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Preview Style', 'kodeem-portfolio-compare'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'gallery_spacing',
            [
                'label' => __('Gallery Spacing', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 40,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 18,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-gallery' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'thumb_radius',
            [
                'label' => __('Thumbnail Radius', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 40,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-thumb' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'divider_thickness',
            [
                'label' => __('Divider Thickness', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-divider' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 40,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 4,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-preview' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => __('Divider Color', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .kpc-divider' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'handle_color',
            [
                'label' => __('Handle Color', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .kpc-handle' => 'color: {{VALUE}}; background-color: {{VALUE}};',
                    '{{WRAPPER}} .kpc-handle svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'handle_size',
            [
                'label' => __('Handle Size', 'kodeem-portfolio-compare'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 32,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 52,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kpc-handle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'preview_shadow',
                'selector' => '{{WRAPPER}} .kpc-preview',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'preview_border',
                'selector' => '{{WRAPPER}} .kpc-preview',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Get ratio
        $ratio = $settings['aspect_ratio'];

        if ('custom' === $ratio) {
            $width = !empty($settings['custom_ratio_width']) ? $settings['custom_ratio_width'] : 4;
            $height = !empty($settings['custom_ratio_height']) ? $settings['custom_ratio_height'] : 3;
            $ratio = $width . '/' . $height;
        } else {
            $ratio = str_replace(':', '/', $ratio);
        }

        // Sanitize data attributes
        $wrapper_attributes = [
            'class' => 'kpc-widget',
            'data-ratio' => esc_attr($ratio),
            'data-autoplay' => esc_attr($settings['enable_autoplay']),
            'data-interval' => intval($settings['autoplay_interval']) * 1000,
            'data-pause-hover' => esc_attr($settings['pause_on_hover']),
            'data-initial-position' => esc_attr($settings['initial_position']['size']),
            'data-before-label' => esc_attr($settings['before_label']),
            'data-after-label' => esc_attr($settings['after_label']),
            'data-show-labels' => esc_attr($settings['show_labels']),
        ];

        // Add responsive classes
        $device_classes = [];
        foreach (['mobile', 'tablet', 'desktop'] as $device) {
            if (!empty($settings['_responsive_' . $device . '_active'])) {
                $device_classes[] = 'kpc-' . $device;
            }
        }
        if (!empty($device_classes)) {
            $wrapper_attributes['class'] .= ' ' . implode(' ', $device_classes);
        }

        $this->add_render_attribute('wrapper', $wrapper_attributes);

        // Check if template exists
        $template_path = KPC_PATH . 'templates/widget.php';
        if (!file_exists($template_path)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="kpc-error">' . 
                    esc_html__('Template file not found: ', 'kodeem-portfolio-compare') . 
                    esc_html($template_path) . 
                    '</div>';
            }
            return;
        }

        include $template_path;
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="kpc-widget">
            <div class="kpc-editor-placeholder">
                <div class="kpc-editor-icon">
                    <i class="eicon-image-before-after"></i>
                </div>
                <h3><?php esc_html_e('Portfolio Compare', 'kodeem-portfolio-compare'); ?></h3>
                <p><?php esc_html_e('Add your before/after images in the Content tab.', 'kodeem-portfolio-compare'); ?></p>
            </div>
        </div>
        <?php
    }
}