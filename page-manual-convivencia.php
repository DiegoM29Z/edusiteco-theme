<?php
/**
 * Template Name: Manual de Convivencia
 * Description: Muestra el Manual de Convivencia institucional en el sitio.
 */

get_header();

$upload_dir = wp_upload_dir();
$manual_url = trailingslashit($upload_dir['baseurl']) . 'manuales/manual-de-convivencia-revision-2026.pdf';
?>

<main id="primary" class="site-main bg-white dark:bg-gray-900 min-h-screen">
    <section class="bg-gradient-to-r from-blue-900 to-blue-700 dark:from-blue-950 dark:to-blue-800 text-white py-12 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 font-plus-jakarta text-white">Manual de Convivencia</h1>
            <p class="text-lg md:text-xl max-w-3xl mx-auto text-white">Institución Educativa Rafael Pombo</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <p class="text-text-light dark:text-text-dark">Consulta aquí el documento completo.</p>
            <a class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                href="<?php echo esc_url($manual_url); ?>" target="_blank" rel="noopener noreferrer">
                Abrir o descargar el manual
            </a>
        </div>
        <iframe class="w-full rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg"
            src="<?php echo esc_url($manual_url); ?>#toolbar=1&navpanes=0&view=FitH"
            title="Manual de Convivencia de la Institución Educativa Rafael Pombo"
            style="height: 85vh; min-height: 700px;"
            loading="lazy"></iframe>
    </section>
</main>

<?php
get_footer();
