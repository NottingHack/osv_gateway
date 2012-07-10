drop table if exists machines;

create table machines 
(
	id        int not null auto_increment,
	location  varchar(255),
	
	primary key (id)
) ENGINE = InnoDB; 
