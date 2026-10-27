<?php
/*
 * The left menu (col-2) of a section, and the start of the content column.
 * The caller sets $sectionNav = [[label, page, active (true/false), count key or null], ...] first.
 * section_nav_close.php closes the two columns again. Put the pair around the body of the page.
 */
?>

<div class="row g-3">
    <div class="col-12 col-xl-2">
        <nav class="section-nav">
            <?php foreach ($sectionNav as [$label, $page, $active, $count]): ?>
                <a href="<?= url($page) ?>" class="section-nav-link <?= $active ? 'active' : '' ?>">
                    <span><?= htmlspecialchars($label) ?></span>
                    <?php if ($count): ?>
                        <span class="sidebar-menu-badge" data-menu-count="<?= $count ?>" hidden></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="col-12 col-xl-10">
