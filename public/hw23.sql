create table project_logs
(
    id int auto_increment,
    project_id int null,
    project_name varchar(255) null,
    action enum('insert', 'update', 'delete') not null,
    content json not null,
    constraint table_name_pk
        primary key (id)
)

CREATE TRIGGER `project_insert` AFTER INSERT ON projects
    FOR EACH ROW BEGIN
    INSERT INTO project_logs
    (project_id, project_name, action, content)
    VALUES(NEW.id, NEW.name, 'insert', JSON_OBJECT('name', NEW.name, 'user_id', NEW.user_id));
end;

CREATE TRIGGER `project_update` BEFORE UPDATE ON projects
    FOR EACH ROW BEGIN
    INSERT INTO project_logs
    (project_id, project_name, action, content)
    VALUES(OLD.id, OLD.name, 'update', JSON_OBJECT('name', OLD.name, 'user_id', OLD.user_id));
end;

CREATE TRIGGER `project_delete` BEFORE DELETE ON projects
    FOR EACH ROW BEGIN
    INSERT INTO project_logs
    (project_id, project_name, action, content)
    VALUES(OLD.id, OLD.name, 'delete', JSON_OBJECT('name', OLD.name, 'user_id', OLD.user_id));
end;

INSERT INTO projects (name, user_id, created_at, updated_at)
VALUES ('project1test', 5, now(), now());

UPDATE projects SET name = 'project1t' WHERE name = 'project1test';

DELETE FROM projects WHERE name = 'project1t';
