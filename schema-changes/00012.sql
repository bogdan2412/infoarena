create table cookie (
  id int not null auto_increment,
  string varchar(50) not null,
  userId int not null,
  createDate int not null,
  primary key (id),
  unique key (string)
);
