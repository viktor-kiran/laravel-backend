
import Vue from 'vue'

// Vue.component('fields-list', require('./components/fields-list.vue'));

// Компоненты для фиелдсов
Vue.component('print-field', require('./components/fields/field.vue').default);


Vue.component('fields-list', require('./components/form/fields.vue').default);


//Модальное окно
Vue.component('modal', require('./components/modal.vue').default);

//Полный вывод формы редактирование
Vue.component('edit-html-form', require('./components/form/edit-html.vue').default);
//Полный вывод формы редактирование
Vue.component('show-html-form', require('./components/form/show-html.vue').default);
//
Vue.component('list-html-posts', require('./components/list/list-html.vue').default);

