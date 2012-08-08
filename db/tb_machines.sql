drop table if exists machines;

create table machines 
(
	id        int not null auto_increment,
	name      varchar(255),
	location  varchar(255),
	mac       varchar(17),
	
	primary key (id),
	constraint machines_mac unique (mac)
) ENGINE = InnoDB; 
