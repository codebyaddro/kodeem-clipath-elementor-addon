<?php
$settings = $this->get_settings_for_display();
$items = $settings['gallery_items'];

if (empty($items)) {
    return;
}

$first = $items[0];
$before = !empty($first['before_image']['url']) ? $first['before_image']['url'] : '';
$after = !empty($first['after_image']['url']) ? $first['after_image']['url'] : '';
$show_labels = ('yes' === $settings['show_labels']);
$widget_id = 'kpc-' . $this->get_id();

$ratio = str_replace(':', '/', $settings['aspect_ratio'] ?? '4/3');
$initial_position = $settings['initial_position']['size'] ?? 50;
?>

<div <?php echo $this->get_render_attribute_string('wrapper'); ?>
    id="<?php echo esc_attr($widget_id); ?>">
    
    <!-- GALLERY -->
    <aside class="kpc-gallery">
        <?php foreach ($items as $index => $item): ?>
            <?php 
            $thumb = !empty($item['thumbnail']['url']) 
                ? $item['thumbnail']['url'] 
                : (!empty($item['before_image']['url']) ? $item['before_image']['url'] : ''); 
            ?>
            
            <button class="kpc-thumb <?php echo $index === 0 ? 'is-active' : ''; ?>"
                    data-index="<?php echo esc_attr($index); ?>"
                    data-before="<?php echo esc_url($item['before_image']['url']); ?>"
                    data-after="<?php echo esc_url($item['after_image']['url']); ?>"
                    data-title="<?php echo esc_attr($item['project_title']); ?>"
                    aria-label="<?php echo esc_attr($item['project_title']); ?>">
                
                <div class="kpc-thumb-image">
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>" 
                            loading="lazy" 
                            alt="<?php echo esc_attr($item['project_title']); ?>">
                    <?php endif; ?>
                </div>
                
                <div class="kpc-thumb-progress"></div>
            </button>
        <?php endforeach; ?>
    </aside>
    
    <!-- PREVIEW -->
    <section class="kpc-preview"
            style="aspect-ratio: <?php echo esc_attr($ratio); ?>; --kpc-ratio: <?php echo esc_attr($ratio); ?>;">
        
        <!-- FULLSCREEN BUTTON -->
        <button class="kpc-fullscreen" 
                aria-label="Fullscreen preview"
                aria-controls="<?php echo esc_attr($widget_id); ?>">
            <i class="eicon-frame-expand"></i>
        </button>
        
        <!-- LABELS - REVERSED: After on LEFT, Before on RIGHT -->
        <?php if ($show_labels): ?>
            <div class="kpc-label kpc-label-after" style="left: 20px; right: auto;">
                <?php echo esc_html($settings['after_label']); ?>
            </div>
            <div class="kpc-label kpc-label-before" style="right: 20px; left: auto;">
                <?php echo esc_html($settings['before_label']); ?>
            </div>
        <?php endif; ?>
        
        <!-- AFTER IMAGE - LEFT SIDE (z-index: 1) -->
        <img class="kpc-after-image"
            src="<?php echo esc_url($after); ?>"
            loading="eager"
            alt="After image"
            style="clip-path: inset(0 <?php echo 100 - $initial_position; ?>% 0 0); z-index: 1;">
        
        <!-- BEFORE IMAGE - RIGHT SIDE (z-index: 2) -->
        <img class="kpc-before-image"
            src="<?php echo esc_url($before); ?>"
            loading="eager"
            alt="Before image"
            style="clip-path: inset(0 0 0 <?php echo $initial_position; ?>%); z-index: 2;">
        
        <!-- DIVIDER CONTAINER -->
        <div class="kpc-divider-container"
            style="left: <?php echo esc_attr($initial_position); ?>%;">
            
            <!-- DIVIDER LINE -->
            <div class="kpc-divider"></div>
            
            <!-- PULSE ANIMATION RING (if you want it separate from the handle) -->
			<div class="kpc-pulse-container">
				<!-- Optional: You can keep this or move the pulse to the handle itself -->
				<!-- HANDLE with PULSE CONTAINER -->
				<button class="kpc-handle"
						aria-label="Drag to compare before and after images"
						aria-valuemin="0"
						aria-valuemax="100"
						aria-valuenow="<?php echo esc_attr($initial_position); ?>">
					Slide
				</button>
			</div>
            
        </div>
        
    </section>
    
</div>