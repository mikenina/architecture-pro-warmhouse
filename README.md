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

[Диаграмма контейнеров (plantuml link)](https://www.plantuml.com/plantuml/uml/jLXRR-D457xFht033udKDW8X7YhreKrN2wWLOTDzG4AA9lOqiTQNO4_QZH3IjgKiQ5TK82GaG1HO95xJIrJtblw5yJ_YdB6TnCvCwWXeLRMPySptdTjtZhjWEhhLDMZNixGsOnr_fLBnoDvoosJjRhFhKqzt7KOTjgoxTgLZ4OTrRMl9PuPbDYlLzvPgkFHqQxDY4vzH3vOQLJX1J8Twonrujb1OCEWEVDHSdzHW8GTErID-cngswHYL1639xL37fthNCrcFU7hRP5HdNO--ksJ2MJycK5Y8BKhWAbNNjbqduZAr-HIGAyIo5DjRfg_FsAu3X_5sOSqm7bdK1YksIQjKt24wCMXnKNkxsQgwbkkj5j_QCGXz_nrSsm5xuzKzjALOpfxtcUkHbhZ0RvCENNtYTXs36klkypf-BfKdhxRCLflLd_NMhIxDTxz1607NptF_fzIotBtCzKsBwC-YwvzGAtkr1Q6livv5jLLjGuHr4rPAvNAXKAEUxpgDHyzP2JDWKIlo4pxaD-5hVWc_ptW_FE03_0mN5GNF3aIVhXOX00qyqcYYjUL2lGUnjyLD1jqrTOftlGdtmuFm1TnqXN_1VSEYVBs_gF5R-7mH7l8R7cZXeTZeypj1umP8tF6xy1KVQFnCunUmUWlaZhAKVFYhuL3mLDFjUctNDTBSTas3kesswpFJQI6L7tW0jmtuS2ayuaC16W3KkU3v6py5w7xuKj0OQE4Ha4PE0P8Cl-M3BBvkkLs4B8nAiB6ECILUh-JRn6Dj5xpMy0KZWAWJKjVu6p06ZRkABIkMjIyA6doDRYdv7OAFrzQGs4Tab_YwPtPOCR9Xm4-Na3T6AtmHVWKBu5cD_m8i8_BdVAZndu7ygR1lWCxL-BqqsCTe5ICj7n8cgQ5oXe_UXUAmmJpRr3qN4Y6EUJLUrxOcrf7bOrTm_HNWhp7quJvI1TG0Y4BKxuLhar07uR6I7JWhcE8J9LsMpOPO_G-uHFcOJbaf3qQnlfdNorBPJZRcOyF_HAFXvnPZ1il1J53N8XqryiURQxKvuJ7R-oBRiJ2keJHpCVAPHmbqZrQMJ3tPc9F7MLAdgiYJZjbe4KRtI6yS-BNQXzhZQ742uY_8fNsmwWBBxGOpxGnoh0y8GeAYuX4n5g7VZpBn0go_2R-3Uagqw7OaLDE9ePK0v52aB5Pb03M9xkk7NoloQH5HqE21y7Wc1L7tW0GyE8HRXf46zYCUuL5PMh1fFsqqnpLhD2ShTGDR1V5546gWZ2sF-fzRmbFVynyLPOXEcaHDMcLv2ZMEZy2Cm_80uhHpyY5boa0WPf9_4dbiOSuadvi4av3OLf78ZJW34Zf-2cxRxPWwGgnjVLABMXhchyYoY_1rAeaqeNspF9_C5CeOm-He4yU8QSJ1hjh6mpokeuOEN2xaY5yM2Z3Qn5DAr0pvxt3w7EKXyXSqlaFq7LhnKdIuw_0Gokf6jBz0AFa1F7oycD9uQ2Zn8MaXOrt6SeGJHnGPtCD3O_zXWGPfcHA41LLLiuG18whvCVTPJMz54muRcvoe2IAFh0J2bx60356xKY76sy0hEBf48u5w0YX-iBrT0pD6LiXRhCG6bIjNP48gf2sQyiIKag4FRhz7Ur4xvQg9gNCdvFKSzaTEaLcWRCxyfqHomszKuHtfNhf0Pu38ch4AAID1uM5kF6MhLK5DvMWoi4ixALJgmLInfFF-BAOZEv_L9du2IO9JjJ1IukLzQVfeAYLIuQgz8hKdrLiZgtAoKbEGYYB_MuGu2WMazH2-JqKvnaDlm2_ditJykYMrR49PmrTOQVs7AUVEsMkHhMU9boGkId9C8O7faOJ_0M68tx4Y_f_50eMABgl9HEbHGAvYvH1o4QYbBPNr6Jxoc4tdh2no3sUe52k_vLAbaywWYOqgmPkgod-LbJCLx_-2Kg6Cfwxvi-b0-cOpXudZkJCNU4xHJqO9XQHD_K_ZHFOUaK_f4EiV)

**Диаграмма компонентов (Components)**

Добавьте диаграмму для каждого из выделенных микросервисов.

**Диаграмма кода (Code)**

Добавьте одну диаграмму или несколько.

# Задание 3. Разработка ER-диаграммы

_К началу этого подзадания вы определили ключевые микросервисы и спроектировали их взаимодействие. А ещё визуализировали полученную архитектуру с помощью диаграмм C4, что позволило вам получить чёткое представление о структуре и взаимодействиях в системе.
Необходимо определить ключевые сущности и смоделировать их взаимосвязи, чтобы создать логическую модель базы данных._

[ER-диаграмма (plantuml link)](https://www.plantuml.com/plantuml/uml/TLBBQiCm4BphAqIE1lz02JcLK6YFxTcihQKHbIKZhKD2qd-Fl9PQFsAVvDsPtPcHme-s7NXefT4MwTgXT9vEJWYqfEagTry1_Kv2a7qyo9kGyIk6SwMLV96sR-jO5x__Z5SywmQPe_YOMUFVZBVVVwD53uJgS61KMSx0WwG5u_YUe8Ln3P1sTXCGPMemLUfaoDY3CsW3jJPHT1K8g0yHKJjdkAymD5vtm_XO9hpGskDd53VnoM8NPfVwPnujjFXa-4UonQEpnpntm_ConkBauYLj1JCnjrx_DZ1vDy_8YvyDN6rTFOp9Kb3AOmXfuQQCQPRtUrcwMufNy0jC9LOZHe6fb9CHVv7DwGy0)

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