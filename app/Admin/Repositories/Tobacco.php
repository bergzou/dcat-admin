<?php

namespace App\Admin\Repositories;

use App\Models\Tobacco as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Tobacco extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
