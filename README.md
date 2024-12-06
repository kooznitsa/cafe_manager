# Менеджер кофейни

![Static Badge](https://img.shields.io/badge/development-ongoing-blue)

## Требования

- Git
- Make
- [Symfony 7.1](https://symfony.com/doc/current/setup.html)

## Диаграмма базы данных

![Диаграмма базы данных](https://raw.githubusercontent.com/kooznitsa/cafe_manager/refs/heads/main/sql_diagram.png)

## Запуск проекта

```bash
git clone https://github.com/kooznitsa/cafe_manager.git
cd cafe_manager
cp .env.sample .env
// Отредактировать .env
cp auth.json.sample auth.json
// Отредактировать auth.json
make setup
```

Урлы:

- Сайт: http://localhost:7777
- Админка: http://localhost:7777/admin
- Документация API: http://localhost:7777/api/v1/doc
- Graphite: http://localhost:8000 (stats_counts)
- Grafana: http://localhost:3000
- Kibana: http://localhost:5601

Данные для входа на сайт:

- Логин: cuckoo@gmail.com
- Пароль: TSshark1957work$

## Функционал

Реализованы:

- Сайт кафе с меню, корзиной, формами регистрации/логина, редактированием профиля. Бизнес-логика:
  - управление меню: CRUD-операции с блюдами;
  - обработка заказов: добавление товаров в корзину, изменение статуса заказа, отмена заказа, методы для оплаты и доставки;
  - контроль запасов: обновление информации о запасах продукта после закупки или создания/обновления заказа;
  - обновление доступности товаров.
- Документация OpenAPI (Nelmio).
- Админка (EasyAdmin):
  - фильтры по полям;
  - выгрузка заказов в CSV;
  - дашборд с графиком об оплаченных заказах.
- Аутентификация на сайте с помощью логина и пароля, с помощью JWT-токена для API.
- Кэширование ресурсозатратных операций с помощью Memcached и Redis.
- Логирование (Elasticsearch и Kibana для визуализации логов).
- Мониторинг с помощью Graphite и Grafana.
- Тесты (Codeception, PHPUnit).
