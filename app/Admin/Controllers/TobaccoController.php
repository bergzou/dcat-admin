<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Tobacco;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TobaccoController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Tobacco(), function (Grid $grid) {
            $grid->fixColumns(3, -3);
            $grid->withBorder();

            $grid->column('tobacco_name');
            $grid->column('tobacco_area');
            $grid->column('tobacco_url');
            $grid->column('tobacco_app_id');
            $grid->column('tobacco_app_secret');
            $grid->column('tobacco_type');
            $grid->column('tobacco_type', '类型')->display(function ($released) {
                switch ($released){
                    case 1:
                        return  '已排队';
                    case 2:
                        return  '排队中';
                }
            });


            $grid->column('created_at');
            $grid->column('updated_at')->sortable();


            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('tobacco_name');
                $filter->like('tobacco_area');

            });

            $grid->export()->filename('深圳烟草_' . date('Ymd')); // 设置文件名

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Tobacco(), function (Show $show) {

            $show->field('tobacco_name');
            $show->field('tobacco_area');
            $show->field('tobacco_url');
            $show->field('tobacco_token');
            $show->field('tobacco_app_id');
            $show->field('tobacco_app_secret');
            $show->field('tobacco_type')->using([
                1 => '已排队',
                2 => '排队中',
            ]);
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Tobacco(), function (Form $form) {

            $form->display('id');
            $form->text('tobacco_name');
            $form->text('tobacco_area');
            $form->text('tobacco_url');
            $form->text('tobacco_token');
            $form->text('tobacco_app_id');
            $form->text('tobacco_app_secret');


            $form->select('tobacco_type', '类型')
                ->options([
                    1 => '已排队',
                    2 => '排队中',
                ]);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
