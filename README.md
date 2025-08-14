# Yii2 Log Parser

Приложение для парсинга и анализа логов Nginx. Реализовано на Yii2 (Basic).

## Функционал

- Парсинг логов Nginx
- Сохранение данных в SQLite
- Отображение статистики в виде таблиц и графиков
- Фильтрация по дате, ОС, архитектуре
- Сортировка по колонкам

## Технологии

- PHP 8.4
- Yii2 Framework
- SQLite
- Chart.js
- Git

## Установка

1. Клонировать репозиторий:
   git clone https://github.com/dem2183r/yii2-log-parser.git
Установить зависимости:
composer install

Применить миграции:
php yii migrate

Загрузить логи:
php yii parse logs/modimio.access.log.1

Запустить сервер:
php yii serve

Открыть в браузере: http://localhost:8080
