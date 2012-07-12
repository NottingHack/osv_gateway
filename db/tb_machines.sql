drop table if exists machines;

create table machines 
(
	id        int not null auto_increment,
	name      varchar(255),
	location  varchar(255),
	
	primary key (id)
) ENGINE = InnoDB; 
