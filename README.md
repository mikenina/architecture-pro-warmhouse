# Project_template

Развитие экосистемы ТеплыйДом -> УмныйДом

# Задание 1. Анализ и планирование

_Прежде чем проектировать новую систему, необходимо досконально разобраться с тем, что есть. Вам нужно изучить текущее монолитное приложение, понять его сильные и слабые стороны, а также проанализировать, как принципы Domain-Driven Design (DDD) могут быть применены для построения новой архитектуры._

### 1. Описание функциональности монолитного приложения

**Управление отоплением:**

- Пользователи могут удалённо включать/выключать отопление в своих домах.
- Система управляет датчиками, установленными в домах.

**Мониторинг температуры:**

- Пользователи могут просматривать текущую температуру в своих домах через веб-интерфейс.
- Система запрашивает данные о температуре у датчиков, установленных в домах. 
- Система выводит данные о температуре в веб-интерфейсе.

**Управление устройствами:**
- Специалист по установке подключает новое оборудование

### 2. Анализ архитектуры монолитного приложения

- Язык программирования: Go
- База данных: PostgreSQL
- Архитектура: Монолитная, все компоненты системы (обработка запросов, бизнес-логика, работа с данными) находятся в рамках одного приложения.
- Взаимодействие: Синхронное, запросы обрабатываются последовательно.

### 3. Определение доменов и границы контекстов

Система удалённого управление отоплением в доме.
- Домен Управление устройствами
- Домен Управление аккаунтами пользователей
- Домен Телеметрия
  - Поддомен Сбор показаний датчиков
  - Поддомен Аналитика и отчетность

### **4. Проблемы монолитного решения**

- Масштабируемость: ограничена, так как монолит сложно масштабировать по частям. При увеличении числа пользователей и устройств, в первую очередь, потребуется отмасштабировать узел Телеметрии.
- Разработка: высокий риск ошибок из-за высокой связанности кода; необходимость тестирования всего приложения.
- Развертывание: требует остановки всего приложения.

### 5. Визуализация контекста системы — диаграмма С4

![system_context](schemas/context/warmhouse_monolith_context-System_Context_Diagram_for_WarmHouse.png)

# Задание 2. Проектирование микросервисной архитектуры

_Вы провели анализ текущего монолитного приложения, определили его функциональные блоки и выделили основные домены. Теперь ваша задача — спроектировать высокоуровневую архитектуру новой экосистемы, основанную на микросервисах. Для этого вам нужно определить ключевые микросервисы, спроектировать их взаимодействие, а также визуализировать полученную архитектуру с помощью диаграмм C4._

**Диаграмма контейнеров (Containers)**

[Диаграмма контейнеров](https://www.plantuml.com/plantuml/uml/hLVVRnj547xtNt4nFDXI4Wk4UAYKXyIf2gYHJ9m-825PwxkDVUhzC7Vhf1P2QX92GQrQWP2G016WIBmwYKtSf77-XRt_Y9dTFTjtjvVOWkGXkLtVp3VppNmpNh8zq-bQj1iuHfknJdYlKWd8_chBPkrkinlImFGzHZssQlfkfUCGZtLTPoLabcCtAzKFLcfuz6ZhOSKb8QC173Mgy0QnFHgiTk2pXSAIHNVXqV13KeE3EVnK0nAsgUNQdbM97Qpu7Ug5rEm6DkkHm6pRZ9gi6z3tLsnuDrG02aigejXTfUgxhkz9B9dBH-2vGXmdvthB3irRhkk0ONfTMBUi-mvr8OeTqYeLTub9B5fSDjvjjgg-umThnNTsBK8_V0_FTY5UTRgFiHJBwVT3vWUa9Go4RTAXQzj-rxEejU4_gUDpgJnhsh5RRLP_tDjmkdG--nPX15A_YFtFgEFu-odpJOUOZwNvRUgaJJj0VPtr76gi6Pk2reTmKYgN2uKQ3KBVQzn_mafO0Sj6aP_mCR-ANl0B-7l6-z4X7-8p62eAd1rWdwuLWO06lj9eOhJbGhq7tBl2aaNtR9EYhTVHGNGOFGLBR_0_i3Skwi_xomP_2y-3w8XVyP4H7OcBFhyMCAu0n3M_ZfxpeS7F33w0qxS0xZWDAOJ_6Xw5J3NzRj3sVIk9RS-sgDze-o6plHP2-O6FmDgGZrUYOpuMJaVWwbpW_8sVWkj-z4p061hHCO16J2C46Nt7Xsd_fkDtqMLXqeAD3UIK1BrIw9A0jNt8MYCKYD357s3dG2GPuZ7uQ_Y3ORvHCHRBnfS50tuczafXXy2BzTewGlo4x97G3EmEAyfeXlnq1ID0az7Jw6iuW1mR_1Vm8yCuvsE3_mnXd8f8XvXcWzzeQN-5yJ7I2g5qucugf_2ONMWJ5m9rRJFmeIGK-rLrRcpDd2FA1xx0-YkulyGYY0uGAdY5J03n_4OaEIPz5BtAHGV96cNmoF9BezaKf_y35bcPoUBLuc0KEvq5lJIKdVXYCJJyJmnQ5CfG01Xfd0QqumSiJBlSbiSBEXo3csUYGCROTFFu3rb02KoDLXf1FRvO4CDPt9zvF9EEtMWHHlT9RqhpUkrZuu4yd77n5rJE0R0rmEQwmhewWuhgWmSXFR9L1AE2w0DPTmD-2Hz-YPbG6lHs8b3PCZ1AuEH852Zsu0Ww4EA2iszogcSPlM30QBsVBZZKknia0bwub3GCX081ZkYuh6tFP9usczCEjPgpVRc98uA4WeGQA68heE4NZiZKz_p7dAR39CrwZKTaEUDLyIDyAbhkyBXOlyq58ku7WIBkWbaOqt3x_5omS0vkdygGfpDdCFkWQmGjcWoSgIR0R4v8mOxViKq4ixxrQKqE7z5xY6WGlKZGRWXjIgTb5a8y_NDPWHtF9PwbO2XwggvrDuvBENe1oq3lyQj20POGjK_8OSL_LtcLa26_G3Na3gEG0dCP7K4ZN8dnD1BC7yA7NoqdQW84NxsahGZi92KWk1j8Pl0ENqeoE4eAYm0ieEODCrW5P9TEiTysbEuParAIKEF-Hy-JA07mXMhvCQfDWcAq1bd19KEExFm9NVnePwS6OKoYq8z0JGnvgRodapuXHd8V4rj42Zwa_GRZHRNJwnoMphKGn7EudoH55q7E7EC_nH8PVJjBxcHIHSy5yhmUpVZ9dLTvdZ9b4s-xsd44pNEOgTzaAPufEBhtqtB1Ju0tN4bnL1vbZMbr0RUx7778yvkV5MqyYG6LrSjRKEL3qEeK_nk5GL41bJQ6trFH8MfF7F6BnIATVbVHHZOZTbXPCA3lXZnd8VM1maDneBAaIL6onbAMzHdALeWkJxVKGzxQcgd5_yH5ZiucDnNuSZIElt99HZvNAeXZGwFhat62AlL6oPFGf56-Vaf0Ui59h0yBmuctpBdswDjodwU69_5WrdhN-9b0akf-hXht30eTxYuDzNmgWln7SVbIVELMmfUA9jaEsWATFtVQ-Q3TjMC9pz6fpBVy4zs6FPzyOG_-2m00)

**Диаграмма компонентов (Components)**

Добавьте диаграмму для каждого из выделенных микросервисов.

**Диаграмма кода (Code)**

Добавьте одну диаграмму или несколько.

# Задание 3. Разработка ER-диаграммы

Добавьте сюда ER-диаграмму. Она должна отражать ключевые сущности системы, их атрибуты и тип связей между ними.

# Задание 4. Создание и документирование API

### 1. Тип API

Укажите, какой тип API вы будете использовать для взаимодействия микросервисов. Объясните своё решение.

### 2. Документация API

Здесь приложите ссылки на документацию API для микросервисов, которые вы спроектировали в первой части проектной работы. Для документирования используйте Swagger/OpenAPI или AsyncAPI.

# Задание 5. Работа с docker и docker-compose

Перейдите в apps.

Там находится приложение-монолит для работы с датчиками температуры. В README.md описано как запустить решение.

Вам нужно:

1) сделать простое приложение temperature-api на любом удобном для вас языке программирования, которое при запросе /temperature?location= будет отдавать рандомное значение температуры.

Locations - название комнаты, sensorId - идентификатор названия комнаты

```
	// If no location is provided, use a default based on sensor ID
	if location == "" {
		switch sensorID {
		case "1":
			location = "Living Room"
		case "2":
			location = "Bedroom"
		case "3":
			location = "Kitchen"
		default:
			location = "Unknown"
		}
	}

	// If no sensor ID is provided, generate one based on location
	if sensorID == "" {
		switch location {
		case "Living Room":
			sensorID = "1"
		case "Bedroom":
			sensorID = "2"
		case "Kitchen":
			sensorID = "3"
		default:
			sensorID = "0"
		}
	}
```

2) Приложение следует упаковать в Docker и добавить в docker-compose. Порт по умолчанию должен быть 8081

3) Кроме того для smart_home приложения требуется база данных - добавьте в docker-compose файл настройки для запуска postgres с указанием скрипта инициализации ./smart_home/init.sql

Для проверки можно использовать Postman коллекцию smarthome-api.postman_collection.json и вызвать:

- Create Sensor
- Get All Sensors

Должно при каждом вызове отображаться разное значение температуры

Ревьюер будет проверять точно так же.


# **Задание 6. Разработка MVP**

Необходимо создать новые микросервисы и обеспечить их интеграции с существующим монолитом для плавного перехода к микросервисной архитектуре. 

### **Что нужно сделать**

1. Создайте новые микросервисы для управления телеметрией и устройствами (с простейшей логикой), которые будут интегрированы с существующим монолитным приложением. Каждый микросервис на своем ООП языке.
2. Обеспечьте взаимодействие между микросервисами и монолитом (при желании с помощью брокера сообщений), чтобы постепенно перенести функциональность из монолита в микросервисы. 

В результате у вас должны быть созданы Dockerfiles и docker-compose для запуска микросервисов. 