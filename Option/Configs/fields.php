<?php
	return [
        'list' => [
        	[ 'name' => 'name', 'icon' => 'file', 'link' => 'edit'],
        ],

		'fields' => [
	        'name' => [
	            'type' => 'text', 
	            'name' => 'name', 
	            'label' => 'Ключ',
	            'validate' => 'required|not_in:general|unique:options,name',
	        ],
	        'type' => [	
	            'type' => 'select', 
	            'name' => 'type', 
	            'label' => 'Тип',
	            'options' => [
	            	['label' => 'Текст', 'value' => 'text'],
	            	['label' => 'Редактор', 'value' => 'mce'],
	            	['label' => 'Меню', 'value' => 'menu'],
	            	['label' => 'Галерея', 'value' => 'gallery'],
	            ],
	            'field-save' => 'array',
	        ],

			'gallery' => [		
	            'type' => 'gallery', 
	            'name' => 'gallery', 
	            'label' => 'Галерея',
	            'field-save' => 'array',
	     		'show' => [
					['field' => 'type', 'value' => 'gallery', 'type' => '=='],
				],
			],

			'mce' => [	
		        'type' => 'mce', 
		        'name' => 'mce', 
		        'label' => 'Текст',
		        'height' => 300,
		        'upload' => true,
		        'field-save' => 'array',
	     		'show' => [
					['field' => 'type', 'value' => 'mce', 'type' => '=='],
				],
	        ],

			'menu' => [	
		        'type' => 'mce', 
		        'name' => 'menu', 
		        'label' => 'Текст',
		        'height' => 300,
		        'field-save' => 'array',
	     		'show' => [
					['field' => 'type', 'value' => 'menu', 'type' => '=='],
				],
	        ],

			'text' => [	
		        'type' => 'textarea', 
		        'name' => 'text', 
		        'label' => 'Значение',
		        'attr' => [ 'rows' => '15' ],
		        'field-save' => 'array',
	     		'show' => [
					['field' => 'type', 'value' => 'text', 'type' => '=='],
				],
	        ],

	        'autoload' => [
	            'type' => 'radio', 
	            'name' => 'autoload', 
	            'label' => 'Автозагрузка',
	            'desc' => 'Если эта опция используется постоянно, то укажите Да, если только на некоторых страницах тогда Нет',
	            'value' => '1',
	            'options' => [
	            	['value' => '1', 'label' => 'Да'],
	            	['value' => '0', 'label' => 'Нет'],
	            ],
	        ],
	    ],

		'edit' => [
			'default' => [
				'label' => 'Основные',
				'name' => 'main',
				'fields' => [ 'name', 'type', 'gallery', 'mce', 'text', 'menu', 'autoload' ],
			],
		],
	];