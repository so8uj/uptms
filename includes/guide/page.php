<?php
/*
 * The User Guide page shared by every panel.
 * Set $guide before including it:
 *   $guide = [
 *     'title'    => 'Student guide',
 *     'intro'    => 'One or two sentences.',
 *     'folder'   => 'student',                    // the image folder: assets/admin_assets/images/guide/<folder>/
 *     'switch'   => [['Teacher guide', 'teacher/guide.php', true], ...]   // optional tabs (label, path, active)
 *     'sections' => [ [ 'id', 'icon', 'title', 'summary', 'link' => [label, path], 'steps' => [], 'tips' => [], 'image' => 'file.jpg' or ['a.jpg', 'b.jpg'] ] ],
 *   ];
 * In the texts, [Button name] is drawn like a button and **word** is bold. The content files are in includes/guide/content/.
 */

if (!function_exists('Sobuj_guideText')) {
    function Sobuj_guideText(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        return preg_replace('/\[(.+?)\]/', '<span class="guide-btn">$1</span>', $text);
    }
}

$guideImages = env('ASSET_URL') . '/admin_assets/images/guide/' . $guide['folder'] . '/';
?>

<div class="guide-page">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 guide-hide-print">
        <div>
            <h2 class="h4 mb-1"><?= htmlspecialchars($guide['title']) ?></h2>
            <p class="text-muted mb-0" style="max-width: 760px;"><?= Sobuj_guideText($guide['intro']) ?></p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php foreach (($guide['switch'] ?? []) as [$label, $path, $active]): ?>
                <a href="<?= url($path) ?>" class="filter-pill text-decoration-none <?= $active ? 'active' : '' ?>"><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
            <button type="button" class="filter-pill" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-3 guide-hide-print">
            <div class="table-card-custom p-3 guide-toc">
                <div class="small text-muted text-uppercase fw-semibold mb-2">On this page</div>
                <?php foreach ($guide['sections'] as $i => $section): ?>
                    <a href="#<?= $section['id'] ?>" class="d-flex align-items-center gap-2 py-1 text-decoration-none small">
                        <span class="text-muted" style="min-width: 20px;"><?= $i + 1 ?>.</span>
                        <span><?= htmlspecialchars($section['title']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-9">
            <?php foreach ($guide['sections'] as $i => $section): ?>
                <div class="table-card-custom p-4 mb-3 guide-section" id="<?= $section['id'] ?>">

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="guide-number"><?= $i + 1 ?></span>
                        <h3 class="h5 mb-0"><i class="bi bi-<?= $section['icon'] ?> me-1"></i><?= htmlspecialchars($section['title']) ?></h3>
                    </div>

                    <p class="mb-3"><?= Sobuj_guideText($section['summary']) ?></p>

                    <?php if (!empty($section['steps'])): ?>
                        <ol class="guide-steps">
                            <?php foreach ($section['steps'] as $step): ?>
                                <li><?= Sobuj_guideText($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php foreach ((array) ($section['image'] ?? []) as $image): ?>
                        <a href="<?= $guideImages . $image ?>" target="_blank" class="d-block mb-3">
                            <img src="<?= $guideImages . $image ?>" alt="<?= htmlspecialchars($section['title']) ?>" class="img-fluid guide-shot" loading="lazy">
                        </a>
                    <?php endforeach; ?>

                    <?php if (!empty($section['tips'])): ?>
                        <div class="alert alert-light border small py-2 mb-3">
                            <strong><i class="bi bi-lightbulb me-1"></i>Good to know</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                <?php foreach ($section['tips'] as $tip): ?>
                                    <li><?= Sobuj_guideText($tip) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($section['link'])): ?>
                        <a href="<?= url($section['link'][1]) ?>" class="btn btn-primary btn-sm guide-hide-print">
                            <?= htmlspecialchars($section['link'][0]) ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
