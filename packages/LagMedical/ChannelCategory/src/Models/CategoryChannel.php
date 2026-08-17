<?php

namespace LagMedical\ChannelCategory\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryChannel extends Model
{
    protected $table = 'category_channels';

    protected $fillable = [
        'category_id',
        'channel_id',
    ];
}
