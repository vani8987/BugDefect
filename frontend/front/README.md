# BugDefect Frontend

Frontend - Vue 3 SPA для BugDefect. Приложение показывает рабочую зону, доски, участников, приглашения и уведомления.

## Стек

- Vue 3
- TypeScript
- Vite
- Pinia
- Vue Router
- Axios
- Sass
- vue-icons-plus

## Запуск через Docker

Из корня проекта:

```bash
docker compose up --build
```

Frontend будет доступен на `http://localhost:5173`.

## Локальный запуск

```bash
npm install
npm run dev
```

По умолчанию API вызывается по адресу `http://localhost:8000/api`. Базовый URL находится в `src/utils/api.ts`.

## Скрипты

```bash
npm run dev          # dev server Vite
npm run build        # type-check и production build
npm run build-only   # только Vite build
npm run type-check   # проверка TypeScript/Vue
npm run preview      # preview production build
npm run format       # форматирование src через Prettier
```

## Структура

```
src/components/      переиспользуемые компоненты
src/components/ui/   базовые UI-компоненты
src/router/          маршруты Vue Router
src/stores/          Pinia stores
src/Ts/              TypeScript-типы
src/utils/           API client и утилиты
src/views/           страницы приложения
src/assets/          глобальные стили и ассеты
```

## Основные страницы

- `/login` - вход в аккаунт.
- `/register` - регистрация.
- `/workspace` - рабочая зона с досками.
- `/boards/:boardId` - страница конкретной доски.

## Доски и участники

Страница доски показывает описание, роль текущего пользователя, статистику, список участников и рабочую область будущей доски дефектов.

Для администратора доступны действия:

- открыть модалку добавления участника;
- удалить доску через `DELETE /api/boards/{boardId}`;
- удалить участника из списка через `DELETE /api/boards/{boardId}/members/{userId}`;
- открыть модалку создания нового статуса.

Логика досок находится в `src/stores/boardStore.ts`: загрузка списка досок, загрузка одной доски, загрузка участников, удаление доски и удаление участника.

## Рабочая доска

Каркас рабочей доски разнесён на компоненты:

- `src/components/board-workspace/board-panel/BoardPanel.vue` - общая панель доски.
- `src/components/board-workspace/status-column/StatusColumn.vue` - колонка статуса.
- `src/components/board-workspace/defect-card/DefectCard.vue` - карточка дефекта.
- `src/Ts/status.ts` - типы статусов, позиций и будущих карточек.

Статусы вынесены в `src/stores/statusesStore.ts`. Если статусы ещё не созданы, `BoardPanel` показывает empty-состояние с подсказкой и кнопкой добавления статуса для администратора.

Для статусов подключена логика:

- создание нового статуса через модальное окно;
- загрузка статусов доски с backend;
- `dragstart` запоминает id перетаскиваемого статуса;
- `drop` передаёт id статуса, на который был сброшен элемент;
- порядок статусов обновляется на фронтенде с пересчётом `position`;
- новый порядок сохраняется через `PATCH /api/status/{boardId}/position`.

Карточки дефектов пока остаются frontend-заглушкой через `items`, позже они будут приходить вместе со статусами или отдельным API дефектов.

## Авторизация и API

Axios настроен с `withCredentials: true`, поэтому frontend отправляет session cookie вместе с запросами. Защищённые маршруты проверяются через `authStore.me()` в router guard.

Если backend недоступен или запрос авторизации ещё выполняется, корневой `App.vue` показывает глобальный loader.

## Уведомления

Уведомления загружаются через `NotificationStore`.

Меню уведомлений поддерживает:

- показ количества непрочитанных уведомлений в header;
- отметку всех уведомлений прочитанными после закрытия меню через `PATCH /api/notifications/read`;
- принятие приглашения через `POST /api/boards/{boardId}/invite/accept`;
- отклонение приглашения через `POST /api/boards/{boardId}/invite/reject`;
- скрытие кнопок у обработанного приглашения;
- показ итоговой строки `Вы приняли приглашение` или `Вы отклонили приглашение`;
- обновление списка досок после принятия приглашения.

Статус приглашения приходит в `notification.data.status` и может быть `pending`, `accepted` или `declined`.
