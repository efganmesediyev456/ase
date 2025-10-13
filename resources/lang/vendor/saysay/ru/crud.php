<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Forms
    'save_action_save_and_new' => 'Сохранить и новый элемент',
    'save_action_save_and_edit' => 'Сохранить и отредактировать этот элемент',
    'save_action_save_and_back' => 'Сохранить и вернуться',
    'save_action_changed_notification' => 'Поведение по умолчанию после сохранения изменено.',

    // Create form
    'add' => 'Добавить',
    'back_to_all' => 'Вернуться к списку ',
    'cancel' => 'Отменить',
    'add_a_new' => 'Добавить новый ',

    // Edit form
    'edit' => 'Изменить',
    'save' => 'Сохранить',

    // Revisions
    'revisions' => 'Изменения',
    'no_revisions' => 'Изменений не найдено',
    'created_this' => 'Создатель',
    'changed_the' => 'Изменил',
    'restore_this_value' => 'Восстановить это значение',
    'from' => 'от',
    'to' => 'до',
    'undo' => 'Отменить',
    'revision_restored' => 'Редакция успешно восстановлена',

    // CRUD table view
    'all' => 'Все ',
    'in_the_database' => 'в базе данных',
    'list' => 'Список',
    'actions' => 'Действия',
    'preview' => 'Предпросмотр',
    'delete' => 'Удалить',
    'admin' => 'Admin',
    'details_row' => 'Это строка сведений. Вы можете изменить ее по своему желанию.',
    'details_row_loading_error' => 'При загрузке данных произошла ошибка. Повторите попытку.',

    // Confirmation messages and bubbles
    'delete_confirm' => 'Вы уверены, что хотите удалить этот элемент?',
    'delete_confirmation_title' => 'Элемент удален',
    'delete_confirmation_message' => 'Элемент успешно удален.',
    'delete_confirmation_not_title' => 'НЕ удален.',
    'delete_confirmation_not_message' => "Произошла ошибка. Возможно, элемент не был удален.",
    'delete_confirmation_not_deleted_title' => 'НЕ удален.',
    'delete_confirmation_not_deleted_message' => 'Ничего не произошло. Ваш товар в безопасности.',

    // DataTables translation
    'emptyTable' => 'Данные отсутствуют в таблице',
    'info' => 'Показано _START_ to _END_ of _TOTAL_ entries',
    'infoEmpty' => 'Показаны с 0 по 0 из 0',
    'infoFiltered' => '(filtered from _MAX_ total entries)',
    'infoPostFix' => '',
    'thousands' => ',',
    'lengthMenu' => '_MENU_ количество записей на страницу',
    'loadingRecords' => 'Загружается ...',
    'processing' => 'Обработка ...',
    'search' => 'Поиск: ',
    'zeroRecords' => 'Совпадающих записей не найдено',
    'paginate' => [
        'first' => 'Первый',
        'last' => 'Последний',
        'next' => 'Следующий',
        'previous' => 'Предыдущая',
    ],
    'aria' => [
        'sortAscending' => ': activate to sort column ascending',
        'sortDescending' => ': activate to sort column descending',
    ],

    // global crud - errors
    'unauthorized_access' => 'Несанкционированный доступ - у вас нет необходимых разрешений для просмотра этой страницы.',
    'please_fix' => 'Пожалуйста, исправьте следующие ошибки:',

    // global crud - success / error notification bubbles
    'insert_success' => 'Элемент успешно добавлен.',
    'update_success' => 'Элемент успешно изменен.',

    // CRUD reorder view
    'reorder' => 'Изменение порядка',
    'reorder_text' => 'Для переупорядочивания используйте drag & drop.',
    'reorder_success_title' => 'Готово',
    'reorder_success_message' => 'Сохранено',
    'reorder_error_title' => 'Ошибка',
    'reorder_error_message' => 'Изменения не сохранены',

    // CRUD yes/no
    'yes' => 'Да',
    'no' => 'Нет',

    // Fields
    'browse_uploads' => 'Просмотр загрузок',
    'clear' => 'Очистить',
    'page_link' => 'Ссылка на страницу',
    'page_link_placeholder' => 'http://example.com/your-desired-page',
    'internal_link' => 'Внутренняя ссылка',
    'internal_link_placeholder' => 'Внутренний слизень. Пример: \'admin/page\' (no quotes) для \':url\'',
    'external_link' => 'Внешняя ссылка',
    'choose_file' => 'Выберите файл',

    //Table field
    'table_cant_add' => 'Невозможно добавить новые :entity',
    'table_max_reached' => 'Максимальное количество :max достигнуто',

    // File manager
    'file_manager' => 'Файловый менеджер',
];
