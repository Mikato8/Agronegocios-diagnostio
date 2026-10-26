<?php /** @var array<string, int> $stats */ ?>
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Nosotros</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Quiénes somos</span>
                <h1>Una comunidad para aprender agronegocios con evidencia.</h1>
                <p>AgroNegocios MX es un archivo abierto de casos, reportes financieros y oportunidades creado por estudiantes de agronegocios de México. No es un consultorio ni una bolsa de inversión: es un espacio de formación profesional.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($stats['students'] ?? 0) ?></span>
                <span>estudiantes verificados</span>
                <span class="muted"><?= number_format($stats['universities'] ?? 0) ?> universidades registradas</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>Por qué existe</h2>
            <p>En las aulas se estudian modelos, ratios y marcos de estrategia. En el campo, las decisiones llegan con información incompleta, precios volátiles, clima adverso y relaciones familiares o comunitarias que no caben en una hoja de cálculo. Este portal existe para cerrar esa brecha: documentar decisiones reales y discutirlas con método.</p>
            <p>También existe porque publicar no debería depender de tener una cédula profesional. Un estudiante puede observar, entrevistar, ordenar cifras y proponer una interpretación. Lo que garantiza la calidad no es un título, sino la transparencia del método, la revisión entre pares y la honestidad sobre los límites del trabajo.</p>

            <h2>Qué puedes hacer aquí</h2>
            <ul>
                <li><strong>Casos:</strong> documenta el contexto, el reto, el diagnóstico, la estrategia y los resultados de una empresa o proyecto.</li>
                <li><strong>Reportes financieros:</strong> captura estados de resultados y balances; el portal calcula indicadores y tú explicas qué significan.</li>
                <li><strong>Oportunidades:</strong> describe un mercado, una inversión y los requisitos para participar, con riesgos explícitos.</li>
                <li><strong>Discusión:</strong> comenta, pregunta y compara con otros casos. La comunidad es parte del aprendizaje.</li>
            </ul>

            <h2>Cómo trabajamos</h2>
            <p>Un comité editorial revisa cada publicación antes de hacerla pública. Verifica que el texto tenga estructura, que las cifras sean coherentes, que no haya datos personales sin consentimiento y que el autor distinga hechos de opiniones. La revisión no certifica la veracidad absoluta de un caso: certifica que cumple los criterios editoriales y que es discutible con evidencia.</p>
            <p>La verificación de cuentas es académica. Si tu correo es institucional (<code>.edu.mx</code> o un dominio universitario registrado), la cuenta se verifica automáticamente. Si usas un correo personal, puedes subir una constancia de estudios y un editor la revisará. <strong>Nunca pedimos cédula profesional.</strong></p>

            <h2>Lo que no somos</h2>
            <p>No somos una consultoría, un despacho financiero, una autoridad sanitaria ni una plataforma de inversión. No auditamos estados financieros ni garantizamos rendimientos. Las publicaciones son trabajos académicos y deben leerse como tales. Si necesitas una decisión profesional, acude a un especialista y verifica la normativa aplicable.</p>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('activity', 'icon icon-sm') ?> En números</h2>
                <dl class="plot-data">
                    <div><dt>Casos publicados</dt><dd><?= number_format($stats['cases'] ?? 0) ?></dd></div>
                    <div><dt>Reportes</dt><dd><?= number_format($stats['reports'] ?? 0) ?></dd></div>
                    <div><dt>Oportunidades</dt><dd><?= number_format($stats['opportunities'] ?? 0) ?></dd></div>
                    <div><dt>Estados</dt><dd><?= number_format($stats['states'] ?? 0) ?></dd></div>
                    <div><dt>Sectores</dt><dd><?= number_format($stats['sectors'] ?? 0) ?></dd></div>
                </dl>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Principios</h2>
                <ul class="check-list check-list-small">
                    <li>Transparencia sobre el método y los límites.</li>
                    <li>Consentimiento y anonimización.</li>
                    <li>Crédito y licencia para cada autor.</li>
                    <li>Revisión editorial con retroalimentación.</li>
                    <li>Ninguna promesa de rendimiento.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('mail', 'icon icon-sm') ?> Contacto</h2>
                <p>¿Quieres proponer una colaboración, una corrección o una visita de campo?</p>
                <a class="btn btn-outline btn-block btn-sm" href="mailto:hola@agronegocios.mx">hola@agronegocios.mx</a>
            </div>
        </aside>
    </div>
</section>
