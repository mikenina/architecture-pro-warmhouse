[Назад](../../README.md)
```puml
@startuml
!includeurl https://raw.githubusercontent.com/plantuml-stdlib/C4-PlantUML/master/C4_Container.puml

!define osaPuml https://raw.githubusercontent.com/Crashedmind/PlantUML-opensecurityarchitecture2-icons/master
!include osaPuml/Common.puml
!include osaPuml/User/all.puml
!include osaPuml/Misc/all.puml
!include osaPuml/Site/all.puml

AddElementTag("facade", $bgColor="#fdae61", $fontColor="white")
AddElementTag("storage", $shape=RoundedBoxShape(), $bgColor="lightSkyBlue", $fontColor="white")
AddElementTag("databus", $shape=RoundedBoxShape(), $bgColor="lightYellow", $fontColor="black")
AddRelTag("databus", $lineStyle = DashedLine())

Person_Ext(user, "Пользователь", "", $sprite="osa_user_blue")
System_Ext(device, "Устройство", "Устройства, модули установленные в домах", $sprite="osa_site_neighbourhood")
System_Ext(video_hosting, "Видео-хостинг", "Облачное хранилище", $sprite="osa_cloud")

Container_Boundary(smarthouse_system, "SaaS Умный дом") {
    Container(spa, "SPA", "Javascript", "Веб-интерфейс Личного Кабинета пользователя", $tags = "facade")
    Container(customer_microservice, "Customer Microservice", "Go", "Микросервис управления пользователями")
    Container(device_microservice, "Device Microservice", "Go", "Микросервис управления устройствами")
    Container(telemetry_microservice, "Telemetry Microservice", "Go", "Микросервис Телеметрии")
    Container(telemetry_olap_microservice, "Telemetry OLAP Microservice", "Go", "Микросервис аналитики Телеметрии")
    Container(streaming_microservice, "Streaming Microservice", "Go", "Микросервис видео")
    Container(device_api_gateway, "Device API Gateway", "Go", "Фасад для взаимодействия с подключенными устройствами (аутентификация устройств, перенаправление потоков данных)", $tags = "facade")

    ContainerDb(customer_db, "Customer Database", "Postgresql", "БД пользователей", $tags = "storage")
    ContainerDb(device_db, "Device Database", "Postgresql", "БД устройств", $tags = "storage")
    ContainerDb(telemetry_olap_db, "Telemetry OLAP Database", "", "БД аналитики Телеметрии", $tags = "storage")
    ContainerDb(telemetry_oltp_db, "Telemetry OLTP Database", "", "БД Телеметрии", $tags = "storage")

    ContainerDb(telemetry_bus, "Telemetry Topic", "AMQP", "Очередь данных с устройств", $tags = "databus")
    ContainerDb(device_command_bus, "Device Cmd Topic", "AMQP", "Очередь команд", $tags = "databus")
}

Rel(user, spa, "Регистрирует учетку, логинится, управляет устройствами, просматривает данные и видео")

Rel(spa, customer_microservice, "Оперирует пользователями: регистрация и логин, запрос данных текущего пользователя", "HTTP")
Rel(spa, device_microservice, "Оперирует устройствами: подключение нового, запрос списка устройств, конфигурация", "HTTP")
Rel(spa, telemetry_microservice, "Запрашивает данные телеметрии", "HTTP")
Rel(spa, telemetry_olap_microservice, "Запрашивает аналитику телеметрии", "HTTP")
Rel(spa, streaming_microservice, "Запрашивает видео")

Rel(device, device_api_gateway, "Отправляет данные в")
Rel(device_api_gateway, telemetry_bus, "Публикует данные с устройства в", $tags = "databus")
Rel(device_api_gateway, streaming_microservice, "Отправляет потоковые данные с устройства в")
Rel(device_api_gateway, device_command_bus, "Читает сообщения из", $tags = "databus")
Rel(device_api_gateway, device, "Отправляет команды на")
Rel(device_api_gateway, device_microservice, "Запрашивает активные устройства")

Rel(customer_microservice, customer_db, "Сохраняет\получает данные учетной записи пользователя")

Rel(device_microservice, device_command_bus, "Публикует сообщения в", $tags = "databus")
Rel(device_microservice, device_db, "Сохраняет\получает конфигурации устройств")

Rel(telemetry_microservice, telemetry_bus, "Читает сообщения из", $tags = "databus")
Rel(telemetry_olap_microservice, telemetry_bus, "Читает сообщения из", $tags = "databus")
Rel(telemetry_microservice, telemetry_oltp_db, "Сохраняет\получает данные телеметрии")
Rel(telemetry_olap_microservice, telemetry_olap_db, "Сохраняет\получает аналитику телеметрии")

Rel(streaming_microservice, video_hosting, "Перенаправляет потоковые данные")


@enduml
```