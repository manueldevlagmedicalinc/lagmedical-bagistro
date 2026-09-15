<?php

return [
    [
        'key' => 'cms.pro',
        'name' => 'CMS Pro',
        'route' => 'admin.cms.index',
        'sort' => 4,
    ], [
        'key' => 'cms.pro.edit',
        'name' => 'Editar contenido',
        'route' => [
            'admin.cms.pro.edit',
            'admin.cms.pro.update',
            'admin.cms.pro.preview',
            'admin.cms.pro.revisions.restore',
        ],
        'sort' => 1,
    ], [
        'key' => 'cms.pro.publish',
        'name' => 'Publicar contenido',
        'route' => 'admin.cms.pro.publish',
        'sort' => 2,
    ], [
        'key' => 'cms.pro.custom_code',
        'name' => 'Código personalizado',
        'route' => [],
        'sort' => 4,
    ], [
        'key' => 'cms.pro.media',
        'name' => 'Administrar medios',
        'route' => [
            'admin.cms.pro.media.index',
            'admin.cms.pro.media.store',
            'admin.cms.pro.media.import',
        ],
        'sort' => 3,
    ], [
        'key' => 'cms.pro.media.delete',
        'name' => 'Eliminar medios',
        'route' => 'admin.cms.pro.media.delete',
        'sort' => 1,
    ],
];
