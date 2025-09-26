CREATE DATABASE device_db;

\c device_db;

CREATE TABLE IF NOT EXISTS device (
    device_id SERIAL PRIMARY KEY,
    serial_number integer NOT NULL,
    device_type_id smallint NOT NULL,
    user_id integer NOT NULL,
    location_id smallint NOT NULL,
    description varchar(1000),
    created_datetime timestamp NOT NULL default now(),
    healthcheck_datetime timestamp,
    healthcheck_status boolean NOT NULL default false
);

CREATE UNIQUE INDEX uq_device_serial_number ON device (serial_number);
