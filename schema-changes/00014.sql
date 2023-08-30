rename table ia_task_view_sources to task_peep;

alter table task_peep
  drop primary key;

alter table task_peep
  add id int not null auto_increment first,
  add primary key(id),
  add unique key(user_id, task_id);
