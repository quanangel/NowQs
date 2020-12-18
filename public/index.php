<?php
declare (strict_types = 1);
namespace nowqs;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// $systemPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'nowqs';
// var_dump();
// require_file(($systemPath));


$http = (new System())->http;
$http->run();


// $allRequire = get_included_files();
// var_dump($allRequire);


// function require_file($path = '') {
//     // var_dump($path);
//     foreach (scandir($path) as $vo) {
//         if ('.' == $vo || '..' == $vo) continue;
//         if (is_file($path . DIRECTORY_SEPARATOR . $vo)) {
//             require_once $path . DIRECTORY_SEPARATOR . $vo;
//         } else if (is_dir($path . DIRECTORY_SEPARATOR . $vo)) {
//             require_file($path . DIRECTORY_SEPARATOR . $vo);
//         }
//     }
//     return;
// }