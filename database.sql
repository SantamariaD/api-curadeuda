CREATE DATABASE IF NOT  EXISTS base_curadeuda;
USE base_curadeuda;

/*INICIO TABLA  USUARIOS*/
CREATE TABLE usuarios(
    id              int(255) auto_increment not null,
    nombre          varchar(50) NOT NULL,
    email           varchar(50) NOT NULL,
    contrasena      varchar(20) NOT NULL,
    created_at      datetime DEFAULT NULL,
    updated_at      datetime DEFAULT NULL,
    CONSTRAINT pk_usuarios PRIMARY KEY(id)
)ENGINE=InnoDb;
/*FIN TABLA  USUARIOS*/

/*INICIO TABLA  POKEMONS*/
CREATE TABLE pokemons(
    id              int(255) auto_increment not null,
    nombre          varchar(50) NOT NULL,
    imagen           varchar(50) NOT NULL,
    created_at      datetime DEFAULT NULL,
    updated_at      datetime DEFAULT NULL,
    CONSTRAINT pk_pokemons PRIMARY KEY(id)
)ENGINE=InnoDb;
/*FIN TABLA  POKEMONS*/

/*INICIO TABLA  POKEMONS*/
CREATE TABLE historial(
    id              int(255) auto_increment not null,
    nombre          varchar(50) NOT NULL,
    created_at      datetime DEFAULT NULL,
    updated_at      datetime DEFAULT NULL,
    CONSTRAINT pk_pokemons PRIMARY KEY(id)
)ENGINE=InnoDb;
/*FIN TABLA  POKEMONS*/