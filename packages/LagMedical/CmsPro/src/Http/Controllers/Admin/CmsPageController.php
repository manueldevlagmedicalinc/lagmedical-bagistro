<?php

namespace LagMedical\CmsPro\Http\Controllers\Admin;

use LagMedical\CmsPro\DataGrids\CmsPageDataGrid;
use Webkul\Admin\Http\Controllers\CMS\PageController as NativePageController;
use Webkul\CMS\Repositories\PageRepository;

class CmsPageController extends NativePageController
{
    public function __construct(PageRepository $pageRepository)
    {
        parent::__construct($pageRepository);
    }

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CmsPageDataGrid::class)->process();
        }

        return view('admin::cms.index');
    }
}
