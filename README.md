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
    - Отображение температуры пользователю 

### **4. Проблемы монолитного решения**

- Масштабируемость: ограничена, так как монолит сложно масштабировать по частям. При увеличении числа пользователей и устройств, в первую очередь, потребуется отмасштабировать узел Телеметрии.
- Разработка: монолит имеет высокий риск ошибок из-за высокой связанности кода; при изменениях в одном узле необходимо тестировать все приложение.
- Развертывание: требует остановки всего приложения.

### 5. Визуализация контекста системы — диаграмма С4

![system_context](./schemas/context/warmhouse_monolith_context-System_Context_Diagram_for_WarmHouse.png)

[Диаграмма контекста ТеплыйДом](https://www.plantuml.com/plantuml/uml/RLBBJjj05DtxAwPP9LA8Dwowgw1I5z14BGXr5PbaIYpv4TbZ7hkGg0-9gWYf-whb1zA0XI7W_CATF-gvOnUSafN7TyuzvznpvuKRzBoaBvF84mEbXl5BsuwSezQ1gmR9VXBBg1S6IWQgrGjzU-WvWKfyRorMVS_TjpVMrpfSsjtUidqdLZ92gRl17SUgDGJIihPUVMZlxdHtsftkPdllRRrXMSfLdXJlJz3WYn8jDbtd879yyJ6Cn9uJ-Mz2g5jMHqPn67HV7wiwkscA6lsadERw6ztWEwEf7bFA_xL65IpzeHUUICaDlsX1gVvC6KrfJfa-rME1KYxqXC-eFXYgEqe53dyeroCzeUk27vUPFWTxOTYmz-MXspFSbyoWHu3U6guPvRNLzQceWn9szHSukQEiAN0FwvGs_dU26auF-emTpOInSe_UJuyswaV8QC0P5HEQgT05bA3VE2oGoHdTqWzkhJq7cG6HXxvW9O2kJ22qWAdfeuNCH8Acd0d_bUY-uYg59-kTzChzBFcwVABJvzZ1U2LPKnXOha_Da1nRPfBdXj8mRt1aBYUy76YoP4Miif9deaMworioiZU06R3-Mf4qmPJnbq9FpsbfFdwArzZbN7_7LilkOeKvabkb2yA_)

# Задание 2. Проектирование микросервисной архитектуры

_Вы провели анализ текущего монолитного приложения, определили его функциональные блоки и выделили основные домены. Теперь ваша задача — спроектировать высокоуровневую архитектуру новой экосистемы, основанную на микросервисах. Для этого вам нужно определить ключевые микросервисы, спроектировать их взаимодействие, а также визуализировать полученную архитектуру с помощью диаграмм C4._

**Диаграмма контейнеров (Containers)**

[Диаграмма контейнеров](https://www.plantuml.com/plantuml/uml/hLXTQzjM6Bxthr2rNjYGr6oCNHHouJXfsk4iRkmo2WDnB9tOelemajpKb46JB6l7oih6OD2DTbi7ktK-l2XVxbyu-aTxtoF9jkGZLnvhAOdEaSxpl5_F-vxUq2tLw6cqvnXIn_EwxkrosI6xjzgwr-crUYvrLDlogENTKcspt3M8vVLCOzdrD4DlbQkVBjTnwS5chMmIrwCEB2bL-8Be5dLkTU7TGk66HdVWKR9TKeU573XLXxWTgfcwfPLZW6MxIosNgZr7z_h4KJkwHrMlvz1FbdNureq856v45iLmvQfjchOLSfdPV038PM8O6TkRkglEsMu0XybseLPvkFMWgTJMxpILxNiRTvl5Kg5GqRHrWvfWMfEqY_8EKOb6vINfPgjTjGtRMP4_sj48_UnZNDi19qIhBOEeZ-HI-dlNintIvWUu7TAbAzjspzAejce_QU1piJHzjA6tErxZKN_LwD7K-RlemDdpDU8HYCSYvp-aXc7lPjFVfWO_chXzIyNNFFeaUkrcPNjxwqlbpjONJQMwLTlQXbq3SYMnExtHyFe6NLdZwL63vs89N5odZcjRoleJhuYPj2J9x2qRiSlW9Jk3dyTi4Eop8Ju3kinDwq8MqHKP0gdW9qeB7LGgDFgGGoO_IQEFTPNYMU-2lM0_U0OddUDlSDv85gyFbYHs1S-dmG6xPBuK7F2D0RlcD2w1n3Mx3hvdGuaTI-mKLg-0t66QaWk_AHO5vxRidjEnRIt9xR6kKLlfsAwdMssayXFpuRGX6oq7Xsp4GNs0Ek4ytx0ZW1u4ppcDeHGS0cdav2F9u3isJEEhXjr3oCAub9LLJ0FYz8kkIHolOuFN59Sp0eW68Gs9lGDZqBZpo3Av93qjIF1dV4hHxH9ylLv1Of-JnyHL7RthoQ4DGtQqZBmnMi6pu1jO0CzAx5TW6P8_OIE9lGRoHzo-8JfNOk-5mNx5KusqNMb5YikkbEAZzg2UJ33Fr5N7XaI8ObwDrgNDgNLakM5phhy1_0M6FjX3ge3g0r68-dlksZZKVl0ganquov_X4oPTciqQN_qVk8Jvc4nP8G-FeYHuJZzDfHblBCQ6_Ob6mxyhZ1ai-tD1RODqiv6tQfNwWl2OxGEUxLWO5r2QkHYvdaC9TA5scamZtbYGnt5SfpDG88bA57tIrPKsyUWkwSDHKE_KiOWXHLbGgT-JDiBTARo_8B7smCHJhBrBJBjZIBW1m74z2Yk91vpdmLwObgVWYilW1oYkM9YkngercoLI4K0EUFvYYVfGeEZBGV1jHd8j8GfQF-JkJ-KZYY2GW1T7SCee5CH1o2CuB0chD-cqjTQaWBNMTDckOHSZBez8utujySn-P3zdL2MwQHetRhQb6TmeL1mr2im7CMSTb0yhLHSSCrKDqrWJ6tDc_S8aF069PXQ9t8Xp864WIS0rxQwEauHSsRnV3tiSvZ3FjDFWPIAH92w6ypmVZpoPKOQHr2IM5j68mbqrjG_pk0WxF70v5ID-NIZ0U1IDBM5tPB_3roUe5w6_e1CUeE_GYkUyvLq41r1QbxmV-bpQz-7bLqi9qOSE4tqaB6QipKYVS0G98uDx-D74VrYaVbAtE65EDQjxmiGHrls4-xmkU5lY3fkO72iA8e-j1C9daOYCKByI8SRJm2iuouGpGlP88DzjDkjWnjWASSyLs93bojiYCUNo5evzV6n9qGUtlqTxKJ_5oecfSyqbzWJsnquHMP3PhTaliUm6BxB2ErQ-P87E0H5rvooab0G51xdnCdjl5jHCZcQrrBIkiBSGO1oHEK61X_Q4ePUIAXBURafpaRDjUWDdfaitsHt3Mip9QpuDeQonltcG8cVwx4natAh-uNFnhDTSzwx0czV96O7FG3Yul-0xLo8B-DKX3666mYIxFli3_1JTQKBNVHNLFmgcAELZ8SNwF8yAarUZdwqS4IEXMeWTEfjMW322lqTSfwBhGMOIpKFARxTGHeH3NcnXbfhCb4sUX4aUA52E_tHgc8P21sNEqN2_745GSHp-1mFFpN-98S4Rvo7tn7MS4HUY_EKNX7mUcin8YoVdll1Yie0Z9rDYBl2S4Xqt_Wm9c_alYRUYoJ-ViicbVm40)

**Диаграмма компонентов (Components)**

[Диаграмма компонентов DeviceMicroservice](https://www.plantuml.com/plantuml/uml/fLTBRzj64BxhLspO72J0ij2YwA603nRjF0mvKaIbHKx4YbnBRFWGI2gsa1QmdRff4GD1WvuIj0MQ0htAgjt83ob_OVcFEhia9OgY94hMHTnTSkQRcMyUUqSp5RsjahQjewRhjfol2mKRxwytDBVPhhSTOYkMwHBJNLSiez3IiUcs3JtlkAgkrGlYb_aAstgoMoeOs7693LkoQ1ajosJVjE3TJEQEIlOqao3BmHNOIA57jB7J9AgXcMeXL92tMiHqYDAsDRU3RQMfkKHnspRv8g_1jqu087Cdi2XKLm0uXcNwMAOE9P1ImBeUG2rjFYq_gScbxNirkVhm_eDQDfV9RAhgjauC05V3ZQomXnMi4c4DtQqtH4ktx0tXipqLawy-PtjxO4QmMzUnyap8nRztNCl63Ix0QU8MsQXQRLCbQj4waDWwcukArhL6qvMUTOfwcyJaxpCNJCjNiOl1eyl8VqfqtTfF1v_5oRPqdTZ2b9o6JJgpfLI9pYLWfsCgx3MN70IltTsiLilVolVAZsgoM2wLgt2g0sScJgC7ajlHoSOMfqa9rjaS14e4jHWMTjPfuJKaI9LDK2FiuEVOKMojvR8LVKilw5cUzkd0EwONtg7t8snSUaU8lgTzxnKTq27zbmuHVKUxz8oz1syTqowYd-3asZkb7-6_HxliSrY_uQRWXeCsK4YEt1ZBLZshakUQGcIr3kgt-3FQOa723Wkf83qkJQpfh_GtvBtqZhnZxn1KNR8dsekg2JcKg0SooC2cAWC9nWf5GqKrgwKnnmkRkuyhNDCVO2-u0EmuzquHlG9jDs3rW9v7jOMCocMa3kIO8MyVk84o9kH372aT2idxtJK4yeUWx2Mzflt0IAwGUVGQS0mWBA_f1Q8zHCzzEDu9vt8B4fzi292viWDFiac0SdMhRJSjInMYuPUBZF7OxeGUCJJ5jg3618W3b-n6TeKSUf51y1iLhwHlrtLBmQvccV8u2q1UATWLewaXx7uZeYfnN3IvJJ_GJ-2KxiZUFhEs2pw1C7X7y7H3-r7lHn8kbmBYY0GHWBx1gyER9kEjOJeACR6jMPD0fM0t7TJVWHiZN43UBeBbJnnr5pO1ER286tD6X_3UwrlW3PFgEo2SYVL9sA9_kEEVJQBa-LPv23wCEzPxW-2H5Pi-pxnBv6S380L_iuoOLN9EuVWA19pCJDa9wvOXzZXBGbPBGOR0LX5gEJ7Lz3HUWh3nz5n2xKnwBaN4CO2GXJCWB4-xLGWsXZF9hXcWte-8rBqjZNAP7nABANHhEK2EMvhSm2xPnnrG3ks0C6WeAAoCxlVzquIwcYnc7IOCkq5CfJFgNnCfjXqU1zv8JZ9e6T2GmG6ylP_m40t67Rq7hOMxWlNqC0KZfYCMGfQfvx0UTIFmJnB3_BOqrVXJ6HinqMaRqHeY1Zln-Fu5L7h5emjMdB0UoXvzSnaVamrckJ9at0JG_1NOxiui6glPC4NK50mD4Do0gmV-5e3iiua0nh0UdxX-vYSteoaXCRs1g31L-kDU-dxDcJ849h3HnzTycTI21JQehg4TgVoeyA1MgqYWSQwwfDwRg0pCdDDJbzQRt4ovvaMPFrswLW4mjplw7W3DOUK6pN4E9cdaAbD7DMLN2on96RrK_MgEoCJ0fEn2SyJES_TolIKDzj7RouccR-FLrfyGUi04Vwu_vlUjYrbTQ77yuyZu3OjV-RQAuk98h_hvJC_41QGEO9AWT5WMIPgy3D8_eUAUiRiNlO8dkDSYFY8CwPdtoxWbyXemlgO7xS7VM07AQXqVvgmBNZROZFnVU2sCK8JFyrE50ttF5_bgPHI1r4qusMV_W5FO9PLv2nptSQ5pKkjBtLdd2vxlo6MK5BKK-6CKOovwbzJE-I0MQ-T9RGy-NKFftRTKEPfarD-Szp08UOSZzez7vSNpOHh6_Gy0)

[Диаграмма компонентов TelemetryMicroservice](https://www.plantuml.com/plantuml/uml/jLRTQzjM57_tNt6ZzS6696PZx67W1zj9qnQdTYrteq_YMhgnHML9I5TDp1ZaOraxLWWRWy4wKcWVzkfuCVFQnVaNhlwZdNCbsVBN5gURmTZtdAjpVh_pgJNJrgt0O85daJRdhlzvFk_H_SsMoTj1C_2PfpisPpRVr9rEthMepOEEjU5pmpAR-VAd6pKKFTwjv3lKvym3aLPsEgvZupCktCrarWosPzgCE3wjWU06VieUzTlCw9YsaKySR3Wkitsc1vx9kzJJsoPdEWyyzic62S_wCO3CMimeSPS7E1t7Zh3CALMmagUM5KEj59zK7pUqolRTXbQ_ltElaSrbCaN3sBPO1y0rQ2khx56T6anP9tUQhR9ZELv1-MZFeEopZr6s1pHYQTEY-bCbD_kypnsFjgG1lqrTLgWxWMqme-GSg7ZEvjAcBRFLvkhJRiaAs8pzVGp1l7s3SWeHNSN-4sPPplxDu6EUFCUocAVCsMbvhBlSIfrPqWBrkxQErpWxYA_TATRhrI-rkzM73QrShLJheBMWPgQqQONAknOhRCaogS0vcuD4bS4jXOENzLswJXIrLWGtoWFwZFgwPxeSJ-97CH0N6s8ehi9ZCGWFmsz0y6TuHCGhCGoVYoin4h-B4H6_Y9wum7jmwrZqYBW6pVlmfVW3VlkYXu_3-KnIeIsV54XI7Ba9bgrcbZEC7_UwccDnLpEQ0AEHo4YrqgYHBKmQzJ75YlgeCdKMFuYVY7Y3pk3x5w893m7NCEqugQfbdg4Yfhms7DV492Z5tKSrwUKSk4CumCLb-9A8Is1_1J7wFZmb49pm93oIVaSOAw3UJtjFQYwNKRlGXHrj-u1d3VRCr1cQVZlxh1WfY-MzTG8CHm3X1DWEOxSI2iOS0o11YG4HVGGvWkkzy5HMkmkZWHKKw6tDXt-QpQ0ecqxWjHt7I4ScwtEFKHWlBQrZwfu3uoU6gYOAifjI8DWTHqB-5GB_3Y67Hp9QVQWPA1tu0J03u9MkGws4hKUzRYeHCmud6Pboc2DVPGXynhDqYONDYNZIc6cpvNIxAhjVb4cT-PnCYyLRSGrSUkC83p6-umg040__lGPIWo5t2-n-q9b3BcK8i0H3XDc6nFe6OlzSlCE6x8UdM2luzn8xaK0f7CdEFfKehoRDV2rpzYtax2Yw0kBUYbK-RkuLsIt8jZefh3ItM-SXb_bwOTt1XDMYFjIeQsejoja-xO8hQ53csTGYMr9BYhNxP2VIBYY-nMOsOn5sFyh1R6GDfnSOd6NtIYPZ6EKEs_OmV07V4s0mXA7QW_J0LFjlah84CGru6zIc3kT4FqbFCQMDc2p9qnIJdcGmaFja2Dj4YXCkVRa-Nc0kWGqE2xWraci6xusYAJRc6oKIDcMqmbPhUinXlAHES9XBfsStYwU2ItcTF52h3_Ft6esQ2aZ6C9QEI_4pc9Ob2nI7EBznEysCGpIp4f4L6Yf6kSA0-H-CZvzOjEZb6yXMgVmXIAFbBNw3T1ud8OQEIRT1z58LHplPl3TkM_4Q5nfCcFU9-SdG6K3-8zje-gOcbvVCEN0OJxdmB787LOjz4NL4jDvJ21PNhTm0AyAQY-bhUD6um5SDsEedSzJ_oV62uA-2v5O3IfoB2zbjtqLv0ZnduFTl)

# Задание 3. Разработка ER-диаграммы

_К началу этого подзадания вы определили ключевые микросервисы и спроектировали их взаимодействие. А ещё визуализировали полученную архитектуру с помощью диаграмм C4, что позволило вам получить чёткое представление о структуре и взаимодействиях в системе.
Необходимо определить ключевые сущности и смоделировать их взаимосвязи, чтобы создать логическую модель базы данных._

[ER-диаграмма Device Database](https://www.plantuml.com/plantuml/uml/hLJBRi8m4BpdAroEIFc12d5fLLLjRotdQB6tZI6n8zkfGD1_h-D7S091L2e7PC-kPyVZtUWj5mqeg4d51PfzWqGgKyeiGs6usPFPIgEQ4T2ajGjoo8ZzpRjrmHcv9wAjrnPVB2p2eIhyVhbqUNcU_PoO7l6RKtHSp2qZcz_TnfSUwEIEJZszXjHv9IaOBiLamg10chAAAGmrLRpfM6pSuCuuc2e4WwnWzj_m6kz9NBbmYL2PafP8jxUaQ0EcrPP_BMLrRjU7lMDYcRlpoBUn3GEh1DIOYcy5DtQlZUBYAott5lnonQ9viTA4cPVUg2ZyaLBQ9faoFt4sQ4kKF7SddGp74JLS8hmcu4AFzlM1COLQ9xvSLVHEKO3YqedIOHDrnVr_UTCJablbFSYw1i5UPAi4L4uazL2nyLZKEeB_Fd9JcWEn3Mwq51u3QgHotTdtMuAVCv6xF8-F9XL9VMNOe62MWTYUFF66scVpQF_1pbNT-0JS8MrT-_fX6yWv1HlOLn9OSeLJx9CBhik98lOfqkay7lDS7iB0Pkwp6I3txfdlwW35oHgYyUvPFuX3ql3z3E3eLGSbnx6-kIRYlm00)

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

_Необходимо создать новые микросервисы и обеспечить их интеграции с существующим монолитом для плавного перехода к микросервисной архитектуре._

```bash
  cd ./apps/task_6
```