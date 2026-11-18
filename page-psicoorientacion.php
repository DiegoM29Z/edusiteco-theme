<?php
/**
 * Template Name: Psicoorientación
 * Description: Página para Psicoorientación integrada con el Plugin Edusiteco.
 */

get_header();

$o = get_option('info_colegio_settings');
$o = is_array($o) ? $o : array();

$hero_title = !empty($o['psico_hero_titulo']) ? $o['psico_hero_titulo'] : get_the_title();
$saludo = $o['psico_saludo'] ?? 'Aquí estamos para escucharte. Este es un espacio seguro para ayudarte con tus emociones, tus estudios y tu futuro. No estás solo en tu proceso.';
$hero_image = !empty($o['profesores']['hero_image'])
    ? $o['profesores']['hero_image']
    : get_template_directory_uri() . '/assets/images/hero-simbolos.jpg';

$default_areas = array(
    array('titulo' => 'Tus Emociones', 'texto' => '¿Te sientes triste, con ansiedad o problemas en casa? Hablemos.', 'icono' => $o['psico_s1_icono'] ?? '', 'material_icon' => 'favorite'),
    array('titulo' => 'Tus Estudios', 'texto' => 'Te ayudamos a organizar tu tiempo y a decidir qué carrera estudiar cuando te gradúes.', 'icono' => $o['psico_s2_icono'] ?? '', 'material_icon' => 'school'),
    array('titulo' => 'Convivencia', 'texto' => 'Ayuda para resolver conflictos con compañeros o detener el acoso escolar (bullying).', 'icono' => $o['psico_s3_icono'] ?? '', 'material_icon' => 'handshake'),
    array('titulo' => 'Para Papás', 'texto' => 'Orientación y consejos para que las familias apoyen a sus hijos.', 'icono' => $o['psico_s4_icono'] ?? '', 'material_icon' => 'family_restroom'),
);
foreach ($default_areas as $i => &$default_area) {
    $number = $i + 1;
    $default_area['titulo'] = $o["psico_s{$number}_titulo"] ?? $default_area['titulo'];
    $default_area['texto'] = $o["psico_s{$number}_texto"] ?? $default_area['texto'];
}
unset($default_area);
$areas = isset($o['psico_areas']) && is_array($o['psico_areas']) ? $o['psico_areas'] : $default_areas;
$areas = array_filter($areas, function ($area) {
    return is_array($area) && !empty(trim($area['titulo'] ?? ''));
});

// Cita
$cita_foto    = $o['psico_foto'] ?? '';
$cita_horario = trim($o['psico_horario'] ?? 'Atendemos de Lunes a Viernes de 8:00 am a 12:00 pm en la oficina del primer piso.');
$cita_url     = trim($o['psico_cita_url'] ?? '');
$cita_telefonos = preg_split('/[,;\/|–—]+/u', trim($o['contacto']['telefono'] ?? ''));
$cita_telefonos = array_filter(array_map('trim', $cita_telefonos), function ($telefono) {
    return preg_replace('/[^+\d]/', '', $telefono) !== '';
});

// Botiquín
$emergencias = trim($o['psico_emergencias'] ?? 'Si hay peligro inmediato, llama a emergencias. Para proteger a niñas, niños y adolescentes, comunícate con el ICBF. Ante una crisis de salud mental, busca atención en urgencias de tu EPS o IPS.');
?>

<main id="primary" class="site-main psico-page">

    <section class="psico-hero" style="background-image: linear-gradient(rgba(51, 102, 204, .85), rgba(51, 102, 204, .9)), url('<?php echo esc_url($hero_image); ?>');">
        <div class="psico-hero__content">
            <h1 class="text-4xl md:text-6xl font-bold font-display mb-4 tracking-tight"><?php echo esc_html($hero_title); ?></h1>
            <p><?php echo esc_html($saludo); ?></p>
        </div>
    </section>

    <div class="psicoorientacion-backdrop">
    <div class="psico-page__content">

        <?php if (!empty($areas)): ?>
        <section id="apoyo" class="psico-section">
            <div class="psico-section__heading">
                <h2>Áreas de ayuda</h2>
            </div>
            <div class="psico-support-grid">
                <?php foreach ($areas as $area) :
                    $area_image = isset($area['icono']) ? $area['icono'] : '';
                    $area_icon = !empty($area['material_icon']) ? $area['material_icon'] : 'psychology';
                    ?>
                    <article class="psico-support-card">
                        <div class="psico-support-card__icon">
                            <?php if ($area_image): ?>
                                <img src="<?php echo esc_url($area_image); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="material-icons" aria-hidden="true"><?php echo esc_html($area_icon); ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc_html($area['titulo']); ?></h3>
                        <?php if (!empty($area['texto'])): ?>
                            <p><?php echo esc_html($area['texto']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php
        $recursos = get_posts(array(
            'post_type'      => 'psico_recurso',
            'numberposts'    => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        $recursos_publicos = array();
        foreach ($recursos as $recurso) {
            $attachment_id = (int) get_post_meta($recurso->ID, 'recurso_attachment_id', true);
            if (!$attachment_id) {
                $attachment_id = get_post_thumbnail_id($recurso->ID);
            }
            $file_url = get_post_meta($recurso->ID, 'recurso_url', true);
            $file_url = $file_url ? $file_url : ($attachment_id ? wp_get_attachment_url($attachment_id) : '');
            $resource_content = trim($recurso->post_content);
            $mime = $attachment_id ? get_post_mime_type($attachment_id) : '';
            $recursos_publicos[] = array(
                'id'            => $recurso->ID,
                'title'         => get_the_title($recurso->ID),
                'content'       => $resource_content,
                'attachment_id' => $attachment_id,
                'file_url'      => $file_url,
                'is_pdf'        => $mime && strpos($mime, 'pdf') !== false,
                'is_image'      => $mime && strpos($mime, 'image/') === 0,
            );
        }
        if (empty($recursos_publicos)) {
            $resource_files_url = get_template_directory_uri() . '/assets/psicoorientacion/';
            $recursos_publicos = array(
                array(
                    'title'         => 'Estrategias emocionales para aulas multigrado',
                    'content'       => 'En grupos con edades distintas, trabaja un tema común —emociones, enojo, empatía, conflictos o autocuidado— y adapta la profundidad según el desarrollo. Los más pequeños pueden identificar y expresar emociones; los mayores, analizar causas y proponer soluciones. Se sugieren tutoría entre pares, juegos cooperativos, cuentos, dramatizaciones, música, rueda emocional, semáforo y rincón de calma. Conecta las actividades con experiencias de la comunidad y valora la participación sin comparar a estudiantes de distintas edades.',
                    'attachment_id' => 0,
                    'file_url'      => $resource_files_url . 'Estrategias_Aulas_Multigrado.pdf',
                    'is_pdf'        => true,
                    'is_image'      => false,
                ),
                array(
                    'title'         => 'Identidad y proyecto de vida en adolescencia',
                    'content'       => 'Las mallas disponibles de 7.º, 8.º, 9.º y 11.º abordan identidad, cambios, autoestima, pertenencia, relaciones, autocuidado y toma de decisiones. En secundaria se reflexiona sobre la presión de pares y redes, el manejo de emociones, los riesgos y los hábitos saludables; en 11.º se incorpora el proyecto de vida y decisiones con impacto personal y colectivo. La propuesta recomienda actividades de reflexión, acuerdos grupales y registro personal, acompañados por adultos de confianza.',
                    'attachment_id' => 0,
                    'file_url'      => $resource_files_url . 'Mallas_Adolescencia_7_8_9_11.pdf',
                    'is_pdf'        => true,
                    'is_image'      => false,
                ),
                array(
                    'title'         => 'Habilidades socioemocionales en primaria',
                    'content'       => 'Las mallas de 1.º a 5.º proponen fortalecer el autoconocimiento, la autoestima, el reconocimiento y la regulación de emociones, la autonomía y las decisiones responsables, la empatía, la comunicación, la cooperación y la convivencia. Las actividades combinan experiencias lúdicas, acuerdos de aula y reflexión en la agenda emocional: reconocer cualidades y gustos, valorar logros, expresar lo que se siente y pensar en las consecuencias de las acciones. Ajusta cada actividad al grado y al contexto del grupo.',
                    'attachment_id' => 0,
                    'file_url'      => $resource_files_url . 'Mallas_Primaria_1_a_5.pdf',
                    'is_pdf'        => true,
                    'is_image'      => false,
                ),
                array(
                    'title'         => 'Educacion Inicial',
                    'content'       => 'Tiene como fin crear e implementar la Cátedra de Educación Emocional en los niveles preescolar, básica y media, en concordancia con el Proyecto Educativo Institucional (PEI). En preescolar (3 a 5 años), la malla propone fortalecer la autoestima, la autorregulación con apoyo adulto, la empatía, las habilidades sociales y la toma de decisiones sencillas. Sugiere trabajar alegría, tristeza, enojo, miedo y sorpresa mediante juego simbólico, cuentos, canciones, experiencias sensoriales, respiración y rutinas de calma. Propone una hora semanal, integrable en las rutinas, y evaluar mediante observación directa en juegos y actividades cotidianas.',
                    'attachment_id' => 0,
                    'file_url'      => $resource_files_url . 'preschool.pdf',
                    'is_pdf'        => true,
                    'is_image'      => false,
                ),
            );
        }
        if (!empty($recursos_publicos)):
        ?>
            <section id="recursos" class="psico-section psico-resources">
                <div class="psico-section__heading">
                    <h2>Botiquín de ayuda</h2>
                </div>
                <div class="psico-resources__grid<?php echo count($recursos_publicos) === 1 ? ' psico-resources__grid--single' : ''; ?>">
                    <?php foreach ($recursos_publicos as $recurso): ?>
                        <article class="psico-resource-card">
                            <?php if ($recurso['is_image'] && $recurso['attachment_id']): ?>
                                <div class="psico-resource-card__preview">
                                    <?php echo wp_get_attachment_image($recurso['attachment_id'], 'medium_large', false, array('alt' => '')); ?>
                                </div>
                            <?php else: ?>
                                <div class="psico-resource-card__top">
                                    <span class="material-icons" aria-hidden="true"><?php echo $recurso['is_pdf'] ? 'picture_as_pdf' : 'description'; ?></span>
                                    <?php if ($recurso['is_pdf']): ?>
                                        <span class="psico-resource-card__type">PDF</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="psico-resource-card__body">
                                <h3><?php echo esc_html($recurso['title']); ?></h3>
                                <?php if ($recurso['content'] !== ''): ?>
                                    <?php
                                    $resource_excerpt = wp_trim_words($recurso['content'], 28, '…');
                                    $resource_has_more = $resource_excerpt !== $recurso['content'];
                                    ?>
                                    <?php if ($resource_has_more): ?>
                                        <p class="psico-resource-card__excerpt"><?php echo esc_html($resource_excerpt); ?></p>
                                        <details class="psico-resource-card__details">
                                            <summary>
                                                <span class="psico-resource-card__read-more">Leer más</span>
                                                <span class="psico-resource-card__read-less">Ver menos</span>
                                            </summary>
                                            <div class="psico-resource-card__description"><?php echo wpautop(esc_html($recurso['content'])); ?></div>
                                        </details>
                                    <?php else: ?>
                                        <div class="psico-resource-card__description"><?php echo wpautop(esc_html($recurso['content'])); ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($recurso['file_url']): ?>
                                <a class="psico-resource-card__link" href="<?php echo esc_url($recurso['file_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="material-icons" aria-hidden="true">open_in_new</span>
                                    <?php echo $recurso['is_pdf'] ? 'Ver PDF completo' : 'Abrir recurso'; ?>
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($cita_foto || $cita_horario !== '' || ($cita_url !== '' && '#' !== $cita_url) || !empty($cita_telefonos)): ?>
            <section id="cita" class="psico-appointment">
                <?php if ($cita_foto): ?>
                    <div class="psico-appointment__visual" aria-hidden="true">
                        <img src="<?php echo esc_url($cita_foto); ?>" alt="">
                    </div>
                <?php endif; ?>
                <div class="psico-appointment__content">
                    <h2>¿Cómo pedir una cita?</h2>
                    <?php if ($cita_horario !== ''): ?>
                        <p><?php echo esc_html($cita_horario); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($cita_telefonos)): ?>
                        <p>
                            Para solicitar una cita, comunícate con el colegio por teléfono:
                        </p>
                        <?php foreach ($cita_telefonos as $telefono):
                            $telefono_url = preg_replace('/[^+\d]/', '', $telefono);
                        ?>
                            <a href="tel:<?php echo esc_attr($telefono_url); ?>" class="psico-button psico-button--light">
                                <span class="material-icons" aria-hidden="true">call</span>
                                Llamar <?php echo esc_html($telefono); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($cita_url !== '' && '#' !== $cita_url): ?>
                        <a href="<?php echo esc_url($cita_url); ?>" target="_blank" rel="noopener noreferrer" class="psico-button psico-button--light">
                            <span class="material-icons" aria-hidden="true">event_available</span>
                            Pedir cita
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="psico-emergency">
            <div class="psico-emergency__icon">
                <span class="material-icons" aria-hidden="true">health_and_safety</span>
            </div>
            <div>
                <h2>Teléfonos importantes</h2>
                <?php if ($emergencias !== ''): ?>
                    <p><?php echo nl2br(esc_html($emergencias)); ?></p>
                <?php endif; ?>
                <div class="psico-emergency__contacts" aria-label="Líneas de emergencia y contacto">
                    <a class="psico-emergency__contact" href="tel:123">
                        <span class="psico-emergency__number">123</span>
                        <span class="psico-emergency__label">Emergencias</span>
                    </a>
                    <a class="psico-emergency__contact" href="tel:141">
                        <span class="psico-emergency__number">141</span>
                        <span class="psico-emergency__label">ICBF · protección a menores</span>
                    </a>
                    <?php foreach ($cita_telefonos as $telefono):
                        $telefono_url = preg_replace('/[^+\d]/', '', $telefono);
                    ?>
                        <a class="psico-emergency__contact" href="tel:<?php echo esc_attr($telefono_url); ?>">
                            <span class="psico-emergency__number"><?php echo esc_html($telefono); ?></span>
                            <span class="psico-emergency__label">Contacto del colegio</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    </div>
    </div>
</main>

<?php get_footer(); ?>
