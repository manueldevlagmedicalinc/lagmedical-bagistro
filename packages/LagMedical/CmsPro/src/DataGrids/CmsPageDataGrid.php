<?php

namespace LagMedical\CmsPro\DataGrids;

use LagMedical\CmsPro\Services\CmsProService;
use Webkul\Admin\DataGrids\CMS\CMSPageDataGrid as NativeDataGrid;

class CmsPageDataGrid extends NativeDataGrid
{
    public function __construct(protected CmsProService $cmsPro) {}

    public function prepareActions()
    {
        parent::prepareActions();

        if (bouncer()->hasPermission('cms.pro.edit')) {
            $this->addAction([
                'index' => 'cms_pro',
                'icon' => 'cms-pro-action',
                'title' => 'CMS Pro',
                'method' => 'GET',
                'url' => fn ($row) => route('admin.cms.pro.edit', [
                    'pageId' => $row->id,
                    'locale' => core()->getRequestedLocaleCode(),
                ]),
            ]);
        }
    }

    protected function formatRecords($records): mixed
    {
        $records = parent::formatRecords($records);

        foreach ($records as $record) {
            if ($this->cmsPro->pageType((int) $record->id) !== 'cms_pro') {
                $record->actions = collect($record->actions)
                    ->reject(fn ($action) => $action['index'] === 'cms_pro')
                    ->values()
                    ->all();
            }
        }

        return $records;
    }
}
