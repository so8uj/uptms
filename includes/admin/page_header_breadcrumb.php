<?php

$showBcrumb  = $showBreadcrumb ?? false; 
$breadcrumbs = $breadcrumbs ?? [];       
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= htmlspecialchars($pageHeader) ?></h1>
    </div>

    <?php if ($showBcrumb && !empty($breadcrumbs)): ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php 
                $total = count($breadcrumbs);
                $i = 0;
                foreach ($breadcrumbs as $label => $link): 
                    $i++;
                    $isLast = ($i === $total || empty($link));
                ?>
                    <?php if ($isLast): ?>
                        <li class="breadcrumb-item active text-main" aria-current="page"><?= htmlspecialchars($label) ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= $link ?>" class="text-decoration-none text-muted-green"><?= htmlspecialchars($label) ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>
</div>