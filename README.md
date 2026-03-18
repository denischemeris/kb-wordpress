# База знаний WordPress

Защищённая база знаний с авторизацией, автодеплоем и защитой от копирования.

## Стек

- **CMS:** WordPress
- **Плагины:** Echo Knowledge Base, Ultimate Member, Members
- **Авторизация:** JWT / WordPress native
- **Деплой:** GitHub Actions → SSH → Server

## Быстрый старт

### Локально

```bash
git clone <repository_url>
cd kb-wordpress
```

### На сервере

Автоматически при push в main ветку

## Структура

```
kb-wordpress/
├── theme/                    # Кастомная тема
│   ├── functions.php         # Роль student, защита, водяные знаки
│   ├── style.css
│   ├── page-knowledge-base.php
│   └── video-proxy.php       # Защищённый прокси для видео
├── .github/
│   └── workflows/
│       └── deploy.yml        # Автодеплой
├── scripts/
│   └── setup-server.sh       # Скрипт настройки сервера
└── README.md
```

## Доступы

- **student** — чтение статей
- **editor_kb** — редактирование статей
- **administrator** — полный доступ

## Сервер

- **IP:** 109.73.201.197
- **Путь:** /srv/www/wordpress
- **Видео:** /srv/www/kb-videos/
