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
