alter table ia_user
  add banned boolean not null default false after security_level;
