<?php

// The signing panel per research was dropped: the thesis committee itself signs (any 2 of its members).
return function (PDO $db): void {
    $db->exec("DROP TABLE IF EXISTS research_panel");
};
