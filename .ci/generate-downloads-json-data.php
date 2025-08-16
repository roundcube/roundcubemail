#!/usr/bin/env php
<?php

if (!isset($argv[1])) {
    echo 'Usage: ' . basename(__FILE__) . " VERSION\n";
    exit(1);
}

$version = $argv[1];

function human_file_size($byte)
{
    $factor = floor((strlen($byte) - 1) / 3);
    if ($factor > 0) {
        $sz = 'KMGT';
    }
    return sprintf('%.1f', $byte / 1024 ** $factor) . ' ' . @$sz[$factor - 1] . 'B';
}

function generate_data($version, $package_name, $filename)
{
    $sum = hash_file('sha256', $filename);
    $size = human_file_size(filesize($filename));
    return [
        'package' => $package_name,
        'url' => "https://github.com/roundcube/roundcubemail/releases/download/{$version}/{$filename}",
        'size' => $size,
        'checksum' => $sum,
    ];
}

echo json_encode([
    generate_data($version, 'Dependent', "roundcubemail-{$version}.tar.gz"),
    generate_data($version, 'Complete', "roundcubemail-{$version}-complete.tar.gz"),
    generate_data($version, 'Framework', "roundcube-framework-{$version}.tar.gz"),
], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n";
