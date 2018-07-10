# iqtz
## Оговорки
- Все операции у нас в центах (одна валюта)
- У каждого юзера может быть только один аккаунт
- Я не монтирую внешнее хранилище в Rabbit, Postgresql и Radis. Поэтому данные сбрасываются после каждого перезапуска контейнеров.

## Инструменты:
- PHP 7.1
- docker
- docker-compose
- supervisor
- https://github.com/php-amqplib/php-amqplib - для работы с РRabbitMq
- https://github.com/moneyphp/money - для работы с деньгами по заветам Фаулера
 https://github.com/vishnubob/wait-for-it - для запуска скрипта, накатывающего тестовые данные (рекомендация в оф. документации докера)
 
## Файлы:
- docker
- - php
- - - supervisor
- - - - conf.d
- - - - - worker.conf - конфиг supervisor
- - - bootstrap.sh - скрипт запускается в Dockerfile, генерит тестовые данные (запускает сreate-data.php) и стартует супервизр
- - - сreate-data.php - наполняет БД тестовыми данными   
- - -  wait-for-it.sh - скрипт проверяет, что на конкретном порту доступен конкретный контейнер и только тогда запускает команду (https://docs.docker.com/compose/startup-order/)
- src 
- - AccountBalance
- - - AccountBalanceInterface.php - Интерфейс для классов управления счетом (AccountBalanceAdd,AccountBalanceASubtrack,AccountTransfer)
- - - AccountBalanceAbstract.php - абстрактный класс для AccountBalance
- - - AccountBalanceAdd.php - подкласс AccountBalanceAbstract для начисления денег
- - - AccountBalanceSubtract.php - подкласс AccountBalanceAbstract для списания денег
- - - AccountBalanceTransfer.php - подкласс AccountBalanceAbstract для перевода денег между аккаунтами
- - - AccountBalanceOperations.php - класс умеет списывать, переводить и добавлять деньги аккаунтам, используется в трех предыдущих
- - - AccountBalanceBlocker.php - класс умеет блокировать, разблокировать и проверять доступность аккаунтов пользователей (использует Redis)
- DAO
- - Account.php - Просто Data Access Object для Аккаунта
- Exception - содержит классы Исключений
- Factory - содержит классы фабрик
- Persister
- - AccountPersister.php - Умеет сохранять DTO Account в БД
- - Persister.php - Родительский класс для AccountPersister. Содержит методы общие для всех (потенциальных) персистеров (например транзакции).
- Que
- - ErrorMessagePublisher.php - Генерит события о том, что произошла ошибка
- - SuccessMessagePublisher.php - Генерит события об успешных списаниях/пополнениях/трансферах
- Repository
- - AccountRepository.php - Умеет искать Аккаунты в БД
- worker.php - сам воркер. Точка входа в приложение. Запускается через supervisor
- .gititgnore
- composer.json
- docker-compose.yml
- Dockerfile

## Окружение
Запускается 4 контейнера
- postgres:11 - для хранения аккаунтов
- rabbitmq:3.7.6-management - обмен сообщениями (management поднимает админку)
- redis:4.0 - для блокировки аккаунтов в процессе выполнения
- php:7.1-fpm - для воркеров

За воркерами следит supervisor. Сейчас в конфиге прописано три воркера.

## Алгоритм
- В зависимости от типа сообщения (поле 'type') выбирается класс для его обработки.
- - AccountBalanceAdd для типа 'add'
- - AccountBalanceSubtrack для типа 'subtrack'
- - AccountBalanceTransfer для типа 'transfer'
- - - Все три типа содержат метод change описанный в AccountBalanceInterface.
- Проверяем не заблокирован ли данный аккаунт (есть ли ключ 'user.account.balance.USER_ID в редисе)
- Если заблокирован выходим из воркера (консьюмер объявлен как no_ack=false, так что сообщение просто вернется в очереди и его возьмет другой воркер)
- Если аккаунт свободен - блокируем его (сетим ключ в редис, ставим ttl 1 минуту на случай, если что-то пойдет не так и мы его не разблокируем)
- Достаем аккаунт из базы
- Стартуем миграцию если это трансфер
- Изменяем счет
- Сохраняем в базу
- Комитим миграцию если это трансфер
- Помечаем сообщение очереди как доставленное
- Генерим событие об успешном изменении счета
- В случае исключений на любой стадии генерится событие о произошедшей ошибке

## Установка и запуск
- git clone https://github.com/boliev/iqtz.git
- cd iqtz
- composer install
- docker-compose up

Эти команды установят и запустят все необходимые контейнеры. Также выполнится скрипт docker/php/create_data.php который наполнит таблицу тестовыми аккаунтами (100 шт)

### Работа с микросервисом
Для публикации сообщения можно использовать стандартную админку Кролика. Она доступна по адресу http://localhost:15672/.
На вкладке Exchanges нужно выбрать точку доступа user. 

#### Типы сообщений

- Зачисление

Routing key: user.balance.add
```
{
	"type": "add",
	"userId": 20,
	"amount": 5000
}
```
Добавит 50$ на аккаунт юзера с id=20

- Списание

Routing key: user.balance.subtract
```
{
	"type": "subtract",
	"userId": 20,
	"amount": 5000
}
```
Спишет 50$ с аккаунта юзера id=20

- Перевод

Routing key: user.balance.transfer
```
{
	"type": "transfer",
	"fromUserId": 21,
	"toUserId": 20,
	"amount": 5000
}
```
Переведет 50$ с аккаунта пользователя id=20 на аккаунт пользователя id=21

#### Проверка результата

Для проверки результат можно использовать ДБ. Поднят контейнер postgres:11. 
```
localhost:5432
DB:iq_tz
User:iq_tz
Password:iq_tz
```
