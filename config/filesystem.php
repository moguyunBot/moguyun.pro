<?php

return [
    // 默认磁盘
    'default' => 'local',
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRootPath() . 'public/storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        'upgrade'  => [
            'type' => 'local',
            'root' => app()->getRootPath() . 'upgrade',
        ],
        // 更多的磁盘配置信息
    ],
];
