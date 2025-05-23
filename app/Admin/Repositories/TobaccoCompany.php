<?php

namespace App\Admin\Repositories;

use App\Models\TobaccoCompany as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TobaccoCompany extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
