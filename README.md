# Менеджер кофейни

![Static Badge](https://img.shields.io/badge/development-ongoing-blue)

## Требования

- Git
- Make
- [Symfony 7.1](https://symfony.com/doc/current/setup.html)

## Запуск проекта

```bash
git clone https://github.com/kooznitsa/cafe_manager.git
cd cafe_manager
cp .env.sample .env
// Отредактировать .env
cp auth.json.sample auth.json
// Отредактировать auth.json
make run
```

Урлы:

- Сайт: http://localhost:7777
- Админка: http://localhost:7777/admin
- Документация API: http://localhost:7777/api/v1/doc
- Graphite: http://localhost:8000 (stats_counts)
- Grafana: http://localhost:3000

## Функционал

Реализованы:

- Сайт кафе с меню, корзиной, формами регистрации/логина, редактированием профиля.
- API.
- Документация OpenAPI.
- Админка (EasyAdmin).
- Бизнес-логика:
  - управление меню: CRUD-операции с блюдами;
  - обработка заказов: добавление товаров в корзину, изменение статуса заказа, отмена заказа, методы для оплаты и доставки;
  - контроль запасов: обновление информации о запасах продукта после закупки или создания/обновления заказа;
  - обновление доступности товаров;
  - статистика и дашборд в админке с выводом информации об оплаченных заказах.
- Аутентификация на сайте с помощью логина и пароля, с помощью JWT-токена для API.
- Кэширование ресурсозатратных операций с помощью Memecached и Redis.
- Логирование и мониторинг с помощью Graphite и Grafana.
