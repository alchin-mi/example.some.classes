Данный функционал написан на symfony, doctrine, API-platform поэтому он подягивает определенные зависимости в найемспейсах
___
- `Entity\Goods` класс сущностей который маппится с БД при помощи ORM Doctrine
- В директории `\Dto` мы задаем классы для принятия/вывода сущности `Entity\Goods` с нужной нам структурой
- Repository `\GoodsRepository` необходим, чтобы абстрагироваться от субд
- В директории `\State` хранятся классы вызываемые при сохранении и выводе сущности

![Иллюстрация к выводу данных](https://downloader.disk.yandex.ru/preview/f1d57ecf564003f01d213b913f29127334bce727f3e6da7cdb352f77382acb93/65017c18/Cu3BHxwoOaYpGMjB8UrbuCQT0EeagOScqzVpd_aa0M3iICFOyIZZSYjWkiX8-4UatQhvqkj6COrgYTCAx9kIHA%3D%3D?uid=0&filename=2023-09-13_08-08-08.png&disposition=inline&hash=&limit=0&content_type=image%2Fpng&owner_uid=0&tknv=v2&size=2048x2048)