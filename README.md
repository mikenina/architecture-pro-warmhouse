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
- Домен Управление отоплением
  - Контекст подключения устройств 
    - Административный API для формирования карты устройств в доме
  - Контекст команд на удаленное устройство
    - API для отправки команд на удаленное устройство
    - Обработка ответа удаленного устройства на команду
- Домен Управление аккаунтами пользователей
  - Контекст управления аккаунтом пользователя
  - Контекст аутентификации пользователя
- Домен Телеметрия
  - Контекст сбора измерений температуры 
    - API для запроса температуры у удаленного сенсора
    - Обработка ответа удаленного сенсора на запрос

### **4. Проблемы монолитного решения**

- Масштабируемость: ограничена, так как монолит сложно масштабировать по частям. При увеличении числа пользователей и устройств, в первую очередь, потребуется отмасштабировать узел Телеметрии.
- Разработка: монолит имеет высокий риск ошибок из-за высокой связанности кода; при изменениях в одном узле необходимо тестировать все приложение.
- Развертывание: требует остановки всего приложения.

### 5. Визуализация контекста системы — диаграмма С4

![system_context](schemas/context/warmhouse_monolith_context-System_Context_Diagram_for_WarmHouse.png)

# Задание 2. Проектирование микросервисной архитектуры

_Вы провели анализ текущего монолитного приложения, определили его функциональные блоки и выделили основные домены. Теперь ваша задача — спроектировать высокоуровневую архитектуру новой экосистемы, основанную на микросервисах. Для этого вам нужно определить ключевые микросервисы, спроектировать их взаимодействие, а также визуализировать полученную архитектуру с помощью диаграмм C4._

**Диаграмма контейнеров (Containers)**

[Диаграмма контейнеров](https://www.plantuml.com/plantuml/uml/hLXTQzjM6Bxthr2rNjYGr6oCNHHouJXfsk4iRkmo2WDnB9tOelemajpKb46JB6l7oih6OD2DTbi7ktK-l2XVxbyu-aTxtoF9jkGZLnvhAOdEaSxpl5_F-vxUq2tLw6cqvnXIn_EwxkrosI6xjzgwr-crUYvrLDlogENTKcspt3M8vVLCOzdrD4DlbQkVBjTnwS5chMmIrwCEB2bL-8Be5dLkTU7TGk66HdVWKR9TKeU573XLXxWTgfcwfPLZW6MxIosNgZr7z_h4KJkwHrMlvz1FbdNureq856v45iLmvQfjchOLSfdPV038PM8O6TkRkglEsMu0XybseLPvkFMWgTJMxpILxNiRTvl5Kg5GqRHrWvfWMfEqY_8EKOb6vINfPgjTjGtRMP4_sj48_UnZNDi19qIhBOEeZ-HI-dlNintIvWUu7TAbAzjspzAejce_QU1piJHzjA6tErxZKN_LwD7K-RlemDdpDU8HYCSYvp-aXc7lPjFVfWO_chXzIyNNFFeaUkrcPNjxwqlbpjONJQMwLTlQXbq3SYMnExtHyFe6NLdZwL63vs89N5odZcjRoleJhuYPj2J9x2qRiSlW9Jk3dyTi4Eop8Ju3kinDwq8MqHKP0gdW9qeB7LGgDFgGGoO_IQEFTPNYMU-2lM0_U0OddUDlSDv85gyFbYHs1S-dmG6xPBuK7F2D0RlcD2w1n3Mx3hvdGuaTI-mKLg-0t66QaWk_AHO5vxRidjEnRIt9xR6kKLlfsAwdMssayXFpuRGX6oq7Xsp4GNs0Ek4ytx0ZW1u4ppcDeHGS0cdav2F9u3isJEEhXjr3oCAub9LLJ0FYz8kkIHolOuFN59Sp0eW68Gs9lGDZqBZpo3Av93qjIF1dV4hHxH9ylLv1Of-JnyHL7RthoQ4DGtQqZBmnMi6pu1jO0CzAx5TW6P8_OIE9lGRoHzo-8JfNOk-5mNx5KusqNMb5YikkbEAZzg2UJ33Fr5N7XaI8ObwDrgNDgNLakM5phhy1_0M6FjX3ge3g0r68-dlksZZKVl0ganquov_X4oPTciqQN_qVk8Jvc4nP8G-FeYHuJZzDfHblBCQ6_Ob6mxyhZ1ai-tD1RODqiv6tQfNwWl2OxGEUxLWO5r2QkHYvdaC9TA5scamZtbYGnt5SfpDG88bA57tIrPKsyUWkwSDHKE_KiOWXHLbGgT-JDiBTARo_8B7smCHJhBrBJBjZIBW1m74z2Yk91vpdmLwObgVWYilW1oYkM9YkngercoLI4K0EUFvYYVfGeEZBGV1jHd8j8GfQF-JkJ-KZYY2GW1T7SCee5CH1o2CuB0chD-cqjTQaWBNMTDckOHSZBez8utujySn-P3zdL2MwQHetRhQb6TmeL1mr2im7CMSTb0yhLHSSCrKDqrWJ6tDc_S8aF069PXQ9t8Xp864WIS0rxQwEauHSsRnV3tiSvZ3FjDFWPIAH92w6ypmVZpoPKOQHr2IM5j68mbqrjG_pk0WxF70v5ID-NIZ0U1IDBM5tPB_3roUe5w6_e1CUeE_GYkUyvLq41r1QbxmV-bpQz-7bLqi9qOSE4tqaB6QipKYVS0G98uDx-D74VrYaVbAtE65EDQjxmiGHrls4-xmkU5lY3fkO72iA8e-j1C9daOYCKByI8SRJm2iuouGpGlP88DzjDkjWnjWASSyLs93bojiYCUNo5evzV6n9qGUtlqTxKJ_5oecfSyqbzWJsnquHMP3PhTaliUm6BxB2ErQ-P87E0H5rvooab0G51xdnCdjl5jHCZcQrrBIkiBSGO1oHEK61X_Q4ePUIAXBURafpaRDjUWDdfaitsHt3Mip9QpuDeQonltcG8cVwx4natAh-uNFnhDTSzwx0czV96O7FG3Yul-0xLo8B-DKX3666mYIxFli3_1JTQKBNVHNLFmgcAELZ8SNwF8yAarUZdwqS4IEXMeWTEfjMW322lqTSfwBhGMOIpKFARxTGHeH3NcnXbfhCb4sUX4aUA52E_tHgc8P21sNEqN2_745GSHp-1mFFpN-98S4Rvo7tn7MS4HUY_EKNX7mUcin8YoVdll1Yie0Z9rDYBl2S4Xqt_Wm9c_alYRUYoJ-ViicbVm40)

**Диаграмма компонентов (Components)**

[Диаграмма компонентов DeviceMicroservice](https://www.plantuml.com/plantuml/uml/fLTBRzj64BxhLspO72J0ij2YwA603nRjF0mvKaIbHKx4YbnBRFWGI2gsa1QmdRff4GD1WvuIj0MQ0htAgjt83ob_OVcFEhia9OgY94hMHTnTSkQRcMyUUqSp5RsjahQjewRhjfol2mKRxwytDBVPhhSTOYkMwHBJNLSiez3IiUcs3JtlkAgkrGlYb_aAstgoMoeOs7693LkoQ1ajosJVjE3TJEQEIlOqao3BmHNOIA57jB7J9AgXcMeXL92tMiHqYDAsDRU3RQMfkKHnspRv8g_1jqu087Cdi2XKLm0uXcNwMAOE9P1ImBeUG2rjFYq_gScbxNirkVhm_eDQDfV9RAhgjauC05V3ZQomXnMi4c4DtQqtH4ktx0tXipqLawy-PtjxO4QmMzUnyap8nRztNCl63Ix0QU8MsQXQRLCbQj4waDWwcukArhL6qvMUTOfwcyJaxpCNJCjNiOl1eyl8VqfqtTfF1v_5oRPqdTZ2b9o6JJgpfLI9pYLWfsCgx3MN70IltTsiLilVolVAZsgoM2wLgt2g0sScJgC7ajlHoSOMfqa9rjaS14e4jHWMTjPfuJKaI9LDK2FiuEVOKMojvR8LVKilw5cUzkd0EwONtg7t8snSUaU8lgTzxnKTq27zbmuHVKUxz8oz1syTqowYd-3asZkb7-6_HxliSrY_uQRWXeCsK4YEt1ZBLZshakUQGcIr3kgt-3FQOa723Wkf83qkJQpfh_GtvBtqZhnZxn1KNR8dsekg2JcKg0SooC2cAWC9nWf5GqKrgwKnnmkRkuyhNDCVO2-u0EmuzquHlG9jDs3rW9v7jOMCocMa3kIO8MyVk84o9kH372aT2idxtJK4yeUWx2Mzflt0IAwGUVGQS0mWBA_f1Q8zHCzzEDu9vt8B4fzi292viWDFiac0SdMhRJSjInMYuPUBZF7OxeGUCJJ5jg3618W3b-n6TeKSUf51y1iLhwHlrtLBmQvccV8u2q1UATWLewaXx7uZeYfnN3IvJJ_GJ-2KxiZUFhEs2pw1C7X7y7H3-r7lHn8kbmBYY0GHWBx1gyER9kEjOJeACR6jMPD0fM0t7TJVWHiZN43UBeBbJnnr5pO1ER286tD6X_3UwrlW3PFgEo2SYVL9sA9_kEEVJQBa-LPv23wCEzPxW-2H5Pi-pxnBv6S380L_iuoOLN9EuVWA19pCJDa9wvOXzZXBGbPBGOR0LX5gEJ7Lz3HUWh3nz5n2xKnwBaN4CO2GXJCWB4-xLGWsXZF9hXcWte-8rBqjZNAP7nABANHhEK2EMvhSm2xPnnrG3ks0C6WeAAoCxlVzquIwcYnc7IOCkq5CfJFgNnCfjXqU1zv8JZ9e6T2GmG6ylP_m40t67Rq7hOMxWlNqC0KZfYCMGfQfvx0UTIFmJnB3_BOqrVXJ6HinqMaRqHeY1Zln-Fu5L7h5emjMdB0UoXvzSnaVamrckJ9at0JG_1NOxiui6glPC4NK50mD4Do0gmV-5e3iiua0nh0UdxX-vYSteoaXCRs1g31L-kDU-dxDcJ849h3HnzTycTI21JQehg4TgVoeyA1MgqYWSQwwfDwRg0pCdDDJbzQRt4ovvaMPFrswLW4mjplw7W3DOUK6pN4E9cdaAbD7DMLN2on96RrK_MgEoCJ0fEn2SyJES_TolIKDzj7RouccR-FLrfyGUi04Vwu_vlUjYrbTQ77yuyZu3OjV-RQAuk98h_hvJC_41QGEO9AWT5WMIPgy3D8_eUAUiRiNlO8dkDSYFY8CwPdtoxWbyXemlgO7xS7VM07AQXqVvgmBNZROZFnVU2sCK8JFyrE50ttF5_bgPHI1r4qusMV_W5FO9PLv2nptSQ5pKkjBtLdd2vxlo6MK5BKK-6CKOovwbzJE-I0MQ-T9RGy-NKFftRTKEPfarD-Szp08UOSZzez7vSNpOHh6_Gy0)

[Диаграмма компонентов TelemetryMicroservice](https://www.plantuml.com/plantuml/uml/jLPHQnj747xNhrXVyo21RT5I-X3mWomxJe8SAJgb9K_7wcuj7JdT7djxiKKfM7RS11e8BOL2Kn987legkpPLarh-2tl_g3Dx9-aioQtbK8EGTdRtcszclfdraklRNkImI7YaBMKGtYuM1TrTRRco7JMZa0cR-vBvSjNcdMBWKLz67Mybb8xdDelbBrTgQ7gqNIbsQ2YP09DLvfs0-tWdWBEvt9B3TboV4HxI6XYkuQSiQDXcJiVrdUB8mGeFc1yoEnAkx59Xjrt9R1a9ziMA2tV3b41kAOrevAu8T3hSJxZCR9g0KgIUbrAjb1vN7pMiokQTXbM_jtMtaI_aSYN7sVHO1yWrQ2jlx52REinO9hUQhJBtk5WpFjjnAFlgSxJjG1YfjUbH-ubHcBuVIYveImE4RHgmjJgFV8SvwtpFn7M-a8Nst5PRca-wwrx4fl1tCGMp-0wL53Aw2FvZvdbyzthqiKw2UnuJnWnEIx3krIXrvca46dPz6uz9jfSUkrMgrwlVM7Ug3nfMkLgfrc7N0yrSsirkcBBhiRKDBPCAhFC5A5GPt59OY7mOq6LYcBKIk37kqwSqj8KRI5ofdzIPEbvH0tKU7wYpU3z-1eQ_uXvHhzKWVgxErL3zeOP4_QhwwXZFmQa3rIVg0dO-nY_Ld_1zelfu7TQlT2Yq5P8rCX97OS9bevcN3FCdHTVYdWmifmaq6YCRgLOQDRA1HQCXbjWm7rOkhTMFwcUYtg4p-FoDRE9zu3N8EXwfwYhFe8XBNXiySB48HcdxOKrxUGEnGph0nMdyagXJYFuSSlH3V4GWEV5XtDD-XvWh2FqawtsakKBEx48NTgpDFPbts5FNPWZzVlgk6XhpxVrb0X4EWS8XH3j8tMegc7DCW2QbpeWwGP93EDwFZxJQ0nWDRCs0thP2-6Nv34JPv95eS-vaCvCFfM0KnalBwhYsu31-KghcQ8DiPpQGx1RNb7-3n7z0od5FP-i4D0FIWIyWSmPnPNLehMFhKT7D56BAuQGYbnmMoBSv0d_ZMNe5mkh4F6dCB6mvswx6zjTbKcUX99VDwhswW5ZwummFCBzZ1K2A1_-jWSnWADo0UnXrPfXh6n9SXo72V4TpVGUvVwu-O4EUn4Ue5VnvYfr8G0eztTb7ckNvf9alTCs-XvhraYDWxY-eyd5pBnZTd6gR4s5bOxjn7Ggvx-Rg3YQibVIXHGFNQb79TcaNN463Ck5JZspeNLAgtIDRoUuSySs7MKrDsFre1zW43QSN04v7zreNOv3K3jjsFtu1dqE8O013jG_bWQbsqw900k25IKR_Ois2iQV3_H2782OUnNGT7WO-PilalbbzKBpRQDHCO3AcSUMOKRy0j2vr_09YXhc7KtrgZ23CGe4i8CIKvGAD-J-0Zs_CUo3roxsnNluKficZftw7SXwCKWnAoyed-MSapVRenRgstDLRV0YWCp-Eu2VDUWRrJx0H-hgGLqlc3SGmdWxngyGTg1RCGsWfU1wJPp73OBvgzUHSaDPCJj_20tsCJpIyXeSpeV-Rupd9_mS0)

**Диаграмма кода (Code)**

Добавьте одну диаграмму или несколько.

# Задание 3. Разработка ER-диаграммы

_К началу этого подзадания вы определили ключевые микросервисы и спроектировали их взаимодействие. А ещё визуализировали полученную архитектуру с помощью диаграмм C4, что позволило вам получить чёткое представление о структуре и взаимодействиях в системе.
Необходимо определить ключевые сущности и смоделировать их взаимосвязи, чтобы создать логическую модель базы данных._

[ER-диаграмма Device Database](https://www.plantuml.com/plantuml/uml/hLJBRi8m4BpdAroEIFc12d5fLLLjRotdQB6tZI6n8zkfGD1_h-D7S091L2e7PC-kPyVZtUWj5mqeg4d51PfzWqGgKyeiGs6usPFPIgEQ4T2ajGjoo8ZzpRjrmHcv9wAjrnPVB2p2eIhyVhbqUNcU_PoO7l6RKtHSp2qZcz_TnfSUwEIEJZszXjHv9IaOBiLamg10chAAAGmrLRpfM6pSuCuuc2e4WwnWzj_m6kz9NBbmYPKfQObqUqE6Dc1QRTdNKbRdPdtO6oQ6kHkFN1kRC31AG8sfz5PmO_VQA2w-qd9lmIrNB5eNAqrOUUc5Yi8VAQLjaIdh4r-3jaJ9StVIoN0SKSCbmci2BdHeNnyOKwXruijLHUyK1IWkdIWTDb5NtF-NDpqXkLNUWwnh4En5jaf0vKHI3nKRZqMj8_Zl0pUbEH3Ru4PBuJ6WHYhNdNs_9VWv4xdBy_XeKf7KLuOD2cOPYEt94s-eVJQDzX_iNDMDJy0TqjQrhn-sWPnJi85z9O4bLpZ5FhdWkfmeOfyYdSxZCS_b8GnivZwQ0NBldlck3b2Sh24QxvxrWpWa3L_F08vUTL1o7EkRQoB-2m00)

[ER-диаграмма Telemetry OLTP Database](https://www.plantuml.com/plantuml/uml/bOxB2i8m44Nt-OgXIw4_44fNxaGG_80oDGD6yoZ9f52A_suQwmDNEelXpk4Ed7ZLRi205ensnDD0u0EVlH3aMFC4rPP6VQ8Z8rC5646LkuiBW5b0dasaeD5qBjcU0aXOGzCSTcrRAdMzDikRX4lqhd-WDAZCLBTirfOaF9EuVMli2MCAP3FzbB5l_B_IY2RHg_63qnq0)

# Задание 4. Создание и документирование API
_После успешной разработки ER-диаграммы вы получили чёткое представление о структуре данных будущей экосистемы. Теперь настало время перейти к следующему этапу — созданию и документированию API._

_Микросервисы должны уметь «общаться» друг с другом. Ваша задача — спроектировать 4-5 эндпоинтов API для ландшафта, который вы создали в рамках предыдущих заданий._

_Вам нужно определить необходимые эндпойнты, методы запросов, форматы данных и описать контракты взаимодействия, чтобы микросервисы могли эффективно обмениваться информацией._
### 1. Тип API

- Для взаимодействия SPA с микросервисами реализуем RestAPI эндпоинты.
- Данные телеметрии будем обрабатывать асинхронно, в связи с потенциально большим количеством подключенных устройств.
- Команды на устройства также будем передавать асинхронно, так как есть требование пользовательских сценариев работы Умного дома, а значит возможна автоматизированная отправка команд с потенциально высокой частотой.
- Также добавим синхронное взаимодействие между DeviceMicroservice и DeviceApiGateway для критических в плане скорости операций.

### 2. Документация API

[DeviceMicroservice RestAPI](schemas/api/device_microservice.yaml)

[DeviceAPIGateway RestAPI](schemas/api/device_api_gateway.yaml)

[SmartHouse AsyncAPI](schemas/api/smarthouse_async_api.yaml)

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