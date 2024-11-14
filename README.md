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
make grafrun
```

Урлы:

- Сайт: http://localhost:7777
- Админка: http://localhost:7777/admin
- Документация API: http://localhost:7777/api/v1/doc
- Graphite: http://localhost:8000 (stats_counts)
- Grafana: http://localhost:3000
