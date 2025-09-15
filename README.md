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

[Диаграмма контейнеров](https://www.plantuml.com/plantuml/uml/hLXRQnj757xNhzYeVP30jcWfVJ3umPP3sc9JDN8U2WKnsXrBI_QYxexiY528xRffIQXfAHJQKZTDeQ_oHNXza__2x3_gERExadOr8wze0YRMZFPytxbzvwmVc8vkTGtQzIojpLZ7NwvKFBAxr39Pkzli-jJJNOTHXotfhbtfMCHXNTjQz9bXcSrAzQF56XuzsToesCHdr8EZHXMU8AP3lQKEVBTGU63GRVYekJwfmK4EdAf7_3OrRDCnAWd0ejkXZa_rhcUo7l7qjicepheU_N3HX6Vzc43XGUnH0bUfkhRjEX6NgSidW5mXbgMutZHzVSPr7JYChmkhXl7GeZPuiKLQfU8sqOb1YmlQ-yrMrRLSRwNuthP1wCSVuDas-1kVDYsYFosMiy_xpFL8InZmswH3LnwxNSUWnfhxh8wVI-L9qvRPQhFwqzwQrQKP-xiOgsdx1c44GZ-F_I-fPRcxQlgFgPKrRK7gwwndKMr5MnTftO2JKhbSADIevxjEu-4pLi8AMD2A_9WF-NNucb_0_wUy7-xp0Nu6GqN1imFPfoj5I4037saqqTjoeTw3tDl2aa5tJ9sYhRVXNhWVlW1Bb_WRs1iMvUVz1OtVmEVpy81Vyq0B3yH5dzyA6jT0ufRVXg_uGEEd6Z-7qniWTvYbvCDl3OT2f9fkrskxhf7cjcCQr6sqNP-PJWkf_Cm3i3RWmyNma0y5Q01GPuBdd_m4eFlXIq5Ze8M7G1ev1KWo_84FilYwvNOHiZ1gmSOQvfHulP9l4u-rNOXQmnUC0A9EI5tZRy4PTEuozgnOrfuNDFWtibBoEmI_Nbj5Of-H7UBhdjbXnSY70JzPHDwOhV15-2qSG6Gr_ZkmZCYVyQ76VmFo9yA_0GPNutVIP1-XLuoqV2YOf8VA6Jvw5vh31lTiK_TSA8GuvzNuNDkSE4UMZrp1zG-0lyBKXtj85L0380fPlnEXJL8TX4TATX2iO8fFL7HPDklYz1rmYUenNR9I7enYVpElbwMobLpCnuR_ZKx3pmtc38w3cQ2kHJfgvCytLcjpmcErzqMrOsDSGMlcOkGpZnAO7gqicNfoCIUFqwHFLPad7RFH8epkajuuyQkrJxL7qU44n3zGItlWrJcsspLMsYdKM1yGX0H5pIDoB5A_5rNYENX_7Vu8_PHeqSr8gAOBGoi1o84eMUpA07eImzSFlrFKqmAYeCC34V5C2QBk0GduuX2i32CDx4SymiEoj67JSLflZdlMQ4vswZgE2kAB9DH06LiUzR-sHAH-uhyesX23D8cQZCho56gS7u4Pf-KUnEdWv4FAj850p1J_9FBOmvn5FZS99Y6nfIAH6t4694py5DoMsp5rX5ZT_A8MZJIiNr5bv-7hL15fGljcHJxPAPGvXitH9euHquYJNRMD-tbSHGCTk9pB4Ryf562rYRUKQ1Zol-3fCvI7A5um-0umTkZ5Ip7Xhi83QAjhCVu2eUJxyEMZXPJ6mq297v8sClPbB4UuSKIPmJjyQ1m_RD0WBLE2iA2g6fQmO4GzF-O-Q-WjQo9WOvSJDK7aaPT0-28Ma25gLoh5Q0sYWgjBj1AeDu3Y9rjRDN1Zv8Lyn4fyK8LoMIQaGjgYBKziAHdw4FOxz1UrKwwQM3gtGbxFu7uK59a7ok7CVqqaD_nUbTwHxgKRT0Q8R1Yha38I51xanbEEMXNKL8qc2xjqaaAdxayrIxhkJsCwikUpsiIFeKXmgnPEIgAy9oqVJQL4AbnrLAJ-f6Phv5LELceAKb7a_ueKnwa8-CLyRetVhQHkJCXe-0hRgdy_ltnLC-f3Y95iJCIbf52wzER1tq2qurUfYFbNiGwXSCjQBn5qr8dBM204cq2jOAdYpl2HfsQwD8M_GtuYz39-Axc_4ALO-JsN2ffqrKnyLEdQLFFbBxwPAlO_x4f3zAxjoONiZNX3FXG1l8tVjzkKMAojjwdu-qc-M8xtklaBUL_wxZH7mCURRIxmd6CbMLOKeZdrLvDZsPjAFYr5hFy0)

**Диаграмма компонентов (Components)**

[Диаграмма компонентов DeviceMicroservice](https://www.plantuml.com/plantuml/uml/fLTBRzj64BxhLspO72J0ij2YwA603nRjF0mvKaIbHKx4YbnBRFWGI2gsa1QmdRff4GD1WvuIj0MQ0htAgjt83ob_OVcFEhia9OgY94hMHTnTSkQRcMyUUqSp5RsjahQjewRhjfol2mKRxwytDBVPhhSTOYkMwHBJNLSiez3IiUcs3JtlkAgkrGlYb_aAstgoMoeOs7693LkoQ1ajosJVjE3TJEQEIlOqao3BmHNOIA57jB7J9AgXcMeXL92tMiHqYDAsDRU3RQMfkKHnspRv8g_1jqu087Cdi2XKLm0uXcNwMAOE9P1ImBeUG2rjFYq_gScbxNirkVhm_eDQDfV9RAhgjauC05V3ZQomXnMi4c4DtQqtH4ktx0tXipqLawy-PtjxO4QmMzUnyap8nRztNCl63Ix0QU8MsQXQRLCbQj4waDWwcukArhL6qvMUTOfwcyJaxpCNJCjNiOl1eyl8VqfqtTfF1v_5oRPqdTZ2b9o6JJgpfLI9pYLWfsCgx3MN70IltTsiLilVolVAZsgoM2wLgt2g0sScJgC7ajlHoSOMfqa9rjaS14e4jHWMTjPfuJKaI9LDK2FiuEVOKMojvR8LVKilw5cUzkd0EwONtg7t8snSUaU8lgTzxnKTq27zbmuHVKUxz8oz1syTqowYd-3asZkb7-6_HxliSrY_uQRWXeCsK4YEt1ZBLZshakUQGcIr3kgt-3FQOa723Wkf83qkJQpfh_GtvBtqZhnZxn1KNR8dsekg2JcKg0SooC2cAWC9nWf5GqKrgwKnnmkRkuyhNDCVO2-u0EmuzquHlG9jDs3rW9v7jOMCocMa3kIO8MyVk84o9kH372aT2idxtJK4yeUWx2Mzflt0IAwGUVGQS0mWBA_f1Q8zHCzzEDu9vt8B4fzi292viWDFiac0SdMhRJSjInMYuPUBZF7OxeGUCJJ5jg3618W3b-n6TeKSUf51y1iLhwHlrtLBmQvccV8u2q1UATWLewaXx7uZeYfnN3IvJJ_GJ-2KxiZUFhEs2pw1C7X7y7H3-r7lHn8kbmBYY0GHWBx1gyER9kEjOJeACR6jMPD0fM0t7TJVWHiZN43UBeBbJnnr5pO1ER286tD6X_3UwrlW3PFgEo2SYVL9sA9_kEEVJQBa-LPv23wCEzPxW-2H5Pi-pxnBv6S380L_iuoOLN9EuVWA19pCJDa9wvOXzZXBGbPBGOR0LX5gEJ7Lz3HUWh3nz5n2xKnwBaN4CO2GXJCWB4-xLGWsXZF9hXcWte-8rBqjZNAP7nABANHhEK2EMvhSm2xPnnrG3ks0C6WeAAoCxlVzquIwcYnc7IOCkq5CfJFgNnCfjXqU1zv8JZ9e6T2GmG6ylP_m40t67Rq7hOMxWlNqC0KZfYCMGfQfvx0UTIFmJnB3_BOqrVXJ6HinqMaRqHeY1Zln-Fu5L7h5emjMdB0UoXvzSnaVamrckJ9at0JG_1NOxiui6glPC4NK50mD4Do0gmV-5e3iiua0nh0UdxX-vYSteoaXCRs1g31L-kDU-dxDcJ849h3HnzTycTI21JQehg4TgVoeyA1MgqYWSQwwfDwRg0pCdDDJbzQRt4ovvaMPFrswLW4mjplw7W3DOUK6pN4E9cdaAbD7DMLN2on96RrK_MgEoCJ0fEn2SyJES_TolIKDzj7RouccR-FLrfyGUi04Vwu_vlUjYrbTQ77yuyZu3OjV-RQAuk98h_hvJC_41QGEO9AWT5WMIPgy3D8_eUAUiRiNlO8dkDSYFY8CwPdtoxWbyXemlgO7xS7VM07AQXqVvgmBNZROZFnVU2sCK8JFyrE50ttF5_bgPHI1r4qusMV_W5FO9PLv2nptSQ5pKkjBtLdd2vxlo6MK5BKK-6CKOovwbzJE-I0MQ-T9RGy-NKFftRTKEPfarD-Szp08UOSZzez7vSNpOHh6_Gy0)

[Диаграмма компонентов TelemetryMicroservice](https://www.plantuml.com/plantuml/uml/jLPHQnj747xNhrXVyo21RT5I-X3mWomxJe8SAJgb9K_7wcuj7JdT7djxiKKfM7RS11e8BOL2Kn987legkpPLarh-2tl_g3Dx9-aioQtbK8EGTdRtcszclfdraklRNkImI7YaBMKGtYuM1TrTRRco7JMZa0cR-vBvSjNcdMBWKLz67Mybb8xdDelbBrTgQ7gqNIbsQ2YP09DLvfs0-tWdWBEvt9B3TboV4HxI6XYkuQSiQDXcJiVrdUB8mGeFc1yoEnAkx59Xjrt9R1a9ziMA2tV3b41kAOrevAu8T3hSJxZCR9g0KgIUbrAjb1vN7pMiokQTXbM_jtMtaI_aSYN7sVHO1yWrQ2jlx52REinO9hUQhJBtk5WpFjjnAFlgSxJjG1YfjUbH-ubHcBuVIYveImE4RHgmjJgFV8SvwtpFn7M-a8Nst5PRca-wwrx4fl1tCGMp-0wL53Aw2FvZvdbyzthqiKw2UnuJnWnEIx3krIXrvca46dPz6uz9jfSUkrMgrwlVM7Ug3nfMkLgfrc7N0yrSsirkcBBhiRKDBPCAhFC5A5GPt59OY7mOq6LYcBKIk37kqwSqj8KRI5ofdzIPEbvH0tKU7wYpU3z-1eQ_uXvHhzKWVgxErL3zeOP4_QhwwXZFmQa3rIVg0dO-nY_Ld_1zelfu7TQlT2Yq5P8rCX97OS9bevcN3FCdHTVYdWmifmaq6YCRgLOQDRA1HQCXbjWm7rOkhTMFwcUYtg4p-FoDRE9zu3N8EXwfwYhFe8XBNXiySB48HcdxOKrxUGEnGph0nMdyagXJYFuSSlH3V4GWEV5XtDD-XvWh2FqawtsakKBEx48NTgpDFPbts5FNPWZzVlgk6XhpxVrb0X4EWS8XH3j8tMegc7DCW2QbpeWwGP93EDwFZxJQ0nWDRCs0thP2-6Nv34JPv95eS-vaCvCFfM0KnalBwhYsu31-KghcQ8DiPpQGx1RNb7-3n7z0od5FP-i4D0FIWIyWSmPnPNLehMFhKT7D56BAuQGYbnmMoBSv0d_ZMNe5mkh4F6dCB6mvswx6zjTbKcUX99VDwhswW5ZwummFCBzZ1K2A1_-jWSnWADo0UnXrPfXh6n9SXo72V4TpVGUvVwu-O4EUn4Ue5VnvYfr8G0eztTb7ckNvf9alTCs-XvhraYDWxY-eyd5pBnZTd6gR4s5bOxjn7Ggvx-Rg3YQibVIXHGFNQb79TcaNN463Ck5JZspeNLAgtIDRoUuSySs7MKrDsFre1zW43QSN04v7zreNOv3K3jjsFtu1dqE8O013jG_bWQbsqw900k25IKR_Ois2iQV3_H2782OUnNGT7WO-PilalbbzKBpRQDHCO3AcSUMOKRy0j2vr_09YXhc7KtrgZ23CGe4i8CIKvGAD-J-0Zs_CUo3roxsnNluKficZftw7SXwCKWnAoyed-MSapVRenRgstDLRV0YWCp-Eu2VDUWRrJx0H-hgGLqlc3SGmdWxngyGTg1RCGsWfU1wJPp73OBvgzUHSaDPCJj_20tsCJpIyXeSpeV-Rupd9_mS0)

**Диаграмма кода (Code)**

Добавьте одну диаграмму или несколько.

# Задание 3. Разработка ER-диаграммы

_К началу этого подзадания вы определили ключевые микросервисы и спроектировали их взаимодействие. А ещё визуализировали полученную архитектуру с помощью диаграмм C4, что позволило вам получить чёткое представление о структуре и взаимодействиях в системе.
Необходимо определить ключевые сущности и смоделировать их взаимосвязи, чтобы создать логическую модель базы данных._

[ER-диаграмма Device Database](https://www.plantuml.com/plantuml/uml/hLJBJiCm4BpdAwmUA-K7KDKB227WLZX7stX5NM8xifsAgkNVyRF43gbw82TxTtOzivvOVpBHWm9EEYRG77iaKfcTh2eKXfaZMRnhL0i2cWnsGKuLiT_IhHj6oHqH0z_QUFNzM_601zQYhw5-cQh2BgjRhJvGe0A3TBtsgBhEm8vIKV7u4g7BJhPWc1Ip04kJGTV4J8A471o0TR1bdINTQ0Dcq1QnbRBpUOgwLQntxMtOu9VnOGV4k9zeVhDaHxez-K9ygIVJB80ZcJP_ZJ9zwwGvzPsPGJdMfWdmdRHtDpUlaUOfYoTNwIdu7kdylnhE7JwnpUXie5IXrgDHNsMpQL60Oj8Jqd6JEANzVypaqepSIYruuLvo3e8-oq49wCQkQ4EcsOTKOP0oU_klCYSWThJ9lPOYcd-mF_diNL1xY67S-Pnni9RdlHCuTlNvNDVo52LL_cs88J_DAdWaXf97ysWQR7MnTDuqFXvPi1nFTl8Cv7A1rs4_)

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