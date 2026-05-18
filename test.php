<?php

$hash = '$2y$12$uWyfQMg/WlgRUBLu3Umrzez.E/FRDPgeR7gpmVqRKp0Syt5zuu/zu';

var_dump(password_verify('admin@123', $hash));