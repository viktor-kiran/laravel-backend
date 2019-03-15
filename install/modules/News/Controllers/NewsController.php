<?php

namespace Backend\News\Controllers;

//\Backend\Category\Controllers\CategoryResourceController
class NewsController extends \Backend\Root\Form\Controllers\ResourceController
{
    public $module = "Backend\Root\News\Models\News";

    public function update($id)
    {
    	//Игнорим текущую запись в валидации урла
        $this->fields['fields']['url']['validate'] .= ','.$id.',id,deleted_at,NULL';
        
        return parent::update($id);
    }
}