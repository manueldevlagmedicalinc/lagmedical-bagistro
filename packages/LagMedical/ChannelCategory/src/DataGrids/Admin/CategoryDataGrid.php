<?php

namespace LagMedical\ChannelCategory\DataGrids\Admin;

use Illuminate\Support\Facades\DB;

class CategoryDataGrid extends \Webkul\Admin\DataGrids\Catalog\CategoryDataGrid
{
    public function prepareQueryBuilder()
    {
        $queryBuilder = parent::prepareQueryBuilder();

        $prefix = DB::getTablePrefix();

        $channelNamesSubQuery = DB::table('category_channels as cc')
            ->selectRaw(
                "{$prefix}cc.category_id, GROUP_CONCAT(COALESCE({$prefix}ct.name, {$prefix}c.code) ORDER BY {$prefix}c.code SEPARATOR ', ') as assigned_channels"
            )
            ->join('channels as c', 'cc.channel_id', '=', 'c.id')
            ->leftJoin('channel_translations as ct', function ($join) {
                $join->on('c.id', '=', 'ct.channel_id')
                    ->where('ct.locale', '=', app()->getLocale());
            })
            ->groupBy('cc.category_id');

        $queryBuilder
            ->addSelect('channel_names.assigned_channels')
            ->leftJoinSub($channelNamesSubQuery, 'channel_names', function ($join) {
                $join->on('categories.id', '=', 'channel_names.category_id');
            });

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        parent::prepareColumns();

        $this->addColumn([
            'index' => 'assigned_channels',
            'label' => 'Channels',
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                if (empty($row->assigned_channels)) {
                    return '<span class="badge badge-md badge-success">All Channels</span>';
                }

                return collect(explode(',', $row->assigned_channels))
                    ->map(fn ($channel) => '<span class="badge badge-md badge-info">'.e(trim($channel)).'</span>')
                    ->implode(' ');
            },
        ]);
    }
}
