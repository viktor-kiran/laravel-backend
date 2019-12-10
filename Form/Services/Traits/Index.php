<?php

namespace Backend\Root\Form\Services\Traits;
use Helpers;
use Request;

trait Index {

	// !Вввод списка записей
    public function index()
    {
    	$this->resourceCombine('index');

        //Поиск
    	$this->indexSearch();
        //Сортировка
        $this->indexOrder();

    	//Параметры к урлу
		$urlPostfix = "";

		// Получаем все дополнительные параметры.
		foreach ($this->config['url-params'] as $param) {
			$urlPostfix = Helpers::mergeUrlParams($urlPostfix, $param, Request::input($param, ''));
		}

		$this->dataReturn['config']['urlPostfix'] = $urlPostfix;

        //------------------------------Кнопка Создать----------------------------------------

		$this->dataReturn['config']['menu'] = $this->indexListMenu($urlPostfix);

		// -----------------------------Хлебные крошки----------------------------------------

		$this->dataReturn['breadcrumbs'] = $this->indexBreadcrumbs($urlPostfix);

        //-------------------------------Подготавливаем поля----------------------------------
     
        $fields = [];
        $fields_prep = []; // Методы доп обработки values
        $optionFields = []; // Поля имеющие option
     
     	// Меню для элемента списка
        $this->dataReturn['itemMenu'] = $this->indexItemMenu();


        foreach ($this->fields['list'] as $field) {
        
        	// Получаем базовое поле. ВСЕ ПОЛЯ ДОЛЖНЫ БЫТЬ КОРНЕВЫМИ
        	$mainField = ( isset ($this->fields['fields'][ $field['name'] ]) ) ? $this->fields['fields'][ $field['name'] ] : [];

        	// Выставляем метку если на задано
        	if ( !isset($field['label']) && isset($mainField['label']) ) {
        		$field['label'] = $mainField['label'];
        	}

        	$fields[] = $field;
        	$fields_prep[] = $this->initField(array_replace($mainField, $field));
        }

        // Делаем выборку
        $query = $this->post->paginate($this->config['list']['count-items'])->toArray();
        if ($query['last_page'] < $query['current_page']) {
        	$query = $this->post->paginate($this->config['list']['count-items'], ['*'], 'page', $query['last_page'])->toArray();
        }

        // Для пагинации
        $this->dataReturn['items']['currentPage'] = $query['current_page'];
        $this->dataReturn['items']['lastPage'] = $query['last_page'];
        
        //урл страницы списка.
        $this->dataReturn['config']['indexUrl'] = $query['path'];

        //Список полей для вывода
        $this->dataReturn['fields'] = $fields;
        $this->dataReturn['config']['title'] = $this->config['lang']['list-title'];

		$this->dataReturn['items']['data'] = [];

    	// Добавляем поля поиска
    	if ( isset($this->fields['search']) ) {
    		foreach ($this->fields['search'] as $field) {
    			unset($field['fields']); // удаляем ненужные опции
    			$this->dataReturn['search'][] = $field;
    		}
    	}

        // Подготваливаем все поля
        foreach ($query['data'] as $post) {
        	$res = []; //Преобразованные данные
        	
        	$res['_links'] = $this->indexLinks($post, $urlPostfix);

        	foreach ($fields as $key => $field) {
        		
        		$name = $field['name'];

        		if (isset($field['func'])) {
        			$func = $field['func'];
        			$res[$name] = $this->$func($post, $field, $urlPostfix);
        			continue;
        		}

        		// Обработчик полей
        		$res[$name] = 
        			$fields_prep[$key]->list( Helpers::getDataField($post, $name, '') );
        	}
        	$this->dataReturn['items']['data'][] = $res;
        }

        //Получаем шаблон 
    	$templite = (isset($this->config['list']['template'])) ? $this->config['list']['template'] : 'Form::list';

    	//Хук перед выходом
    	$this->resourceCombineAfter ('index');

        if ( Request::ajax() ) return $this->dataReturn;

        return view($templite, [ 'data' => $this->dataReturn ]);
    }
    
    // Выводим хлебные крошки.
    protected function indexBreadcrumbs($url_postfix = '')
    {
    	return false;
    }

    // Главное меню в списке. url_postfix добавочная строка у url адресу.
    protected function indexListMenu($url_postfix = '')
    {

    	$menu = [];
    	
    	// Если нужно создавать запись
    	if ($this->config['list']['create']) {
    		$menu[] = $this->indexListMenuCreateButton($url_postfix);
		}

		// Для ручной сортировки
		if ( isset($this->config['list']['sortable']) && $this->config['list']['sortable']) {
			$menu[] = $this->listSortableButton($url_postfix);
		}

		return $menu;
    }


    // Кнопка создать
	protected function indexListMenuCreateButton($url_postfix)
	{
		return [
			'label' => isset($this->config['lang']['create-title']) 
				? $this->config['lang']['create-title'] : 'Создать',
			'url' => action($this->config['controller-name'].'@create').$url_postfix,
			'btn-type' => 'primary'
		];
	}

    // Получаем пункты меню для строки списка
    protected function indexItemMenu() {
    	if (isset($this->config['list']['item-menu'])) {
    		$res = [];
    		foreach ($this->config['list']['item-menu'] as $item) {
    			// Если есть опция default, то берем значения из дефолтного меню
    			if (isset($item['default'])) {
    				$res[] = $this->config['list']['item-menu-default'][$item['default']];
    			}
    			else $res[] = $item;
    		}
    		return $res;
    	}
    	return $this->config['list']['item-menu-default'];
    }

    // Обрабатываем ссылки в списке
    protected function indexLinks($post, $urlPostfix) {
    	$res = [];

    	if ($this->config['list']['item-edit']) {
    		$res['edit'] = action($this->config['controller-name'].'@edit', $post['id']);
    	}

    	if ($this->config['list']['item-destroy']) {
    		$res['destroy'] = action($this->config['controller-name'].'@destroy', $post['id']);
    	}

    	if ($this->config['list']['item-clone']) {
    		$res['clone'] = action($this->config['controller-name'].'@create')
    			. Helpers::mergeUrlParams($urlPostfix, 'clone', $post['id']);
    	}

    	return $res;
    }



    // Функция для сортировки списка
    protected function indexOrder() 
    {
    	$order = Request::input('order', false);

        // Если выставлена опция ручной сортировки, то сортировка по умолчанию будет по sort_num
        if ( isset($this->config['list']['sortable']) && $this->config['list']['sortable'] )  {
        	$orderField = 'sort_num';
        	$orderType = 'asc'; //от меньшего к большему
        } else {
        	$orderField = $this->config['list']['default-order']['col'];
        	$orderType = $this->config['list']['default-order']['type'];
        }

        if ($order !== false && isset($this->fields['list'][$order]['sortable']) ) {
        	$orderType = Request::input('order-type', 'desc');
        	$orderField = $this->fields['list'][$order]['name'];
        	$this->fields['list'][$order]['sortable'] = $orderType;
        }
        $this->post = $this->post->orderBy($orderField, $orderType);
    }

    //Функция поиска для списка, возвращает true если есть что искать.
    protected function indexSearch() {
    	
    	$searchReq = false;
        
        //Если есть поля для поиска
        if ( isset($this->fields['search']) ) {
      	 	//Перебираем
      	 	foreach ($this->fields['search']  as $key => &$field) {
      	 		//Проверяем на валидность
  	 			if (!isset($field['name']) || !isset($field['fields']) || !is_array($field['fields']) ) continue;
 				  	

      	 		//Копируем данные поля из основных полей
      	 		if (isset($field['field-from'])) {
      	 			// Если поле не существует, удаляем текущее поле из поиска
      	 			if (!isset($this->fields['fields'][$field['field-from']])) {
      	 				unset($this->fields['search'][$key]);
      	 				continue;
      	 			}

      	 			$field = array_replace_recursive($this->fields['fields'][$field['field-from']], $field);
      	 			unset($field['field-from']);
      	 		}

 				$field['value'] = Request::input($field['name'], '');

      	 		//Добавляем пустой элемент в начало.
      	 		if (isset($field['options-empty']) && isset($field['options']) && is_array($field['options'])){
      	 			array_unshift($field['options'], ['value' => '', 'label' => $field['options-empty']]);
      	 		}

				if ( $field['value'] == '' ) continue;

 				$req = $field['value'];

      	 		// Проверяем значения и добавляем дополнительные опции из options
      	 		if ($field['type'] == 'select') {

      	 			$option = Helpers::searchArray($field['options'], 'value', $field['value']);

      	 			// Если нет значния
      	 			if (!$option) abort(403, 'indexSearch: select value not found '.$field['value']);

	      	 		// подменяем элемент нельзя передать в строке запроса. например null
	      	 		if ( array_key_exists('change-value', $option) ) {
	      	 			$req = $option['change-value'];
	      	 			unset($option['change-value']);
	      	 		}

	      	 		// Получаем нужные опции
      	 			foreach (['type-comparison', 'exact-match'] as $key) {
      	 				if (isset($option[$key])) {
      	 					$field[$key] = $option[$key];
      	 					unset($option[$key]);
      	 				}
      	 			}

      	 			if (!isset($field['exact-match']))$field['exact-match'] = true;
      	 			if (!isset($field['type-comparison']))$field['type-comparison'] = '=';
      	 		}

      	 		// Тип выборки, по умолчанию like
      	 		$typeComparison = 'like';
      	 		if (isset($field['type-comparison'])) {
      	 			$typeComparison = $field['type-comparison'];
      	 			unset($field['type-comparison']);
      	 		}

			  	// По умолчанию добавляем %% для запроса
			  	if (isset($field['exact-match'])) unset($field['exact-match']);
			  	else $req = '%'.$req.'%';

				//Выборка по группе полей, если в каком то поле есть то данные выведутся
		 		$this->post = $this->post->where(function ($query)
		 		use (&$field, $req, $typeComparison, &$searchReq) 
		 		{
					$first = true;

					foreach ($field['fields'] as $column) {
						// Выборка для релатед полей
						$func = ($first) ? 'where' : 'orWhere';
						
						$searchReq[$column] = $req;

						if (isset($field['field-save']) && $field['field-save'] == 'relation'){
							$func .= 'Has';
							$query = $query->$func('relationFields', function ($query) 
							use ($column, $req, $typeComparison, $first) 
							{
								$query->where('value', $typeComparison, $req)->where('field_name', $column);
							});
						} else $query = $query->$func($column, $typeComparison, $req);

						$first = false;
					}
				});
	        }
    	}

    	return $searchReq;
    }
}