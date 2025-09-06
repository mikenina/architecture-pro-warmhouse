# Project_template

Развитие экосистемы ТеплыйДом -> SaaS УмныйДом

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

[Диаграмма контейнеров](https://www.plantuml.com/plantuml/uml/hLXTR-D457tFht033udKD08X7YhreKrN2wWLOPDzG4AA9lOqiTOVmPwq6wsGjWrbGRkY0Y4X0QB08l6QfetgDctw5yR_Y7l7TX8xuzOHx6hLPYQUS-wzvvwvtXNTKeskHhkEeRGPwxZhvR933jPQEcjtcrsNEgfjCMgnDTKsons3MAnh6YMNQORUB5S-A5LnwUdkJjaaBgCEB3Kgy0JHBUgiTU2xkToAHlVWesAxf0eB6N0g3d7RL3DrIoj702MxGosNgbr7PptYg6sTKPLr7Vf-IOTdtP10RYMCA88hLspJjAs0oyBcKq0k4yD8sTxLNVMUxHfmc6tdDZNjaK5DYA9EMeNy7b69HlEhohlDLiKsR6SZ_ywUHkY7x-7Q7iGRhXvWBFbYydcNsGvfYGFSDkdGZITsrzAejcK_h-7dGd7-Q4DljLdjMM_BwD9ivsk44KZzCkT_JWt3FaWStpI8-Ymu_WaraaSRKFeQwnbKsL2sHLbtOALGBEPoLUgujjLuz9mLK06hIfwVyWaV-s_u9VmSye5_n4Vu6GxA2vuTg3xTo4C16lX8eudH5dEr7jJU52TfT5zNAPxrrZ_qZ_oNSD8L_WRdJVBozS6gmc_WyuNVvsFkANvVR0puhQ0n1XAt_DP_pKSA7ohy0bPlWDnnaf8BlpKi2fbgsbsdRTjQdDk-hb6xqRPTfbijfF8Zz-2q4P-K_6C-4Q0UG9qBdh_pCu0U-Ay4ZP7Y7mDfvEGXIVyxFahYguRTHSZSj0KRMrXJulGAhaaSrhOXQmrNC0A86Y4rXR-5O32ugp2oV55vaLFWp_IKWjiX-FNg9XBxXEmJLtNq3ii7COpuMGbvOxNyb_xNi02PLVYlm38WVyud2ly5o9-9-4QON8NVIOjzWb4nqd91C54F5HDyr2uqXmdXcRhgs224iEQLS5tPdLj7bezjmVKtWB_6qlk7I1LGFI0ALRyJgOrAxVadgUmWMTu2dq1qIJRROlL_u1BeCIvPAGz6iR-PqqjIgKSRox7XVs7GyEy6QmRBthsWja4wwSYVxcnMbuH7jG-4sh4nhg4rCp5ocKC9t1wj99bQjB4aZs7KfscL9nszqIAC7f3UhF2RrO-Lny7Y7CJVeAL3YEe2sssCIXk2nWQ082meQ1vHOr7wmq295n3zsFyU-YdoefkfKIqAGoa0I5z85hlIWvx4z0tyRrBqj8ee6F18P3mXGVGz805VdC0fay031m4F_xWeRTXudhQRivxLclETkerN1N554QhWZ2s7kbyQ8bC_y9zIsX2JD8yQNPN51TImFW8pBCi3YCjrO2OIYGuHP19zCKzg5dn6_IzDWab8rDD8P4Qy1nA6WHXStUxeAa9ixdvM3MuxbBOGu8N_9gOlHTXYCl_p5A9n8xNyC5IQnD926g4EAgRsC8_huAu7BXTonAzoEPXwmW4ck2Vv7_3qEJf7a2-u4_kOEupYbRZyhlq-TDnOt8oUaDWHVFbaDIO-k6l2XwJImvODdGg7aQ0ok8SFpVA7lUl57KmG5bJJxb6OFG8xc76_xpvSLqJ2PY57HeF8qoY1y6NeBHEqjLY9yJJ82aurmRIGFXpaFwhNgn36D0hvxIk98IsLwpAF5QuN381YW4dGXxJVORnegt93HUdS2kSzX_rfKcGHfDxR_EV8ZVrlqyexjSHuWzu38hkdqv0I5kJtC-Ebti9fK0iQZMPvwIKBdNgqq2nntGz3EhBdazx4Jq4aE72B82LPFfJAHqbpeZJSzAn8uubTkq5K6LcbKv2Q8lz7b3We1SXw0d_FH3k6yx37BvUBTFOc9eripbdzrzXfWuSfPzRijL3hCCgI94MHnb8iC3wjy3-X3E7hLy3_Yz2WqD5bFHavFHh8LUWSmYwyT6kBgJx1HrwpHStAAlUmGgLOsIENEfrqF8rYJ3EyXQxyJwfy66Ks2YrVuYFfcqXMN8a3dqQ3HrQuwHl4mfouG4G2A9jACsKXuvKG3HefXhVmdo6diXUGR3w8MFy2)

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