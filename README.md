# Прикреплённые файлы к товару — плагин для Shop-Script

[English version](README_en.md)

Плагин позволяет прикреплять к каждому товару любое количество файлов с описаниями. Список файлов отображается покупателям на карточке товара.

Типичные сценарии использования: инструкции и руководства пользователя, драйверы и прошивки, документация и сертификаты, дополнительные материалы к товару.

## Функции

**Управление файлами в бэкенде**

- Загрузка файлов через drag-and-drop или стандартный диалог выбора
- Прогресс-бар при загрузке
- Произвольное описание для каждого файла
- Поддержка нового и устаревшего редакторов товара
- Ручная сортировка файлов перетаскиванием (drag-and-drop)
- Прикрепление уже загруженного файла к нескольким товарам без повторной загрузки

**Отображение на сайте**

- Блок файлов выводится через один из хуков: `frontend_product.block` или `frontend_product.block_aux`
- Альтернативно — вставка в произвольное место шаблона через встроенный хелпер
- **Шаблон из темы дизайна (рекомендуется):** создайте файл `plugin.syrattach.attachments.html` в папке активной темы дизайна — плагин найдёт и применит его автоматически
- Если файл темы отсутствует, используется встроенный дефолтный шаблон
- В шаблоне доступна переменная `$attachments` — массив файлов с полями: `id`, `name`, `ext`, `description`, `size`, `url`

**Импорт через CSV**

- При импорте товаров из CSV файлы можно прикреплять сразу к нужным товарам
- Для этого предварительно загрузите файлы в `wa-data/public/site/syrattach` и укажите имена файлов в CSV

## Для разработчиков

### Структура базы данных

Плагин использует две таблицы.

**`shop_syrattach_files`** — физические файлы

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | int | PK |
| `product_id` | int\|NULL | `NULL` — новый стиль (центральное хранилище); не `NULL` — старый стиль (файл внутри директории товара, legacy) |
| `name` | varchar(255) | Имя файла |
| `ext` | varchar(255) | Расширение |
| `upload_datetime` | datetime | Дата загрузки |
| `size` | int | Размер в байтах |
| `description` | text | *Устарело* — перенесено в `shop_syrattach_links` |
| `sort` | int | *Устарело* — перенесено в `shop_syrattach_links` |

**`shop_syrattach_links`** — привязки файлов к сущностям

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | int | PK — **это и есть «ID вложения»** в API и JS |
| `file_id` | int | FK → `shop_syrattach_files.id` |
| `entity_type` | varchar(64) | Тип сущности (`'product'`, расширяемо) |
| `entity_id` | int | ID сущности |
| `sort` | int | Порядок сортировки в рамках данной сущности |
| `description` | text | Описание файла для данного вложения |

Один файл можно прикрепить к нескольким товарам без дублирования на диске.

### Расположение файлов

**Новый стиль** (`product_id IS NULL`) — центральное хранилище:

```
wa-data/public/shop/attachments/files/{file_id}/{filename}
```

**Старый стиль** (`product_id IS NOT NULL`, legacy) — внутри директории товара:

```
wa-data/public/shop/products/{folder}/{product_id}/attachments/{filename}
```

где `{folder}` — подпапка, вычисляемая через `shopProduct::getFolder($product_id)`. Старый стиль создаётся только при импорте из CSV; новые загрузки всегда идут в центральное хранилище. При удалении товара Shop-Script сам зачищает его директорию (и старые файлы); новые файлы без оставшихся ссылок плагин удаляет сам.

### Вспомогательные методы плагина

```php
// Абсолютный путь к директории файла
shopSyrattachPlugin::getDirectory(?int $product_id, int $file_id): string

// Абсолютный путь к файлу (строка из БД: нужны поля product_id, name, [file_id/id])
shopSyrattachPlugin::getFilePath(array $attachment): string

// Публичный URL файла
shopSyrattachPlugin::getFileUrl(array $attachment, bool $absolute = false): string
```

### Модели

**`shopSyrattachFileModel`** (`shop_syrattach_files`):

- `add(int $entity_id, waRequestFile $file, string $entity_type = 'product'): array` — загрузить файл и прикрепить к сущности; возвращает данные link-записи
- `getByEntity(string $entity_type, int $entity_id, bool $with_urls = false): array` — список файлов сущности, упорядоченный по `sort`
- `delete(int $link_id): void` — открепить файл; если ссылок больше нет — удаляет физический файл и запись
- `deleteByEntity(string $entity_type, int $entity_id): void` — удалить все вложения сущности (используется в хуке `product_delete`)
- `search(string $query, string $entity_type, int $entity_id): array` — файлы, ещё не прикреплённые к данной сущности

**`shopSyrattachLinkModel`** (`shop_syrattach_links`) — вспомогательная модель для работы с привязками.

> **Важно:** в ответах API и во всём JS `id` всегда означает `shop_syrattach_links.id` (link id), а не `shop_syrattach_files.id`. Это позволяет корректно работать со случаем, когда один файл прикреплён к нескольким товарам.

## Требования

- PHP 7.4 или выше
- Webasyst Framework 3.0
- Shop-Script 10.0 или выше

## Ссылки

- [Страница плагина в Webasyst Маркете](https://www.webasyst.ru/store/plugin/shop/syrattach/)
- [История изменений](CHANGELOG.md)
- [Лицензия](LICENSE) / [Лицензия (ru)](LICENSE_ru)

## Разработчик

Сергей Родовниченко — serge@syrnik.com
