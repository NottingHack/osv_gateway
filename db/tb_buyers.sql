drop table if exists buyers;

create table buyers
(
	id    int not null auto_increment,
	name  varchar(255),
	email varchar(255),
  
	primary key (id),
	constraint buyers_email unique (email)
) ENGINE = InnoDB; 
