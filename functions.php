<?php
function saxon_category_list_shortcode($atts) {
    if (!function_exists('get_field')) {
        return '<p>ACF plugin is not active.</p>';
    }

    $atts = shortcode_atts([
        'post_type' => 'post',
        'taxonomy'  => '', // Optional override
    ], $atts, 'saxon_category_list');

    $post_type = sanitize_key($atts['post_type']);
    $output = '';

    // Parse taxonomies
    $taxonomies = [];

    if (!empty($atts['taxonomy'])) {
        $input_tax = explode(',', $atts['taxonomy']);
        foreach ($input_tax as $tax) {
            $sanitized = sanitize_key(trim($tax));
            if ($sanitized) {
                $taxonomies[] = $sanitized;
            }
        }
    } else {
        // Auto-detect taxonomies from post type
        $object_taxes = get_object_taxonomies($post_type, 'objects');
        foreach ($object_taxes as $tax_obj) {
            if ($tax_obj->public && !$tax_obj->_builtin) {
                $taxonomies[] = $tax_obj->name;
            }
        }

        // Fallback to 'category' if no custom taxonomy found
        if (empty($taxonomies) && $post_type === 'post') {
            $taxonomies[] = 'category';
        }
    }

    if (empty($taxonomies)) {
        return '<p>No valid taxonomies found for post type "' . esc_html($post_type) . '".</p>';
    }

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            $output .= '<p>No terms found in taxonomy "' . esc_html($taxonomy) . '".</p>';
            continue;
        }

        $output .= '<div class="saxon-category-list saxon-tax-' . esc_attr($taxonomy) . '">';
        foreach ($terms as $term) {
            $image_id  = get_field('category_image', $taxonomy . '_' . $term->term_id);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : '';
            $term_link = get_term_link($term);

            $output .= '<a href="' . esc_url($term_link) . '" class="saxon-category-item">';
            if ($image_url) {
                $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($term->name) . '" class="saxon-category-image" />';
            }
            $output .= '<div class="saxon-category-content">';
            $output .= '<h4 class="saxon-category-title">' . esc_html($term->name) . '</h4>';
            $output .= '<p class="saxon-category-description">' . esc_html($term->description) . '</p>';
            $output .= '</div></a>';
        }
        $output .= '</div>';
    }

    return $output;
}
add_shortcode('saxon_category_list', 'saxon_category_list_shortcode');
