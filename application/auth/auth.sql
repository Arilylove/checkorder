#管理员用户
create table admins(
uid int PRIMARY KEY auto_increment,
username varchar(100) not null,
password varchar(100) not null,
create_time varchar(100) not null,
email varchar(100) not null,
phone varchar(100) not null,
de_id int not null,                            #部门id
po_id int not null,                            #职位id ,由此赋予权限
role_id int not null,                          #角色id
status tinyint default '1'                     #正常状态
);

#职位表(根据职位赋予角色权限)
create table positions(
po_id int PRIMARY KEY auto_increment,
pid int not null,                              #父级职位id
po_name varchar(100) not null,                 #职位名
create_time varchar(100) not null,
role_id int not null                           #角色id,包括权限
);

#角色-菜单
create table role_way(
id int PRIMARY KEY auto_increment,
role_id int not null,
wid text not null,                              #方法(操作权限),id组合
create_time varchar(100) not null
);

#角色
create table roles(
role_id int PRIMARY KEY auto_increment,
role_name varchar(100) not null,
remark varchar(255) not null,                   #标签,备注
wid text not null,
create_time varchar(100) not null,
status tinyint default '1'                      #正常状态
);


#方法/菜单
create table ways(
wid int PRIMARY KEY auto_increment,
w_name varchar(100) not null,
pid int not null,
w_control varchar(100) not null,                #操作名(controller名)
w_way varchar(100) not null,                     #方法名(function名)
url varchar(100) not null,
create_time varchar(100) not null,
status tinyint default '1'
);


#部门
create table depts(
de_id int PRIMARY KEY auto_increment,
dept_name varchar(100) not null,                  #部门名称
description varchar(255) not null,
create_time varchar(100) not null
);