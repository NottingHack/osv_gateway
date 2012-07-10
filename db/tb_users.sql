drop table if exists users;

create table users 
(
	id        int not null auto_increment,
	name      varchar(255),
	email     varchar(255),
	password  varchar(160),-- SHA-1 hash
	
	primary key (id)
) ENGINE = InnoDB; 
