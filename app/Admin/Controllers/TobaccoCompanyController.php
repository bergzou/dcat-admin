<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TobaccoCompany;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TobaccoCompanyController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TobaccoCompany(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('company_name')->display(function (){
                return '<a href="https://www.tianyancha.com/search?key='.$this->company_name.'">'.$this->company_name.'</a>';
            });


            $grid->column('company_area');
            $grid->column('company_person');
            $grid->column('company_phone');
            $grid->column('company_level');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('company_name');
                $filter->equal('company_area');
                $filter->equal('company_person');
                $filter->equal('company_phone');
                $filter->equal('company_level');

            });

            $grid->export()->filename('深圳烟草公司_' . date('Ymd')); // 设置文件名
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
        return Show::make($id, new TobaccoCompany(), function (Show $show) {
            $show->field('company_name');
            $show->field('company_area');
            $show->field('company_person');
            $show->field('company_phone');
            $show->field('company_phone_json');
            $show->field('company_level');
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
        return Form::make(new TobaccoCompany(), function (Form $form) {
            $form->text('company_name');
            $form->text('company_area');
            $form->text('company_person');
            $form->text('company_phone');
            $form->text('company_phone_json');
            $form->text('company_level');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
