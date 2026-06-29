# BugDefect Frontend

Frontend — Vue 3 SPA для BugDefect. Приложение показывает рабочую зону, доски, участников, статусы, дефекты, приглашения и уведомления.

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

```text
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

- `/login` — вход в аккаунт.
- `/register` — регистрация.
- `/workspace` — рабочая зона со списком досок.
- `/boards/:boardId` — страница конкретной доски.

## Авторизация и API

Axios настроен с `withCredentials: true`, поэтому frontend отправляет session cookie вместе с запросами. Защищённые маршруты проверяются через `authStore.me()` в router guard.

Если backend недоступен или запрос авторизации ещё выполняется, корневой `App.vue` показывает глобальный loader.

## Доски и участники

Страница доски показывает описание, роль текущего пользователя, статистику, список участников и рабочую область дефектов.

Для администратора доступны действия:

- открыть модалку добавления участника;
- удалить доску через `DELETE /api/boards/{boardId}`;
- удалить участника из списка через `DELETE /api/boards/{boardId}/members/{userId}`;
- открыть модалку создания нового статуса;
- создать дефект;
- удалить пустой статус;
- менять порядок статусов и переносить дефекты между статусами.

Логика досок находится в `src/stores/boardStore.ts`.

## Рабочая доска

Каркас рабочей доски разнесён на компоненты:

- `src/components/board-workspace/board-panel/BoardPanel.vue` — общая панель доски и проброс drag/drop событий;
- `src/components/board-workspace/status-column/StatusColumn.vue` — колонка статуса;
- `src/components/board-workspace/defect-card/DefectCard.vue` — карточка дефекта;
- `src/Ts/status.ts` — типы статусов, позиций и карточек дефектов.

Статусы вынесены в `src/stores/statusesStore.ts`. Если статусы ещё не созданы, `BoardPanel` показывает empty-состояние с подсказкой и кнопкой добавления статуса для администратора.

Для статусов подключена логика:

- создание нового статуса через модальное окно;
- загрузка статусов доски с backend;
- удаление пустого статуса с состоянием `Удаление статуса...`;
- `dragstart` запоминает id перетаскиваемого статуса;
- `drop` передаёт id статуса, на который был сброшен элемент;
- порядок статусов обновляется на фронтенде с пересчётом `position`;
- новый порядок сохраняется через `PATCH /api/status/{boardId}/position`;
- при ошибке сохранения порядок возвращается к предыдущему состоянию.

## Дефекты

Форма создания дефекта содержит:

- название;
- необязательное описание;
- выбор начального статуса;
- выбор исполнителя из участников доски.

Дефекты создаются через `src/stores/defectsStore.ts`, сохраняются на backend и возвращаются внутри `items` каждого статуса.

Карточки дефектов показывают:

- код `DEF-{id}`;
- название;
- описание;
- исполнителя;
- автора;
- позицию внутри статуса.

Для дефектов подключена логика:

- `dragstart` запоминает `defectId` и исходный `statusId`;
- `drop` получает целевой `statusId`;
- карточка удаляется из старого статуса и добавляется в новый;
- пересчитываются позиции в старой и новой колонке;
- перенос сохраняется через `PATCH /api/boards/{boardId}/defects/{defectId}/move`;
- при ошибке backend frontend возвращает предыдущие данные.

## Stores

- `src/stores/authStore.ts` — авторизация и текущий пользователь.
- `src/stores/boardStore.ts` — доски, текущая доска, участники и действия администратора.
- `src/stores/statusesStore.ts` — статусы, порядок статусов, удаление статуса.
- `src/stores/defectsStore.ts` — создание и перенос дефектов.
- `src/stores/InviteStore.ts` — приглашения.
- `src/stores/NotificationStore.ts` — уведомления и обработка приглашений.

## Уведомления

Меню уведомлений поддерживает:

- показ количества непрочитанных уведомлений в header;
- отметку всех уведомлений прочитанными после закрытия меню через `PATCH /api/notifications/read`;
- принятие приглашения через `POST /api/boards/{boardId}/invite/accept`;
- отклонение приглашения через `POST /api/boards/{boardId}/invite/reject`;
- скрытие кнопок у обработанного приглашения;
- показ итоговой строки `Вы приняли приглашение` или `Вы отклонили приглашение`;
- обновление списка досок после принятия приглашения.

Статус приглашения приходит в `notification.data.status` и может быть `pending`, `accepted` или `declined`.

## Проверка перед деплоем

```bash
npm install
npm run type-check
npm run build
npm run preview
```

Перед production-сборкой нужно проверить:

- корректный backend API URL в `src/utils/api.ts` или будущей env-настройке;
- работу cookie/sessions между доменами frontend и backend;
- CORS на backend;
- отсутствие dev-only логики в UI;
- прохождение `npm run build` без ошибок.

После `npm run build` production-файлы будут в `dist/`.
