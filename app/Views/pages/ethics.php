<?php ?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Ética editorial</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Criterios editoriales</span>
                <h1>Ética y revisión</h1>
                <p>Reglas claras para publicar, revisar y discutir casos de agronegocios sin dañar a las personas ni prometer lo que no se puede cumplir.</p>
            </div>
            <div class="page-hero-aside"><span class="big-number">6</span><span>criterios de publicación</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>1. Consentimiento y anonimización</h2>
            <p>Si documentas una empresa, un productor o una comunidad, debes contar con autorización para usar la información. Cuando no sea posible identificar a la fuente, anonimiza nombres, teléfonos, direcciones y cualquier dato que permita localizar a una persona o predio. No publiques documentos con datos personales visibles.</p>

            <h2>2. Veracidad y trazabilidad</h2>
            <p>Distingue entre hechos observados, datos proporcionados por la empresa, estimaciones propias y opiniones. Indica la fuente de las cifras y el periodo al que corresponden. Si no tienes un dato, dilo; no lo inventes ni lo presentes como auditoría.</p>

            <h2>3. Relevancia académica</h2>
            <p>Un caso debe explicar una decisión o un problema de negocio, no ser un anuncio. Un reporte financiero debe incluir análisis, no sólo una tabla. Una oportunidad debe describir mercado, inversión, requisitos y riesgos, no prometer rendimientos.</p>

            <h2>4. Conflicto de interés</h2>
            <p>Declara si trabajas para la empresa, si recibiste pago por el trabajo, si tienes una relación familiar con la dirección o si participas en el proyecto. El conflicto no impide publicar; ocultarlo sí.</p>

            <h2>5. Respeto en la discusión</h2>
            <p>Los comentarios deben aportar evidencia, preguntas o experiencias comparables. No se permiten ataques personales, discriminación, acoso ni divulgación de datos privados. El comité puede ocultar comentarios que incumplan estas reglas.</p>

            <h2>6. Corrección y retiro</h2>
            <p>Si encuentras un error, escribe al comité con la evidencia. Corregiremos el texto y dejaremos constancia cuando el cambio sea sustantivo. El autor puede solicitar el retiro de su contenido; el comité puede conservarlo anonimizado por razones de archivo, avisando al autor.</p>

            <h2>Verificación de cuentas</h2>
            <p>La cuenta se verifica con correo institucional o constancia de estudios. No se solicita cédula profesional. La verificación acredita pertenencia a una institución, no la calidad de cada publicación. Un editor puede suspender cuentas que suplanten identidad, publiquen contenido dañino o incumplan repetidamente estas reglas.</p>

            <h2>Publicidad y financiamiento</h2>
            <p>El portal no vende posiciones editoriales. Si en el futuro recibe financiamiento, se declarará de forma visible y no condicionará la revisión de contenidos. Las oportunidades publicadas no son anuncios pagados.</p>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('alert-triangle', 'icon icon-sm') ?> Señales de alerta</h2>
                <ul class="check-list check-list-small">
                    <li>Promesas de rendimiento garantizado.</li>
                    <li>Presión para invertir rápido.</li>
                    <li>Datos de contacto que no coinciden con la empresa.</li>
                    <li>Cifras sin periodo ni fuente.</li>
                    <li>Solicitudes de dinero por canales informales.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('mail', 'icon icon-sm') ?> Reportar un problema</h2>
                <p>Escribe a <a href="mailto:etica@agronegocios.mx">etica@agronegocios.mx</a> con el enlace y una descripción. Trataremos el reporte de forma reservada.</p>
            </div>
        </aside>
    </div>
</section>
