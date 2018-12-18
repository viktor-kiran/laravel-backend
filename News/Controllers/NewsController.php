<?php

namespace Backend\Root\News\Controllers;

use Backend\Root\News\Models\News;
use Cache;
use Content;

class NewsController extends \Backend\Root\Form\Controllers\ResourceController
{
    use \Backend\Root\Category\Services\Traits\Category;

    function __construct(News $post)
    {
       parent::init($post);
    }

    public function update($id)
    {
    	//Игнорим текущую запись в валидации
        $this->fields['fields']['url']['validate'] .= ','.$id.',id,deleted_at,NULL';
        
        return parent::update($id);
    }
}