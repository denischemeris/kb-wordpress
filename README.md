# База знаний WordPress

Защищённая база знаний с авторизацией, автодеплоем и защитой от копирования.

## 🚀 Статус

✅ Репозиторий создан  
✅ Тема развёрнута на сервере  
✅ Плагины установлены и активированы  
✅ Пользователи созданы  
✅ Страницы созданы  
✅ GitHub Actions настроен  

## Стек

- **CMS:** WordPress
- **Плагины:** Echo Knowledge Base, Ultimate Member, Members
- **Авторизация:** WordPress native + Ultimate Member
- **Деплой:** GitHub Actions → SSH → Server

## 📍 Доступ к системе

### Сервер
- **IP:** http://109.73.201.197
- **Путь:** /srv/www/wordpress
- **Видео:** /srv/www/kb-videos/

### Страницы
- **База знаний:** http://109.73.201.197/knowledge-base/
- **Регистрация:** http://109.73.201.197/register/
- **Вход:** http://109.73.201.197/login/
- **Админка:** http://109.73.201.197/wp-admin/

### Тестовые пользователи

| Роль | Логин | Пароль | Email |
|------|-------|--------|-------|
| Student | test_student | Student123! | student@test.ru |
| Editor | test_editor | Editor123! | editor@test.ru |

## Быстрый старт

### Локально

```bash
git clone https://github.com/denischemeris/kb-wordpress.git
cd kb-wordpress
```

### На сервере

Автоматически при push в main ветку:
```bash
git push
```

## Структура

```
kb-wordpress/
├── functions.php             # Роль student, защита, водяные знаки
├── style.css                 # Стили темы
├── page-knowledge-base.php   # Шаблон страницы БЗ
├── video-proxy.php           # Защищённый прокси для видео
├── index.php                 # Главный шаблон
├── header.php                # Шапка
├── footer.php                # Подвал
├── .github/
│   └── workflows/
│       └── deploy.yml        # Автодеплой
├── scripts/
│   └── setup-server.sh       # Скрипт настройки сервера
├── .gitignore
├── .env.example
└── README.md
```

## Роли

- **student** — чтение статей (доступ к /knowledge-base/)
- **editor_kb** — редактирование статей
- **administrator** — полный доступ

## Защита от копирования

Реализовано:
- ✅ Запрет выделения текста (user-select: none)
- ✅ Запрет правого клика (contextmenu)
- ✅ Запрет горячих клавиш (Ctrl+C, Ctrl+U, Ctrl+S, Ctrl+P, F12)
- ✅ Размытие при потере фокуса (blur event)
- ✅ Водяные знаки с email пользователя
- ✅ Rate limiting для видео (1 запрос/сек)
- ✅ Whitelist файлов для видео
- ✅ Проверка авторизации для доступа к видео

## Следующие шаги

1. Войти в админку: http://109.73.201.197/wp-admin/
2. Активировать плагины (если не активированы)
3. Настроить Ultimate Member (роли после регистрации)
4. Создать категории Echo Knowledge Base
5. Добавить первую статью
6. Загрузить видео в /srv/www/kb-videos/
