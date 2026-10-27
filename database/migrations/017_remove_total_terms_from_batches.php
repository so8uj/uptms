<?php

// The program length is not kept any more. A batch is only: name, code, department, shift and admission session.
return function (PDO $db): void {
    $db->exec("ALTER TABLE batches DROP COLUMN total_terms");
};
