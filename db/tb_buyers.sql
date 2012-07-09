drop table if exists products;

create table products 
(
	id    int not null auto_increment,
	name	varchar(255),
	email	varchar(255),
  
	primary key (id)
) ENGINE = InnoDB; 
