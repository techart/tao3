<?php
$path = base_path('routes/api.php');
if (is_file($path)) {
    include($path);
}

