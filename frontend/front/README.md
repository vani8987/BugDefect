# BugDefect Frontend

Frontend - Vue 3 SPA для BugDefect. Приложение показывает рабочую зону, доски, участников, статусы, дефекты, приглашения и уведомления.

## Стек

- Vue 3
- TypeScript
- Vite
- Pinia
- Vue Router
- Axios
- Sass
- `vue-icons-plus`

## Запуск Через Docker

Из корня проекта:

```bash
docker compose up --build
```

Frontend доступен на `http://localhost:5173`.

## Локальный Запуск

```bash
npm install
npm run dev
```

По умолчанию API выбирается в `src/utils/api.ts`:

- dev: `http://localhost:8000/api`
- production: `/api`

Адрес можно переопределить через `VITE_API_BASE_URL`.

## Скрипты

```bash
npm run dev
npm run build
npm run build-only
npm run type-check
npm run preview
npm run format
```

## Структура

```text
src/views/           страницы приложения
src/components/      компоненты предметной области
src/components/ui/   базовые UI-компоненты
src/stores/          Pinia stores
src/router/          Vue Router
src/Ts/              TypeScript-типы
src/utils/           API client и утилиты
src/assets/          стили и ассеты
```

## Основные Страницы

- `/login` - вход.
- `/register` - регистрация.
- `/workspace` - список досок пользователя.
- `/boards/:boardId` - доска со статусами и дефектами.

## API И Авторизация

Axios настроен с `withCredentials: true`, поэтому session cookie отправляется вместе с запросами. Router guard проверяет текущего пользователя через `authStore.me()`.

## Доска

Страница доски показывает:

- описание доски;
- роль текущего пользователя;
- статистику;
- участников;
- статусы;
- дефекты внутри статусов.

Администратор доски может удалять доску, управлять участниками, приглашать пользователей, создавать и удалять статусы, создавать, переносить и удалять дефекты.

Удаление дефекта идет через типизированное событие `DeleteDefectPayload`. Компонент карточки дефекта не вызывает backend напрямую: `DefectCard` отправляет событие наверх в `StatusColumn`, дальше в `BoardPanel`, а запрос к API выполняется на странице `BoardPage` через `defectsStore.deleteDefect()`. После успешного ответа дефект удаляется из локального массива статусов через `statusesStore.deleteDefectInArray()`.

## Stores

- `authStore.ts` - авторизация и текущий пользователь.
- `boardStore.ts` - доски, текущая доска, участники.
- `statusesStore.ts` - статусы, порядок колонок и локальное удаление дефекта из нужной колонки после успешного API-запроса.
- `defectsStore.ts` - создание, перенос и удаление дефектов через backend API.
- `InviteStore.ts` - приглашения.
- `NotificationStore.ts` - уведомления и обработка приглашений.

## Проверка

```bash
npm run type-check
npm run build
npm run preview
```
