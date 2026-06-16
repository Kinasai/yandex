# Yandex Map Reviews Parser

Данный проект реализует комплексный подход к взаимодействию с защищённым веб-API. Вместо поверхностного парсинга HTML, проект использует внутренние запросы фронтенда, восстанавливает логику генерации сигнатур и поддерживает сессионную идентификацию.

# Системные требования
- PHP: 8.4
- Базовые расширения PHP: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML
- Менеджер зависимостей: Composer
- Инструменты для сборки фронтенда: Node.js и npm

## Первый запуск
- `git clone https://github.com/Kinasai/yandex.git`
- `cd yandex`
- `composer update`
- `cp .env.example .env && php artisan key:generate && php artisan storage:link`
- `php artisan migrate`
- `npm i`
- `npm run build`
- `php artisan serve`
  
### Тестовое наполнение таблицы

- `php artisan db:seed`

### Данные для авторизации
- `test@example.com`
- `password`

## Основные возможности

- Имитация сессии — автоматическое получение, хранение и обновление CSRF-токенов и Session Cookies.
- Генерация динамической подписи (Signature) — воспроизведение алгоритма, используемого системой для валидации каждого запроса.

# Начало работы

В этом разделе описано, как добавить новую организацию в систему и начать работать с её данными.

## Добавление организации

### 1. Откройте настройки профиля
Нажмите на иконку профиля в правом верхнем углу и выберите пункт **«Settings»**.
  <p align="center"><img width="264" height="308" alt="image" src="https://github.com/user-attachments/assets/828dfed1-076d-4d73-811f-d473b133980d" /></p>

### 2. Перейдите во вкладку «Yandex»
В открывшемся окне выберите вкладку Yandex.
В поле ввода вставьте ссылку на страницу организации в Яндекс Картах.

> Пример ссылки: `https://yandex.ru/maps/46/kirov/?ll=49.607258,58.596401&mode=poi&poi[point]=49.607485,58.596605&poi[uri]=ymapsbm1://org?oid=35935430988&z=15.52`
  <p align="center"><img width="840" height="359" alt="image" src="https://github.com/user-attachments/assets/064371da-7ea8-4fb5-8b7b-996be282912f" /></p>

### 3. Сохраните изменения 
Нажмите кнопку **«Save link»**.
Если ссылка корректна, система автоматически начнет выгрузку данных через API

### 4. Проверьте результат
После успешного добавления организация появится:
- В списке под формой ввода ссылки
- В левом сайдбаре навигации
<p align="center"><img width="1095" height="870" alt="image" src="https://github.com/user-attachments/assets/3a991067-ec95-4866-aed5-4db82df93c54" /></p>

### 5. Просмотр данных
Кликните по названию организации в сайдбаре, чтобы открыть карточку с полной информацией:
<p align="center"><img width="1889" height="422" alt="image" src="https://github.com/user-attachments/assets/344b4f2a-003f-45f3-b18a-5337191b5b0a" /></p>




