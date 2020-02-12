create table workday
(
        id int auto_increment
                primary key,
        profile_id int not null,
        date_start datetime null,
        date_stop datetime null
);
create table workday_pause
(
        id int auto_increment
                primary key,
        workday_id int not null,
        date_start datetime null,
        date_stop datetime null
);

create table profile
(
        id int auto_increment
                primary key,
        login varchar(255) not null,
        name varchar(255) null,
        last_name varchar(255) null,
    offset   varchar(10) null
);

create table lateness
(
        id int auto_increment
        primary key,
        profile_id int not null,
        date date not null          
);
