<?php ?>
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Guías</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Método de trabajo</span>
                <h1>Guías para documentar con criterio</h1>
                <p>Tres formatos, una misma exigencia: contexto, evidencia, decisión y límites. Usa estas guías antes de publicar.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container guide-grid">
        <article class="guide-card">
            <span class="guide-number">01</span>
            <h2><?= icon('folder', 'icon icon-sm') ?> Caso de agronegocios</h2>
            <p>Sirve para aprender de una decisión real: una reconversión productiva, un cambio de canal, una crisis, una alianza o una inversión.</p>
            <ol class="guide-steps">
                <li><strong>Contexto.</strong> Empresa, tamaño, ubicación, producto, clientes, equipo y entorno.</li>
                <li><strong>Reto.</strong> La decisión concreta, las alternativas y las restricciones.</li>
                <li><strong>Diagnóstico.</strong> Datos de costos, mercado, capacidad, flujo o riesgos. Separa hechos de supuestos.</li>
                <li><strong>Estrategia.</strong> Qué se hizo, con qué recursos, aliados y tiempos; qué se descartó.</li>
                <li><strong>Resultados.</strong> Cifras antes/después, evidencia cualitativa y efectos no previstos.</li>
                <li><strong>Aprendizajes.</strong> Qué se repetiría, qué no y qué queda pendiente.</li>
            </ol>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/casos/nuevo')) ?>">Empezar un caso</a>
        </article>

        <article class="guide-card">
            <span class="guide-number">02</span>
            <h2><?= icon('trending-up', 'icon icon-sm') ?> Reporte financiero</h2>
            <p>Sirve para ordenar cifras y convertirlas en una interpretación útil, no en una tabla sin conclusiones.</p>
            <ol class="guide-steps">
                <li><strong>Periodo y moneda.</strong> Define el corte y si las cifras son nominales o reales.</li>
                <li><strong>Estado de resultados.</strong> Ingresos, costo de ventas, gastos, otros ingresos y utilidad neta.</li>
                <li><strong>Balance.</strong> Activo, pasivo y capital. Revisa que la ecuación contable cierre.</li>
                <li><strong>Indicadores.</strong> Márgenes, liquidez, endeudamiento, ROE y ROA. Explica causas, no sólo valores.</li>
                <li><strong>Análisis.</strong> Compara con el periodo anterior o con referencias del sector.</li>
                <li><strong>Recomendaciones.</strong> Qué hacer, en qué orden y con qué indicador medir.</li>
            </ol>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte</a>
        </article>

        <article class="guide-card">
            <span class="guide-number">03</span>
            <h2><?= icon('sparkles', 'icon icon-sm') ?> Oportunidad de negocio</h2>
            <p>Sirve para conectar con socios, proveedores o compradores sin caer en promesas vacías.</p>
            <ol class="guide-steps">
                <li><strong>Mercado.</strong> Demanda, competencia, precios, canales y estacionalidad.</li>
                <li><strong>Propuesta de valor.</strong> Qué problema resuelve y por qué es diferente.</li>
                <li><strong>Modelo.</strong> Cómo se genera ingreso, quién paga y qué costos son críticos.</li>
                <li><strong>Inversión.</strong> Rango, destino de los recursos y supuestos del retorno.</li>
                <li><strong>Requisitos.</strong> Capital, tierra, certificaciones, experiencia o permisos.</li>
                <li><strong>Riesgos.</strong> Clima, precio, regulación, logística y dependencia de un comprador.</li>
            </ol>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/oportunidades/nueva')) ?>">Publicar oportunidad</a>
        </article>
    </div>
</section>

<section class="section">
    <div class="container prose-page-grid">
        <article class="prose-page">
            <h2>Checklist antes de enviar a revisión</h2>
            <ul class="check-list">
                <li><?= icon('check', 'icon icon-sm') ?> El título describe el caso, no una generalidad.</li>
                <li><?= icon('check', 'icon icon-sm') ?> El resumen permite entender el resultado sin leer todo.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las cifras tienen periodo, moneda y fuente.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Los supuestos están separados de los hechos.</li>
                <li><?= icon('check', 'icon icon-sm') ?> No hay nombres, teléfonos o direcciones sin consentimiento.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las imágenes no muestran documentos ni personas identificables.</li>
                <li><?= icon('check', 'icon icon-sm') ?> Las referencias permiten verificar lo afirmado.</li>
                <li><?= icon('check', 'icon icon-sm') ?> El texto explica qué haría distinto el autor.</li>
            </ul>

            <h2>Cómo se calculan los indicadores</h2>
            <p>El portal calcula los indicadores sólo cuando existen las cifras necesarias. No completa datos faltantes. Las fórmulas son:</p>
            <ul>
                <li>Utilidad bruta = ingresos − costo de ventas.</li>
                <li>Utilidad de operación = utilidad bruta − gastos de operación + otros ingresos.</li>
                <li>Margen bruto = utilidad bruta / ingresos.</li>
                <li>Margen operativo = utilidad de operación / ingresos.</li>
                <li>Margen neto = utilidad neta / ingresos.</li>
                <li>Razón circulante = activo circulante / pasivo circulante.</li>
                <li>Pasivo / activo = pasivo total / activo total.</li>
                <li>ROE = utilidad neta / capital contable.</li>
                <li>ROA = utilidad neta / activo total.</li>
            </ul>

            <h2>Plantilla mínima de un caso</h2>
            <p>Si no sabes por dónde empezar, responde estas preguntas en orden. Con eso ya tienes la estructura completa:</p>
            <ol>
                <li>¿Quién es la empresa y qué vende?</li>
                <li>¿Cuál era la decisión y por qué había que tomarla?</li>
                <li>¿Qué datos se tenían y qué faltaba?</li>
                <li>¿Qué alternativas se evaluaron?</li>
                <li>¿Qué se hizo y por qué?</li>
                <li>¿Qué pasó, con cifras y evidencia?</li>
                <li>¿Qué se aprendió y qué se haría distinto?</li>
            </ol>
        </article>
        <aside class="prose-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Ética express</h2>
                <p>Si dudas si un dato es sensible, anonimízalo. Si no tienes autorización, no publiques la empresa. Si no puedes sustentar una cifra, declárala como estimación.</p>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/etica')) ?>">Leer criterios completos</a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> Lecturas</h2>
                <p>Antes de escribir, revisa dos o tres casos publicados y observa cómo estructuran el contexto, las cifras y los aprendizajes.</p>
                <a class="btn btn-primary btn-block btn-sm" href="<?= e(url('/casos')) ?>">Ver casos</a>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('download', 'icon icon-sm') ?> Plantillas</h2>
                <p>Descarga una estructura base para tu caso o tus cifras.</p>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/descargas/plantilla-caso-agronegocios.md')) ?>" download>Plantilla de caso (MD)</a>
                <a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/descargas/plantilla-reporte-financiero.csv')) ?>" download>Plantilla financiera (CSV)</a>
            </div>
        </aside>
    </div>
</section>
