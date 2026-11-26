<?php

/**
 * Custom Nav Walkers for the theme - VERSIÓN MEJORADA
 * Considerando imagen de cabecera y modo oscuro
 *
 * @package edusiteco
 */

/**
 * Custom Nav Walker for the desktop menu.
 * Renders a semantic <ul><li> structure with Tailwind CSS classes
 * for dropdown functionality. Mejora: detección automática de contraste
 */
class Custom_Nav_Walker extends Walker_Nav_Menu
{
    /**
     * Starts the list before the elements are added.
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"submenu\">\n";
    }

    /**
     * Ends the list of after the elements are added.
     */
    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    /**
     * Starts the element output.
     * MEJORA: Detecta automáticamente si hay imagen y modo oscuro para colores óptimos
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        // Preserve WordPress native classes and add custom ones
        $li_classes = array('header-group');
        $li_classes = array_merge($li_classes, empty($item->classes) ? array() : (array) $item->classes);

        $has_children = in_array('menu-item-has-children', $item->classes);

        if ($has_children) {
            $li_classes[] = 'relative';
            $li_classes[] = 'group';
        }

        // Add current item classes
        if (in_array('current-menu-item', $item->classes)) {
            $li_classes[] = 'current';
        }

        // Filter and sanitize classes
        $li_classes = apply_filters('nav_menu_css_class', array_filter($li_classes), $item, $args, $depth);
        $class_names = join(' ', $li_classes);
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        // Add ID if exists
        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $has_header_image = get_header_image() ? true : false;

        // ========== LÓGICA MEJORADA DE COLORES ==========
        // Determina colores basados en: imagen de cabecera + modo oscuro

        if ($has_header_image) {
            // CON IMAGEN - siempre usar blanco/claro para mejor contraste
            $text_color_class = 'text-white drop-shadow-md';
            $hover_color_class = 'hover:text-brand-primary-200';
            $icon_color_class = 'text-white drop-shadow-sm';
            $focus_ring_class = 'focus:ring-white/50 focus:ring-offset-blue-900';
        } else {
            // SIN IMAGEN - usa colores según modo claro/oscuro
            $text_color_class = 'text-text-light dark:text-text-dark';
            $hover_color_class = 'hover:text-brand-primary dark:hover:text-brand-primary-300';
            $icon_color_class = 'text-text-light dark:text-text-dark';
            $focus_ring_class = 'focus:ring-brand-primary/50 focus:ring-offset-2 focus:ring-offset-background-light dark:focus:ring-offset-background-dark';
        }

        $a_classes = array(
            $text_color_class,
            $hover_color_class,
            'text-lg',
            'font-bold',
            'transition-all',
            'duration-200',
            'rounded-sm',
            'focus:outline-none',
            'focus:ring-2',
            $focus_ring_class,
            'no-underline',
        );

        $atts = array();
        $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';
        $atts['href'] = !empty($item->url) ? $item->url : '';
        $atts['class'] = implode(' ', $a_classes);

        if ($has_children) {
            $atts['aria-haspopup'] = 'true';
            $atts['aria-expanded'] = 'false';
        }

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('nav_menu_item_title', $item->title, $item, $args, $depth);

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . '<span class="underline-link">' . esc_html($title) . '</span>' . $args->link_after;
        if ($has_children) {
            $item_output .= '<svg class="w-5 h-5 ml-1 ' . esc_attr($icon_color_class) . ' group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        }
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * Ends the element output, if needed.
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }
}


/**
 * Custom Nav Walker for the mobile menu.
 * Renders an accessible toggle button for submenus and uses
 * an external JS file for interactions.
 */
class Mobile_Nav_Walker extends Walker_Nav_Menu
{
    /**
     * Starts the element output.
     * MEJORA: Mejor detección de contraste en modo móvil
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $has_children = in_array('menu-item-has-children', $item->classes);
        $submenu_id = 'submenu-' . $item->ID;
        $title = apply_filters('nav_menu_item_title', $item->title, $item, $args, $depth);

        $has_header_image = get_header_image() ? true : false;

        // ========== ESTILOS MEJORADOS PARA MÓVIL ==========
        if ($has_header_image) {
            // CON IMAGEN - fondos semi-transparentes sobre la imagen
            $text_color_class = 'text-white';
            $bg_class = 'bg-white/15 hover:bg-white/25 active:bg-white/35';
            $link_bg_class = 'bg-white/15 hover:bg-white/25';
        } else {
            // SIN IMAGEN - colores normales según tema
            $text_color_class = 'text-text-light dark:text-text-dark';
            $bg_class = 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700';
            $link_bg_class = 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700';
        }

        if ($has_children) {
            $output .= '<li class="border-b border-border-light dark:border-border-dark last:border-b-0">';
            $output .= '<div class="flex items-center">';

            // Link
            $output .= '<a href="' . esc_url($item->url) . '" class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium ' . $text_color_class . ' ' . $link_bg_class . ' transition-all duration-200">';
            $output .= esc_html($title);
            $output .= '</a>';

            // Toggle Button
            $output .= '<button type="button" class="mobile-toggle flex-shrink-0 px-4 py-[8px] transition-all duration-200 ' . $bg_class . ' ' . $text_color_class . '" aria-controls="' . esc_attr($submenu_id) . '" aria-expanded="false" aria-label="' . esc_attr(sprintf(__('Toggle %s submenu', 'edusiteco'), $title)) . '">';
            $output .= '<svg class="w-5 h-5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
            $output .= '</button>';

            $output .= '</div>';
            $output .= '<div id="' . esc_attr($submenu_id) . '" class="submenu hidden">';
        } else {
            $output .= '<li class="border-b border-border-light dark:border-border-dark last:border-b-0">';
            $output .= '<a class="block pl-4 pr-4 py-3.5 text-base font-medium ' . $text_color_class . ' ' . $link_bg_class . ' transition-all duration-200" href="' . esc_url($item->url) . '">';
            $output .= esc_html($title);
            $output .= '</a>';
        }
    }

    /**
     * Ends the element output.
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if (in_array('menu-item-has-children', $item->classes)) {
            $output .= '</div>';
        }
        $output .= '</li>';
    }

    /**
     * Starts the list before the elements are added.
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"pl-6 space-y-0\">\n";
    }

    /**
     * Ends the list of after the elements are added.
     */
    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
}

function edusiteco_get_primary_menu_titles()
{
    $defaults = array(
        'acerca_de' => 'Acerca de',
        'documental' => 'G. Documental',
        'academico' => 'G. Académico',
        'contacto' => 'Contáctanos',
        'administrativa_financiera' => 'G. Ad. y Financiera',
        'convocatorias' => 'Convocatorias',
    );

    $settings = get_option('info_colegio_settings', array());
    $saved_titles = is_array($settings) && isset($settings['menu_headers']) && is_array($settings['menu_headers'])
        ? $settings['menu_headers']
        : array();

    foreach ($defaults as $key => $default) {
        if (isset($saved_titles[$key]) && is_scalar($saved_titles[$key])) {
            $saved_title = trim(sanitize_text_field((string) $saved_titles[$key]));
            if ('' !== $saved_title) {
                $defaults[$key] = $saved_title;
            }
        }
    }

    return $defaults;
}

/**
 * Organize the V2 primary menu into its institutional groups without changing
 * the saved WordPress menu or the V1 theme.
 *
 * @param array    $items Menu items.
 * @param stdClass $args  Menu arguments.
 * @return array
 */
function edusiteco_organize_primary_menu($items, $args)
{
    if (!isset($args->theme_location) || 'menu-1' !== $args->theme_location || !is_array($items)) {
        return $items;
    }

    $titles = edusiteco_get_primary_menu_titles();
    $about_alias = remove_accents(strtolower($titles['acerca_de']));
    $documental_alias = remove_accents(strtolower($titles['documental']));
    $academic_alias = remove_accents(strtolower($titles['academico']));
    $contact_alias = remove_accents(strtolower($titles['contacto']));
    $administrative_alias = remove_accents(strtolower($titles['administrativa_financiera']));
    $convocatorias_alias = remove_accents(strtolower($titles['convocatorias']));

    $academic_item = null;
    $psychology_item = null;
    $docente_item = null;
    $administrative_item = null;
    $convocatorias_item = null;
    $directivo_item = null;
    $directory_item = null;
    $manual_item = null;
    $administrative_child_items = array();
    $administrative_child_titles = array(
        'asignacion academica' => 'Asignación Académica',
        'jornada escolar' => 'Jornada Escolar',
        'foses' => 'FOSES',
        'pagos' => 'Pagos',
    );

    foreach ($items as $item) {
        $title = remove_accents(strtolower(trim(wp_strip_all_tags($item->title))));

        if (in_array($title, array('la institucion', 'acerca de', $about_alias), true)) {
            $item->title = $titles['acerca_de'];
        } elseif (in_array($title, array('noticias', 'g. documental', $documental_alias), true)) {
            $item->title = $titles['documental'];
        } elseif (in_array($title, array('profesores', 'g. academico', $academic_alias), true)) {
            $academic_item = $item;
            $item->title = $titles['academico'];
        } elseif ('psicoorientacion' === $title) {
            $psychology_item = $item;
            $item->title = 'Psicoorientación';
        } elseif ('docente' === $title) {
            $docente_item = $item;
        } elseif (in_array($title, array('g. ad. y financiera', $administrative_alias), true)) {
            $administrative_item = $item;
        } elseif (in_array($title, array('convocatorias', $convocatorias_alias), true)) {
            $convocatorias_item = $item;
        } elseif (in_array($title, array('grupo directivo', 'g. directivo'), true)) {
            $directivo_item = $item;
        } elseif ('directorio' === $title) {
            $directory_item = $item;
        } elseif ('manual de convivencia' === $title) {
            $manual_item = $item;
        } elseif (isset($administrative_child_titles[$title])) {
            $administrative_child_items[$title] = $item;
        } elseif (in_array($title, array('contáctanos', 'contactanos', $contact_alias), true)) {
            $item->title = $titles['contacto'];
        }
    }

    if (!$academic_item) {
        return $items;
    }

    $profesores_page = get_page_by_path('profesores');
    $profesores_url = $profesores_page ? get_permalink($profesores_page->ID) : home_url('/profesores/');
    $academic_item->url = '#';
    $academic_item->menu_item_parent = '0';
    $academic_item->classes = array_values(array_unique(array_merge(
        (array) $academic_item->classes,
        array('menu-item-has-children')
    )));

    $next_id = 0;
    foreach ($items as $item) {
        $next_id = max($next_id, absint($item->ID));
    }

    if (!$directivo_item) {
        $next_id++;
        $directivo_item = edusiteco_create_virtual_menu_item($next_id, 'Grupo Directivo', '#', 0);
        $items[] = $directivo_item;
    }

    $directivo_item->menu_item_parent = '0';
    $directivo_item->classes = array_values(array_unique(array_merge(
        (array) $directivo_item->classes,
        array('menu-item-has-children')
    )));

    $manual_page = get_page_by_path('manual-de-convivencia');
    $manual_url = $manual_page ? get_permalink($manual_page->ID) : home_url('/manual-de-convivencia/');
    if (!$manual_item) {
        $next_id++;
        $manual_item = edusiteco_create_virtual_menu_item($next_id, 'Manual de Convivencia', $manual_url, $directivo_item->ID);
    } else {
        $manual_item->title = 'Manual de Convivencia';
        $manual_item->url = $manual_url;
        $manual_item->menu_item_parent = (string) $directivo_item->ID;
    }

    $directory_page = get_page_by_path('directorio');
    $directory_url = $directory_page ? get_permalink($directory_page->ID) : home_url('/directorio/');
    if (!$directory_item) {
        $next_id++;
        $directory_item = edusiteco_create_virtual_menu_item($next_id, 'Directorio', $directory_url, $directivo_item->ID);
    } else {
        $directory_item->title = 'Directorio';
        $directory_item->url = $directory_url;
        $directory_item->menu_item_parent = (string) $directivo_item->ID;
    }

    foreach (array($manual_item, $directory_item) as $child_item) {
        $child_index = array_search($child_item, $items, true);
        if (false !== $child_index) {
            array_splice($items, $child_index, 1);
        }
    }

    $directivo_index = array_search($directivo_item, $items, true);
    $directivo_children_index = $directivo_index + 1;
    foreach ($items as $index => $item) {
        if ((string) $directivo_item->ID === (string) $item->menu_item_parent) {
            $directivo_children_index = $index + 1;
        }
    }
    array_splice($items, $directivo_children_index, 0, array($manual_item, $directory_item));

    if (!$docente_item) {
        $next_id++;
        $docente_item = edusiteco_create_virtual_menu_item($next_id, 'Docente', $profesores_url, $academic_item->ID);
    } else {
        $docente_item->title = 'Docente';
        $docente_item->url = $profesores_url;
        $docente_item->menu_item_parent = (string) $academic_item->ID;
    }

    if ($psychology_item) {
        $psychology_item->menu_item_parent = (string) $academic_item->ID;
    }

    $docente_index = array_search($docente_item, $items, true);
    if (false !== $docente_index) {
        array_splice($items, $docente_index, 1);
    }

    $psychology_index = $psychology_item ? array_search($psychology_item, $items, true) : false;
    $academic_index = array_search($academic_item, $items, true);
    $child_insert_index = false !== $psychology_index ? $psychology_index : $academic_index + 1;
    array_splice($items, $child_insert_index, 0, array($docente_item));

    if (!$administrative_item) {
        $next_id++;
        $administrative_item = edusiteco_create_virtual_menu_item($next_id, $titles['administrativa_financiera'], '#', 0);
    } else {
        $administrative_item->title = $titles['administrativa_financiera'];
        $administrative_item->menu_item_parent = '0';
    }

    $administrative_item->url = '#';
    $administrative_item->classes = array_values(array_unique(array_merge(
        (array) $administrative_item->classes,
        array('menu-item-has-children')
    )));

    $administrative_children = array();
    foreach ($administrative_child_titles as $child_slug => $child_title) {
        if (isset($administrative_child_items[$child_slug])) {
            $child_item = $administrative_child_items[$child_slug];
            $child_item->title = $child_title;
            $child_item->url = '#';
            $child_item->menu_item_parent = (string) $administrative_item->ID;
            $child_index = array_search($child_item, $items, true);
            if (false !== $child_index) {
                array_splice($items, $child_index, 1);
            }
        } else {
            $next_id++;
            $child_item = edusiteco_create_virtual_menu_item($next_id, $child_title, '#', $administrative_item->ID);
        }

        $administrative_children[] = $child_item;
    }

    $administrative_index = array_search($administrative_item, $items, true);
    if (false !== $administrative_index) {
        array_splice($items, $administrative_index, 1);
    }

    $convocatorias_index = $convocatorias_item ? array_search($convocatorias_item, $items, true) : false;
    $group_insert_index = false !== $convocatorias_index ? $convocatorias_index + 1 : count($items);
    array_splice($items, $group_insert_index, 0, array($administrative_item));
    array_splice($items, $group_insert_index + 1, 0, $administrative_children);

    return array_values(array_filter($items, function ($item) use ($administrative_item) {
        return absint($item->ID) !== absint($administrative_item->ID)
            && (string) $item->menu_item_parent !== (string) $administrative_item->ID;
    }));
}
add_filter('wp_nav_menu_objects', 'edusiteco_organize_primary_menu', 10, 2);

/**
 * Create a menu item used for display only; it is not saved in WordPress.
 *
 * @param int    $id     Menu item ID.
 * @param string $title  Menu item title.
 * @param string $url    Menu item URL.
 * @param int    $parent Parent menu item ID.
 * @return stdClass
 */
function edusiteco_create_virtual_menu_item($id, $title, $url, $parent)
{
    $item = new stdClass();
    $item->ID = $id;
    $item->db_id = $id;
    $item->menu_item_parent = (string) $parent;
    $item->object_id = 0;
    $item->object = 'custom';
    $item->type = 'custom';
    $item->type_label = 'Custom Link';
    $item->title = $title;
    $item->url = $url;
    $item->target = '';
    $item->attr_title = '';
    $item->description = '';
    $item->classes = array('menu-item', 'menu-item-type-custom', 'menu-item-object-custom', 'menu-item-' . $id);
    $item->xfn = '';
    $item->current = false;
    $item->current_item_ancestor = false;
    $item->current_item_parent = false;
    $item->current_object = false;
    $item->current_object_id = 0;

    return $item;
}


/**
 * Add the news section to the primary menu when a custom menu omits it.
 *
 * @param string   $items Menu item markup.
 * @param stdClass $args  Menu arguments.
 * @return string
 */
function edusiteco_append_news_to_primary_menu($items, $args)
{
    if (!isset($args->theme_location) || 'menu-1' !== $args->theme_location) {
        return $items;
    }

    $titles = edusiteco_get_primary_menu_titles();
    $plain_items = wp_strip_all_tags($items);
    if (false !== stripos($plain_items, $titles['documental']) || false !== stripos($plain_items, 'Noticias')) {
        return $items;
    }

    $has_header_image = get_header_image() ? true : false;
    $comunicados_url = get_post_type_archive_link('comunicado');
    if (!$comunicados_url) {
        $comunicados_url = home_url('/comunicados/');
    }

    $eventos_category = get_category_by_slug('eventos');
    $eventos_url = $eventos_category
        ? get_category_link($eventos_category->term_id)
        : $comunicados_url;

    $circulares_category = get_category_by_slug('circulares');
    $circulares_url = $circulares_category
        ? get_category_link($circulares_category->term_id)
        : $comunicados_url;

    if (isset($args->walker) && $args->walker instanceof Mobile_Nav_Walker) {
        $mobile_link_classes = $has_header_image
            ? 'text-white bg-white/15 hover:bg-white/25'
            : 'text-text-light dark:text-text-dark bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700';
        $mobile_toggle_classes = $has_header_image
            ? 'text-white bg-white/15 hover:bg-white/25'
            : 'text-text-light dark:text-text-dark bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700';
        $mobile_submenu_classes = $has_header_image
            ? 'text-white hover:bg-white/20'
            : 'text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700';

        $items .= '<li class="border-b border-border-light dark:border-border-dark last:border-b-0">';
        $items .= '<div class="flex items-center">';
        $items .= '<a href="' . esc_url($comunicados_url) . '" class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium ' . esc_attr($mobile_link_classes) . ' transition-all">' . esc_html($titles['documental']) . '</a>';
        $items .= '<button type="button" class="mobile-toggle flex-shrink-0 px-4 py-3.5 ' . esc_attr($mobile_toggle_classes) . ' transition-all" aria-expanded="false" aria-controls="primary-mobile-news" aria-label="' . esc_attr(sprintf('Mostrar secciones de %s', $titles['documental'])) . '">';
        $items .= '<svg class="w-5 h-5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        $items .= '</button></div><div id="primary-mobile-news" class="hidden"><ul class="pl-6 space-y-0">';
        $items .= '<li><a class="block pl-4 pr-4 py-3.5 text-base font-medium ' . esc_attr($mobile_submenu_classes) . ' transition-all" href="' . esc_url($comunicados_url) . '">Comunicados</a></li>';
        $items .= '<li><a class="block pl-4 pr-4 py-3.5 text-base font-medium ' . esc_attr($mobile_submenu_classes) . ' transition-all" href="' . esc_url($circulares_url) . '">Circulares</a></li>';
        $items .= '<li><a class="block pl-4 pr-4 py-3.5 text-base font-medium ' . esc_attr($mobile_submenu_classes) . ' transition-all" href="' . esc_url($eventos_url) . '">Eventos</a></li>';
        $items .= '</ul></div></li>';

        return $items;
    }

    $desktop_text_classes = $has_header_image
        ? 'text-white drop-shadow-md hover:text-brand-primary-200'
        : 'text-text-light dark:text-text-dark hover:text-brand-primary dark:hover:text-brand-primary-300';
    $desktop_icon_classes = $has_header_image
        ? 'text-white drop-shadow-sm'
        : 'text-text-light dark:text-text-dark';

    $items .= '<li class="relative header-group group">';
    $items .= '<a href="' . esc_url($comunicados_url) . '" class="' . esc_attr($desktop_text_classes) . ' text-lg font-bold transition-all duration-200 rounded-sm no-underline" aria-haspopup="true" aria-expanded="false">';
    $items .= '<span class="underline-link">' . esc_html($titles['documental']) . '</span>';
    $items .= '<svg class="w-5 h-5 ml-1 ' . esc_attr($desktop_icon_classes) . ' group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></a>';
    $items .= '<ul class="submenu">';
    $items .= '<li><a class="submenu-item" href="' . esc_url($comunicados_url) . '">Comunicados</a></li>';
    $items .= '<li><a class="submenu-item" href="' . esc_url($circulares_url) . '">Circulares</a></li>';
    $items .= '<li><a class="submenu-item" href="' . esc_url($eventos_url) . '">Eventos</a></li>';
    $items .= '</ul></li>';

    return $items;
}
add_filter('wp_nav_menu_items', 'edusiteco_append_news_to_primary_menu', 10, 2);


// ========== FALLBACK FUNCTIONS ==========

// Fallback function for desktop menu
function edusiteco_default_menu()
{
    $has_header_image = get_header_image() ? true : false;
    $menu_titles = edusiteco_get_primary_menu_titles();

    // Clases dinámicas basadas en imagen
    $link_class = $has_header_image
        ? 'text-white drop-shadow-md hover:text-yellow-200 focus:ring-white/50'
        : 'text-text-light dark:text-text-dark hover:text-brand-primary dark:hover:text-brand-primary-300 focus:ring-brand-primary/50';

    $icon_class = $has_header_image
        ? 'text-white drop-shadow-sm'
        : 'text-text-light dark:text-text-dark';
?>
    <ul class="flex items-center space-x-1">
        <li class="relative header-group group">
            <?php
            $mision_page = get_page_by_path('mision-vision-y-valores');
            $mision_url = $mision_page ? get_permalink($mision_page->ID) : '#';

            $history_page = get_page_by_path('historia');
            $history_url = $history_page ? get_permalink($history_page->ID) : '#';

            $directory_page = get_page_by_path('directorio');
            $directory_url = $directory_page ? get_permalink($directory_page->ID) : '#';

            $manual_page = get_page_by_path('manual-de-convivencia');
            $manual_url = $manual_page ? get_permalink($manual_page->ID) : home_url('/manual-de-convivencia/');

            $symbols_page = get_page_by_path('simbolos-institucionales');
            $symbols_url = $symbols_page ? get_permalink($symbols_page->ID) : '#';

            $contact_page = get_page_by_path('contactanos');
            $contact_url = $contact_page ? get_permalink($contact_page->ID) : '#';

            $psico_page = get_page_by_path('psicoorientacion-escolar');
            $psico_url = $psico_page ? get_permalink($psico_page->ID) : '#';

            $contratacion_page = get_page_by_path('portal-de-contratacion');
            $contratacion_url = $contratacion_page ? get_permalink($contratacion_page->ID) : home_url('/portal-de-contratacion/');
            
            ?>

            <a href="#"
                class="<?= $link_class ?> inline-flex items-center text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-background-light dark:focus:ring-offset-background-dark">
                <span><?php echo esc_html($menu_titles['acerca_de']); ?></span>
                <svg
                    class="w-4 h-4 ml-1 <?= $icon_class ?> group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </a>

            <ul class="submenu">
                <li><a class="submenu-item" href="<?= $mision_url ?>">Misión, Visión y Valores</a></li>
                <li><a class="submenu-item" href="<?= $history_url ?>">Nuestra Historia</a></li>
                <li><a class="submenu-item" href="<?= $symbols_url ?>">Símbolos Institucionales</a></li>
            </ul>
        </li>

        <li class="relative header-group group">
            <?php
            $comunicados_archive_url = get_post_type_archive_link('comunicado');

            $eventos_category = get_category_by_slug('eventos');
            $eventos_url       = $eventos_category ? get_category_link($eventos_category->term_id) : '#';

            $circulares_category = get_category_by_slug('circulares');
            $circulares_url       = $circulares_category ? get_category_link($circulares_category->term_id) : '#';
            ?>
            <a href="#"
                class=" inline-flex items-center text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-background-light dark:focus:ring-offset-background-dark">
                <span><?php echo esc_html($menu_titles['documental']); ?></span>
                <svg class="w-4 h-4 ml-1 <?= $icon_class ?> group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </a>

            <ul class="submenu">
                <li>
                    <a class="submenu-item" href="<?php echo esc_url($comunicados_archive_url); ?>">
                        Comunicados
                    </a>
                </li>
                <li>
                    <a class="submenu-item" href="<?php echo esc_url($circulares_url); ?>">
                        Circulares
                    </a>
                </li>
                <li>
                    <a class="submenu-item" href="<?php echo esc_url($eventos_url); ?>">
                        Eventos
                    </a>
                </li>
            </ul>
        </li>

        <?php
        $prof_page = get_page_by_path('profesores');
        $profesores_url = $prof_page ? get_permalink($prof_page->ID) : '#';
        ?>
        <li class="relative header-group group">
            <a class="<?= $link_class ?> inline-flex items-center text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                href="#">
                <span><?php echo esc_html($menu_titles['academico']); ?></span>
                <svg class="w-4 h-4 ml-1 <?= $icon_class ?> group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </a>
            <ul class="submenu">
                <li><a class="submenu-item" href="<?= esc_url($profesores_url) ?>">Docente</a></li>
                <li><a class="submenu-item" href="<?= esc_url($psico_url) ?>">Psicoorientación</a></li>
            </ul>
        </li>

        <li class="header-group">
            <a class="<?= $link_class ?> text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                href="<?= $contact_url ?>">
                <span><?php echo esc_html($menu_titles['contacto']); ?></span>
            </a>
        </li>
        <li class="relative header-group group">
            <a class="<?= $link_class ?> inline-flex items-center text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                href="#">
                <span>G. Directivo</span>
                <svg class="w-4 h-4 ml-1 <?= $icon_class ?> group-hover:rotate-180 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </a>
            <ul class="submenu">
                <li><a class="submenu-item" href="<?= esc_url($manual_url) ?>">Manual de Convivencia</a></li>
                <li><a class="submenu-item" href="<?= esc_url($directory_url) ?>">Directorio</a></li>
            </ul>
        </li>
        <li class="header-group">
            <a class="<?= $link_class ?> text-sm font-semibold underline-link transition-all px-3 py-2 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                href="<?= esc_url($contratacion_url) ?>">
                <span><?php echo esc_html($menu_titles['convocatorias']); ?></span>
            </a>
        </li>
    </ul>

<?php
}

// Fallback function for mobile menu
function edusiteco_default_mobile_menu()
{
    $has_header_image = get_header_image() ? true : false;
    $menu_titles = edusiteco_get_primary_menu_titles();

    $institution_page = get_page_by_path('la-institucion');
    $institution_url = $institution_page ? get_permalink($institution_page->ID) : '#';

    $mision_page = get_page_by_path('mision-vision-y-valores');
    $mision_url = $mision_page ? get_permalink($mision_page->ID) : '#';

    $history_page = get_page_by_path('historia');
    $history_url = $history_page ? get_permalink($history_page->ID) : '#';

    $directory_page = get_page_by_path('directorio');
    $directory_url = $directory_page ? get_permalink($directory_page->ID) : '#';

    $manual_page = get_page_by_path('manual-de-convivencia');
    $manual_url = $manual_page ? get_permalink($manual_page->ID) : home_url('/manual-de-convivencia/');

    $symbols_page = get_page_by_path('simbolos-institucionales');
    $symbols_url = $symbols_page ? get_permalink($symbols_page->ID) : '#';

    $psico_page = get_page_by_path('psicoorientacion-escolar');
    $psico_url = $psico_page ? get_permalink($psico_page->ID) : '#';

    $prof_page = get_page_by_path('profesores');
    $profesores_url = $prof_page ? get_permalink($prof_page->ID) : home_url('/profesores/');

    $comunicados_url = get_post_type_archive_link('comunicado');
    if (!$comunicados_url) {
        $comunicados_url = home_url('/comunicados/');
    }

    $eventos_category = get_category_by_slug('eventos');
    $eventos_url = $eventos_category ? get_category_link($eventos_category->term_id) : $comunicados_url;

    $circulares_category = get_category_by_slug('circulares');
    $circulares_url = $circulares_category ? get_category_link($circulares_category->term_id) : $comunicados_url;

    $contact_page = get_page_by_path('contactanos');
    $contact_url = $contact_page ? get_permalink($contact_page->ID) : '#';

    $contratacion_page = get_page_by_path('portal-de-contratacion');
    $contratacion_url = $contratacion_page ? get_permalink($contratacion_page->ID) : home_url('/portal-de-contratacion/');

    // Estilos dinámicos para móvil
    $link_classes = $has_header_image
        ? 'text-white bg-white/15 hover:bg-white/25'
        : 'text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-gray-700';

    $submenu_link_classes = $has_header_image
        ? 'text-white hover:bg-white/20'
        : 'text-text-light dark:text-text-dark hover:bg-gray-100 dark:hover:bg-gray-700';
?>
    <div class="border-b border-border-light dark:border-border-dark last:border-b-0">
        <div class="flex items-center">
            <a href="<?= $institution_url ?>"
                class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> transition-all">
                <?php echo esc_html($menu_titles['acerca_de']); ?>
            </a>
            <button class="mobile-toggle flex-shrink-0 px-4 py-3.5 <?= $link_classes ?> transition-all"
                aria-expanded="false" aria-controls="default-submenu-1" aria-label="<?php echo esc_attr(sprintf('Mostrar secciones de %s', $menu_titles['acerca_de'])); ?>">
                <svg class="w-4 h-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
        </div>
        <div id="default-submenu-1" class="hidden">
            <div class="pl-6">
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= $history_url ?>">
                    Nuestra Historia
                </a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= $mision_url ?>">
                    Misión, Visión y Valores
                </a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= $symbols_url ?>">
                    Símbolos Institucionales
                </a>
            </div>
        </div>
    </div>

    <div class="border-b border-border-light dark:border-border-dark last:border-b-0">
        <div class="flex items-center">
            <a href="<?= esc_url($comunicados_url) ?>"
                class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> transition-all">
                <?php echo esc_html($menu_titles['documental']); ?>
            </a>
            <button type="button" class="mobile-toggle flex-shrink-0 px-4 py-3.5 <?= $link_classes ?> transition-all"
                aria-expanded="false" aria-controls="default-submenu-news" aria-label="<?php echo esc_attr(sprintf('Mostrar secciones de %s', $menu_titles['documental'])); ?>">
                <svg class="w-4 h-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
        </div>
        <div id="default-submenu-news" class="hidden">
            <div class="pl-6">
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= esc_url($comunicados_url) ?>">
                    Comunicados
                </a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= esc_url($circulares_url) ?>">
                    Circulares
                </a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= esc_url($eventos_url) ?>">
                    Eventos
                </a>
            </div>
        </div>
    </div>

    <div class="border-b border-border-light dark:border-border-dark last:border-b-0">
        <div class="flex items-center">
            <a href="#" class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> transition-all">
                <?php echo esc_html($menu_titles['academico']); ?>
            </a>
            <button type="button" class="mobile-toggle flex-shrink-0 px-4 py-3.5 <?= $link_classes ?> transition-all"
                aria-expanded="false" aria-controls="default-submenu-academico" aria-label="<?php echo esc_attr(sprintf('Mostrar secciones de %s', $menu_titles['academico'])); ?>">
                <svg class="w-4 h-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
        </div>
        <div id="default-submenu-academico" class="hidden">
            <div class="pl-6">
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= esc_url($profesores_url) ?>">Docente</a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all"
                    href="<?= esc_url($psico_url) ?>">Psicoorientación</a>
            </div>
        </div>
    </div>
    <a class="block pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> border-b border-border-light dark:border-border-dark last:border-b-0 transition-all"
        href="<?= esc_url($contact_url) ?>"><?php echo esc_html($menu_titles['contacto']); ?></a>
    <div class="border-b border-border-light dark:border-border-dark last:border-b-0">
        <div class="flex items-center">
            <a href="#" class="flex-1 pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> transition-all">G. Directivo</a>
            <button type="button" class="mobile-toggle flex-shrink-0 px-4 py-3.5 <?= $link_classes ?> transition-all"
                aria-expanded="false" aria-controls="default-submenu-directivo" aria-label="Mostrar secciones de G. Directivo">
                <svg class="w-4 h-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
        </div>
        <div id="default-submenu-directivo" class="hidden">
            <div class="pl-6">
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all" href="<?= esc_url($manual_url) ?>">Manual de Convivencia</a>
                <a class="block pl-8 pr-4 py-3 text-base font-medium <?= $submenu_link_classes ?> transition-all" href="<?= esc_url($directory_url) ?>">Directorio</a>
            </div>
        </div>
    </div>
    <a class="block pl-4 pr-4 py-3.5 text-base font-medium <?= $link_classes ?> border-b border-border-light dark:border-border-dark last:border-b-0 transition-all"
        href="<?= esc_url($contratacion_url) ?>"><?php echo esc_html($menu_titles['convocatorias']); ?></a>
<?php
}
