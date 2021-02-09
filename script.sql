create table if not exists entity
(
    id      int                      not null,
    field1  varchar(100) default '0' not null,
    field2  varchar(100) default '0' not null,
    safedel tinyint(1)   default 0   not null,
    constraint entity_id_uindex
        unique (id)
);

alter table entity
    add primary key (id);

create table if not exists users
(
    id       int auto_increment
        primary key,
    login    varchar(20)  not null,
    password varchar(120) not null,
    token    varchar(250) null,
    constraint users_login_uindex
        unique (login)
);

create table if not exists users_entities
(
    userid   int not null,
    entityid int not null,
    primary key (userid, entityid)
);


